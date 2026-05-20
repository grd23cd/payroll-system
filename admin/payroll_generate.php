<?php
include 'includes/session.php';

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$range = $_POST['date_range'];
$ex    = explode(' - ', $range);
$from  = date('Y-m-d', strtotime($ex[0]));
$to    = date('Y-m-d', strtotime($ex[1]));

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title   = date('M d, Y', strtotime($ex[1]));

/* ── GLOBAL DEDUCTIONS ───────────────────────────────────────────── */
$deductions_query   = $conn->query("SELECT description, amount FROM deductions");
$global_deductions  = [];
$total_global_deduction = 0;
while ($row = $deductions_query->fetch_assoc()) {
    $global_deductions[]      = $row;
    $total_global_deduction  += $row['amount'];
}

/* ── COLA ────────────────────────────────────────────────────────── */
$cola_q       = $conn->query("SELECT amount, status FROM cola WHERE id=1");
$cola         = $cola_q->fetch_assoc();
$cola_enabled = $cola['status'] ?? 0;
$cola_amount  = $cola['amount'] ?? 0;

/* ── ATTENDANCE → group by employee ─────────────────────────────── */
$sql = "SELECT attendance.*,
               employees.id          AS empid,
               employees.employee_id AS emp_code,
               employees.firstname,
               employees.lastname,
               position.rate
        FROM attendance
        LEFT JOIN employees ON employees.id = attendance.employee_id
        LEFT JOIN position  ON position.id  = employees.position_id
        WHERE attendance.date BETWEEN '$from' AND '$to'";

$query     = $conn->query($sql);
$employees = [];

/* ── ATTENDANCE → group by employee (FIXED SATURDAY LOGIC) ─────── */
$sql = "SELECT attendance.*,
               employees.id          AS empid,
               employees.employee_id AS emp_code,
               employees.firstname,
               employees.lastname,
               position.rate
        FROM attendance
        LEFT JOIN employees ON employees.id = attendance.employee_id
        LEFT JOIN position  ON position.id  = employees.position_id
        WHERE attendance.date BETWEEN '$from' AND '$to'";

$query = $conn->query($sql);

$employees = [];
$daily = [];

/* =========================
   STEP 1: GROUP PER DAY
========================= */
while ($row = $query->fetch_assoc()) {

    $empid = $row['empid'];
    $date  = $row['date'];

    if (!isset($employees[$empid])) {
        $employees[$empid] = [
            'firstname' => $row['firstname'],
            'lastname'  => $row['lastname'],
            'emp_code'  => $row['emp_code'],
            'rate'      => $row['rate'],
            'total_hr'  => 0,
        ];
    }

    if (!isset($daily[$empid][$date])) {
        $daily[$empid][$date] = 0;
    }

    $daily[$empid][$date] += (float)$row['num_hr'];
}

/* =========================
   STEP 2: APPLY RULES
========================= */
foreach ($daily as $empid => $dates) {

    foreach ($dates as $date => $hours) {

        $day = date('l', strtotime($date));

        // cap normal days
        if ($hours > 8) {
            $hours = 8;
        }

        /* =========================
           SATURDAY 3:8 RULE
        ========================= */
        if ($day === 'Saturday') {

            if ($hours > 0) {

                // proportional scaling
                $hours = ($hours / 3) * 8;

                // safety cap
                if ($hours > 8) {
                    $hours = 8;
                }
            }
        }

        $employees[$empid]['total_hr'] += $hours;
    }
}

uasort($employees, function ($a, $b) {
    $c = strcmp($a['lastname'], $b['lastname']);
    return $c !== 0 ? $c : strcmp($a['firstname'], $b['firstname']);
});

/* ── BUILD EMPLOYEE ROWS WITH COMPUTED PAY ───────────────────────── */
$rows = [];
foreach ($employees as $empid => $emp) {
    $emp_code = $emp['emp_code'];

    $ot = (float)($conn->query("SELECT SUM(hours * rate) AS v FROM overtime
                                 WHERE employee_id='$empid'
                                 AND date_overtime BETWEEN '$from' AND '$to'")
                       ->fetch_assoc()['v'] ?? 0);

    $hp = (float)($conn->query("SELECT SUM(hours * rate * (percentage/100)) AS v FROM holiday_pay
                                 WHERE employee_id='$empid'
                                 AND date_holiday BETWEEN '$from' AND '$to'")
                       ->fetch_assoc()['v'] ?? 0);

    $ca = (float)($conn->query("SELECT SUM(amount) AS v FROM cashadvance
                                 WHERE employee_id='$empid'
                                 AND date_advance BETWEEN '$from' AND '$to'")
                       ->fetch_assoc()['v'] ?? 0);

    /* personal deductions */
    $pd_q         = $conn->query("SELECT description, amount FROM personal_deductions
                                   WHERE employee_id='$emp_code'");
    $pd_items     = [];
    $pd_total     = 0;
    while ($p = $pd_q->fetch_assoc()) {
        $pd_items[] = $p;
        $pd_total  += $p['amount'];
    }

    $cola_value      = ($cola_enabled == 1) ? $cola_amount : 0;
    $regular         = $emp['rate'] * $emp['total_hr'];
    $gross           = $regular + $ot + $hp + $cola_value;
    $total_deduction = $total_global_deduction + $pd_total + $ca;
    $net             = $gross - $total_deduction;

    $rows[] = [
        'emp'             => $emp,
        'regular'         => $regular,
        'ot'              => $ot,
        'hp'              => $hp,
        'cola_value'      => $cola_value,
        'gross'           => $gross,
        'global_ded'      => $global_deductions,
        'pd_items'        => $pd_items,
        'ca'              => $ca,
        'total_deduction' => $total_deduction,
        'net'             => $net,
    ];
}

/* ═══════════════════════════════════════════════════════════════════
   SPREADSHEET SETUP
═══════════════════════════════════════════════════════════════════ */
$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Payroll');

/* ── color constants ─────────────────────────────────────────────── */
$HEADER_BG  = 'D9E1F2';
$LABEL_BG   = 'F2F2F2';
$TOTAL_BG   = 'FFF2CC';
$BORDER_CLR = '4472C4';

$thin = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF' . $BORDER_CLR],
        ],
    ],
];

function applyBg(object $sheet, string $range, string $hex): void {
    $sheet->getStyle($range)->getFill()
          ->setFillType(Fill::FILL_SOLID)
          ->getStartColor()->setRGB($hex);
}
function bold(object $sheet, string $range): void {
    $sheet->getStyle($range)->getFont()->setBold(true);
}
function center(object $sheet, string $range): void {
    $sheet->getStyle($range)->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER);
}
function right(object $sheet, string $range): void {
    $sheet->getStyle($range)->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
          ->setVertical(Alignment::VERTICAL_CENTER);
}
function money(object $sheet, string $range): void {
    $sheet->getStyle($range)->getNumberFormat()
          ->setFormatCode('#,##0.00');
}
function colLetter(int $n): string {
    return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($n);
}

/* ── COLUMN LAYOUT ───────────────────────────────────────────────── */
$max_pd = 0;
foreach ($rows as $r) {
    if (count($r['pd_items']) > $max_pd) $max_pd = count($r['pd_items']);
}

$all_ded_labels = [];
foreach ($global_deductions as $d) {
    $all_ded_labels[] = strtoupper($d['description']);
}
for ($i = 0; $i < $max_pd; $i++) {
    $all_ded_labels[] = 'DEDUCTION ' . ($i + 1);
}
$all_ded_labels[] = 'CASH ADVANCE';

$total_deds      = count($all_ded_labels);
$DEDS_PER_ROW    = 4;
$ded_rows_needed = (int)ceil($total_deds / $DEDS_PER_ROW);
$EMP_BLOCK_ROWS  = max(3, $ded_rows_needed);

$ded_start_col = 6; // F
$ded_cols      = $DEDS_PER_ROW * 2;
$ded_end_col   = $ded_start_col + $ded_cols - 1;
$total_ded_col = $ded_end_col + 1;
$net_col       = $total_ded_col + 1;
$sig_col       = $net_col + 1;
$last_col      = $sig_col;
$lastColLtr    = colLetter($last_col);

/* ── column widths ─────────────────────────────────────────────────── */
$sheet->getColumnDimension('A')->setWidth(26);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(13);
$sheet->getColumnDimension('D')->setWidth(16);
$sheet->getColumnDimension('E')->setWidth(13);

for ($pair = 0; $pair < $DEDS_PER_ROW; $pair++) {
    $labelCol = $ded_start_col + ($pair * 2);
    $valueCol = $labelCol + 1;
    $sheet->getColumnDimension(colLetter($labelCol))->setWidth(16);
    $sheet->getColumnDimension(colLetter($valueCol))->setWidth(10);
}
$sheet->getColumnDimension(colLetter($total_ded_col))->setWidth(13);
$sheet->getColumnDimension(colLetter($net_col))->setWidth(13);
$sheet->getColumnDimension(colLetter($sig_col))->setWidth(18);

/* ══════════════════════════════════════════════════════════════════
   TITLE ROWS  (rows 1 & 2)
══════════════════════════════════════════════════════════════════ */
$sheet->mergeCells("A1:{$lastColLtr}1");
$sheet->setCellValue('A1', 'San Luis Development Cooperative');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
center($sheet, "A1:{$lastColLtr}1");
applyBg($sheet, "A1:{$lastColLtr}1", $HEADER_BG);
$sheet->getRowDimension(1)->setRowHeight(22);

$sheet->mergeCells("A2:{$lastColLtr}2");
$sheet->setCellValue('A2', "PAYROLL: {$from_title} – {$to_title}");
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
center($sheet, "A2:{$lastColLtr}2");
applyBg($sheet, "A2:{$lastColLtr}2", $HEADER_BG);
$sheet->getRowDimension(2)->setRowHeight(18);

/* ══════════════════════════════════════════════════════════════════
   HEADER ROWS 3 & 4
══════════════════════════════════════════════════════════════════ */
$hdrRow    = 3;
$subHdrRow = 4;

$sheet->mergeCells("A3:A4");
$sheet->setCellValue("A3", 'NAME');

$sheet->mergeCells("B3:B4");
$sheet->setCellValue("B3", 'DESIGNATION');

$sheet->mergeCells("C3:E3");
$sheet->setCellValue("C3", 'GROSS');

$dedHeaderStart = colLetter($ded_start_col);
$dedHeaderEnd   = colLetter($ded_end_col);
$sheet->mergeCells("{$dedHeaderStart}3:{$dedHeaderEnd}3");
$sheet->setCellValue("{$dedHeaderStart}3", 'DEDUCTIONS');

$sheet->mergeCells(colLetter($total_ded_col) . "3:" . colLetter($total_ded_col) . "4");
$sheet->setCellValue(colLetter($total_ded_col) . "3", "TOTAL\nDEDUCTIONS");
$sheet->getStyle(colLetter($total_ded_col) . "3")->getAlignment()->setWrapText(true);

$sheet->mergeCells(colLetter($net_col) . "3:" . colLetter($net_col) . "4");
$sheet->setCellValue(colLetter($net_col) . "3", "NET\nAMOUNT");
$sheet->getStyle(colLetter($net_col) . "3")->getAlignment()->setWrapText(true);

$sigColLtr = colLetter($sig_col);
$sheet->mergeCells("{$sigColLtr}3:{$sigColLtr}4");
$sheet->setCellValue("{$sigColLtr}3", "SIGNATURE");
$sheet->getStyle("{$sigColLtr}3")->getAlignment()->setWrapText(true);

$sheet->setCellValue("C4", 'S & W / DL');
$sheet->setCellValue("D4", 'HOLIDAY / OT / COLA');
$sheet->setCellValue("E4", 'GROSS PAY');

$sheet->mergeCells("{$dedHeaderStart}4:{$dedHeaderEnd}4");
$sheet->setCellValue("{$dedHeaderStart}4", 'CONTRIBUTION / LOAN');

$hdrRange = "A3:{$lastColLtr}4";
applyBg($sheet, $hdrRange, $HEADER_BG);
bold($sheet, $hdrRange);
center($sheet, $hdrRange);
$sheet->getStyle($hdrRange)->applyFromArray($thin);
$sheet->getRowDimension(3)->setRowHeight(18);
$sheet->getRowDimension(4)->setRowHeight(16);

/* ══════════════════════════════════════════════════════════════════
   DATA ROWS
══════════════════════════════════════════════════════════════════ */
$currentRow = 5;
$grandNet   = 0;
$grandGross = 0;
$grandDed   = 0;
$shade      = false;

foreach ($rows as $r) {
    $emp  = $r['emp'];
    $name = strtoupper($emp['lastname'] . ', ' . $emp['firstname']);

    $all_ded_values = [];
    foreach ($r['global_ded'] as $d) {
        $all_ded_values[] = ['label' => strtoupper($d['description']), 'amount' => $d['amount']];
    }
    for ($i = 0; $i < $max_pd; $i++) {
        if (isset($r['pd_items'][$i])) {
            $all_ded_values[] = ['label' => $r['pd_items'][$i]['description'], 'amount' => $r['pd_items'][$i]['amount']];
        } else {
            $all_ded_values[] = ['label' => '', 'amount' => null];
        }
    }
    $all_ded_values[] = ['label' => ($r['ca'] > 0 ? 'Cash Advance' : ''), 'amount' => ($r['ca'] > 0 ? $r['ca'] : null)];

    $emp_rows = max(3, $ded_rows_needed);
    $rowStart = $currentRow;
    $rowEnd   = $currentRow + $emp_rows - 1;

    /* Name */
    $sheet->mergeCells("A{$rowStart}:A{$rowEnd}");
    $sheet->setCellValue("A{$rowStart}", $name);
    bold($sheet, "A{$rowStart}");
    $sheet->getStyle("A{$rowStart}")->getAlignment()
          ->setWrapText(true)
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setHorizontal(Alignment::HORIZONTAL_LEFT);

    /* Designation */
    $sheet->mergeCells("B{$rowStart}:B{$rowEnd}");
    $sheet->setCellValue("B{$rowStart}", '');
    $sheet->getStyle("B{$rowStart}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    /* Regular Pay */
    $sheet->setCellValue("C{$rowStart}", $r['regular']);
    money($sheet, "C{$rowStart}");
    right($sheet, "C{$rowStart}");
    if ($rowStart + 1 <= $rowEnd) {
        $sheet->setCellValue("C" . ($rowStart + 1), 'Rate: ' . number_format($emp['rate'], 2));
        $sheet->getStyle("C" . ($rowStart + 1))->getFont()->setSize(8)->setItalic(true);
    }
    if ($rowStart + 2 <= $rowEnd) {
        $sheet->setCellValue("C" . ($rowStart + 2), 'Hours: ' . number_format($emp['total_hr'], 2));
        $sheet->getStyle("C" . ($rowStart + 2))->getFont()->setSize(8)->setItalic(true);
    }

    /* Addons (OT / Holiday / COLA) */
    $addons = 0;
    $addonDetails = [];
    if ($r['ot']         > 0) { $addons += $r['ot'];         $addonDetails[] = 'OT: '   . number_format($r['ot'], 2); }
    if ($r['hp']         > 0) { $addons += $r['hp'];         $addonDetails[] = 'Hol: '  . number_format($r['hp'], 2); }
    if ($r['cola_value'] > 0) { $addons += $r['cola_value']; $addonDetails[] = 'COLA: ' . number_format($r['cola_value'], 2); }

    $sheet->setCellValue("D{$rowStart}", $addons > 0 ? $addons : 0);
    money($sheet, "D{$rowStart}");
    right($sheet, "D{$rowStart}");
    if (!empty($addonDetails) && ($rowStart + 1 <= $rowEnd)) {
        $sheet->setCellValue("D" . ($rowStart + 1), implode(' | ', $addonDetails));
        $sheet->getStyle("D" . ($rowStart + 1))->getFont()->setSize(8)->setItalic(true);
    }
    if ($rowStart + 2 <= $rowEnd) {
        $sheet->setCellValue("D" . ($rowStart + 2), $emp['emp_code']);
        $sheet->getStyle("D" . ($rowStart + 2))
              ->getFont()->setColor((new \PhpOffice\PhpSpreadsheet\Style\Color())->setRGB('808080'));
        $sheet->getStyle("D" . ($rowStart + 2))->getFont()->setSize(8);
    }

    /* Gross Pay */
    $sheet->mergeCells("E{$rowStart}:E{$rowEnd}");
    $sheet->setCellValue("E{$rowStart}", $r['gross']);
    money($sheet, "E{$rowStart}");
    bold($sheet, "E{$rowStart}");
    $sheet->getStyle("E{$rowStart}")->getAlignment()
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    /* Deductions */
    $dedIdx = 0;
    for ($dr = 0; $dr < $ded_rows_needed; $dr++) {
        $dataRow = $rowStart + $dr;
        for ($dp = 0; $dp < $DEDS_PER_ROW; $dp++) {
            if ($dedIdx >= count($all_ded_values)) break;
            $item   = $all_ded_values[$dedIdx];
            $lblCol = colLetter($ded_start_col + ($dp * 2));
            $valCol = colLetter($ded_start_col + ($dp * 2) + 1);

            $sheet->setCellValue("{$lblCol}{$dataRow}", $item['label']);
            $sheet->getStyle("{$lblCol}{$dataRow}")->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                  ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$lblCol}{$dataRow}")->getFont()->setSize(9);

            if ($item['amount'] !== null && $item['amount'] != 0) {
                $sheet->setCellValue("{$valCol}{$dataRow}", $item['amount']);
                money($sheet, "{$valCol}{$dataRow}");
            }
            $sheet->getStyle("{$valCol}{$dataRow}")->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                  ->setVertical(Alignment::VERTICAL_CENTER);

            $dedIdx++;
        }
    }

    /* Total Deductions */
    $tdCol = colLetter($total_ded_col);
    $sheet->mergeCells("{$tdCol}{$rowStart}:{$tdCol}{$rowEnd}");
    $sheet->setCellValue("{$tdCol}{$rowStart}", $r['total_deduction']);
    money($sheet, "{$tdCol}{$rowStart}");
    bold($sheet, "{$tdCol}{$rowStart}");
    applyBg($sheet, "{$tdCol}{$rowStart}", $LABEL_BG);
    $sheet->getStyle("{$tdCol}{$rowStart}")->getAlignment()
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    /* Net Pay */
    $npCol = colLetter($net_col);
    $sheet->mergeCells("{$npCol}{$rowStart}:{$npCol}{$rowEnd}");
    $sheet->setCellValue("{$npCol}{$rowStart}", $r['net']);
    money($sheet, "{$npCol}{$rowStart}");
    bold($sheet, "{$npCol}{$rowStart}");
    applyBg($sheet, "{$npCol}{$rowStart}", $TOTAL_BG);
    $sheet->getStyle("{$npCol}{$rowStart}")->getAlignment()
          ->setVertical(Alignment::VERTICAL_CENTER)
          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    /* Signature */
    $scLtr     = colLetter($sig_col);
    $sigMidRow = (int)(($rowStart + $rowEnd) / 2);
    $sheet->mergeCells("{$scLtr}{$rowStart}:{$scLtr}{$rowEnd}");
    for ($sr = $rowStart; $sr <= $rowEnd; $sr++) {
        $topBorder    = ($sr === $rowStart)  ? Border::BORDER_THIN   : Border::BORDER_NONE;
        $bottomBorder = ($sr === $sigMidRow) ? Border::BORDER_MEDIUM : ($sr === $rowEnd ? Border::BORDER_THIN : Border::BORDER_NONE);
        $sheet->getCell("{$scLtr}{$sr}")->getStyle()->applyFromArray([
            'borders' => [
                'left'   => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF' . $BORDER_CLR]],
                'right'  => ['borderStyle' => Border::BORDER_THIN,   'color' => ['argb' => 'FF' . $BORDER_CLR]],
                'top'    => ['borderStyle' => $topBorder,            'color' => ['argb' => 'FF' . $BORDER_CLR]],
                'bottom' => ['borderStyle' => $bottomBorder,         'color' => ['argb' => 'FF555555']],
            ],
        ]);
    }

    /* Borders + alternating shade */
    $sheet->getStyle("A{$rowStart}:{$lastColLtr}{$rowEnd}")->applyFromArray($thin);
    if ($shade) {
        applyBg($sheet, "A{$rowStart}:E{$rowEnd}", 'F9F9F9');
    }
    $shade = !$shade;

    for ($dr = 0; $dr < $emp_rows; $dr++) {
        $sheet->getRowDimension($rowStart + $dr)->setRowHeight(16);
    }

    $grandGross += $r['gross'];
    $grandDed   += $r['total_deduction'];
    $grandNet   += $r['net'];

    $currentRow += $emp_rows;
}

/* ── TOTALS ROW ───────────────────────────────────────────────────── */
$totRow = $currentRow;   // ← defined HERE, after the loop

$sheet->mergeCells("A{$totRow}:D{$totRow}");
$sheet->setCellValue("A{$totRow}", 'GRAND TOTAL');
bold($sheet, "A{$totRow}:{$lastColLtr}{$totRow}");
applyBg($sheet, "A{$totRow}:{$lastColLtr}{$totRow}", $HEADER_BG);
center($sheet, "A{$totRow}");

$sheet->setCellValue("E{$totRow}", $grandGross);
money($sheet, "E{$totRow}");
right($sheet, "E{$totRow}");

$sheet->setCellValue(colLetter($total_ded_col) . $totRow, $grandDed);
money($sheet, colLetter($total_ded_col) . $totRow);
right($sheet, colLetter($total_ded_col) . $totRow);

$sheet->setCellValue(colLetter($net_col) . $totRow, $grandNet);
money($sheet, colLetter($net_col) . $totRow);
right($sheet, colLetter($net_col) . $totRow);

$sheet->getStyle("A{$totRow}:{$lastColLtr}{$totRow}")->applyFromArray($thin);
$sheet->getRowDimension($totRow)->setRowHeight(20);

/* ── FOOTER ROWS ─────────────────────────────────────────────────── */
// footerRow1 = "Prepared by:" / "Corrected by:" / "Approved by:" labels
// footerRow2 = signature underline (one merged cell per section, smaller width)
// footerRow3 = role/title labels
// footerRow4 = PAID UNDER VOUCHER # + DATE

$footerSpacer = $totRow + 1;
$footerRow1   = $totRow + 2;
$footerRow2   = $totRow + 3;
$footerRow3   = $totRow + 4;
// footerRow4 and footerRow5 declared inside the block below

$sheet->getRowDimension($footerSpacer)->setRowHeight(12);
$sheet->getRowDimension($footerRow1)->setRowHeight(16);
$sheet->getRowDimension($footerRow2)->setRowHeight(22);
$sheet->getRowDimension($footerRow3)->setRowHeight(22);

// ── Column block definitions ───────────────────────────────────────
// Each signatory gets a "label zone" (for "Prepared by:" text) and a smaller
// "underline zone" centered inside it, with explicit gaps so lines never touch.
//
// Structure across columns 1..$net_col:
//   [pad][===BLK1===][pad][===BLK2===][pad][===BLK3===][pad] | sig_col..last = voucher
//
// We assign equal thirds to $net_col columns, each block padded by 1 col on each side
// so the underline is visually inset and the voucher block is clearly separated.

$totalSigCols = $net_col; // columns 1 to $net_col belong to the 3 signatories
$third        = (int)floor($totalSigCols / 3);

// Full label zones (for "Prepared by:" text)
$lbl1S = 1;             $lbl1E = $third;
$lbl2S = $third + 1;    $lbl2E = $third * 2;
$lbl3S = $third * 2 + 1; $lbl3E = $net_col - 1; // stop 1 col before sig_col gap

// Underline zones: inset by 1 col on each side of the label zone
$ul1S = $lbl1S + 1;    $ul1E = $lbl1E - 1;
$ul2S = $lbl2S + 1;    $ul2E = $lbl2E - 1;
$ul3S = $lbl3S + 1;    $ul3E = $lbl3E - 1;  // does NOT reach net_col, gap before voucher

// Voucher block starts at sig_col
$vS = colLetter($sig_col);

// Convert to letters
$lbl1SL = colLetter($lbl1S); $lbl1EL = colLetter($lbl1E);
$lbl2SL = colLetter($lbl2S); $lbl2EL = colLetter($lbl2E);
$lbl3SL = colLetter($lbl3S); $lbl3EL = colLetter($lbl3E);
$ul1SL  = colLetter($ul1S);  $ul1EL  = colLetter($ul1E);
$ul2SL  = colLetter($ul2S);  $ul2EL  = colLetter($ul2E);
$ul3SL  = colLetter($ul3S);  $ul3EL  = colLetter($ul3E);

// ── Row layout (5 rows per footer block) ──────────────────────────
// footerRow1 = "Prepared by:" / "Corrected by:" / "Approved by:" labels
// footerRow2 = signature space (empty, tall)
// footerRow3 = signature space (empty, tall)
// footerRow4 = name (MARLYN B. FORMENTO / LAILA B. BARRETTO / ROWENA T. SALVANERA)
// footerRow5 = role (Bookkeeper / Head Cash & Disbursement / General Manager)
$footerRow4 = $totRow + 5;
$footerRow5 = $totRow + 6;

$sheet->getRowDimension($footerRow2)->setRowHeight(20);
$sheet->getRowDimension($footerRow3)->setRowHeight(20);
$sheet->getRowDimension($footerRow4)->setRowHeight(16);
$sheet->getRowDimension($footerRow5)->setRowHeight(16);

// Helper to apply center alignment to a merged block
$mc = function(string $s, string $e, int $row, string $val, bool $bld, bool $itl, int $sz) use ($sheet) {
    $sheet->mergeCells("{$s}{$row}:{$e}{$row}");
    $sheet->setCellValue("{$s}{$row}", $val);
    $f = $sheet->getStyle("{$s}{$row}")->getFont();
    $f->setBold($bld)->setItalic($itl)->setSize($sz);
    $sheet->getStyle("{$s}{$row}")->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER);
};

// ── PREPARED BY ────────────────────────────────────────────────────
$mc($ul1SL, $ul1EL, $footerRow1, 'Prepared by:',  true,  true,  10);
// sig space rows: merged + bottom border on row3 to act as signature line
$sheet->mergeCells("{$ul1SL}{$footerRow2}:{$ul1EL}{$footerRow3}");
$sheet->getStyle("{$ul1SL}{$footerRow2}:{$ul1EL}{$footerRow3}")
      ->getBorders()->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()->setRGB('999999');
$mc($ul1SL, $ul1EL, $footerRow4, 'MARLYN B. FORMENTO', true,  false, 9);
$mc($ul1SL, $ul1EL, $footerRow5, 'Bookkeeper',         false, true,  9);

// ── CORRECTED BY ───────────────────────────────────────────────────
$mc($ul2SL, $ul2EL, $footerRow1, 'Corrected by:', true,  true,  10);
$sheet->mergeCells("{$ul2SL}{$footerRow2}:{$ul2EL}{$footerRow3}");
$sheet->getStyle("{$ul2SL}{$footerRow2}:{$ul2EL}{$footerRow3}")
      ->getBorders()->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()->setRGB('999999');
$mc($ul2SL, $ul2EL, $footerRow4, 'LAILA B. BARRETTO',      true,  false, 9);
$mc($ul2SL, $ul2EL, $footerRow5, 'Head Cash & Disbursement', false, true,  9);

// ── APPROVED BY ────────────────────────────────────────────────────
$mc($ul3SL, $ul3EL, $footerRow1, 'Approved by:', true,  true,  10);
$sheet->mergeCells("{$ul3SL}{$footerRow2}:{$ul3EL}{$footerRow3}");
$sheet->getStyle("{$ul3SL}{$footerRow2}:{$ul3EL}{$footerRow3}")
      ->getBorders()->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()->setRGB('999999');
$mc($ul3SL, $ul3EL, $footerRow4, 'ROWENA T. SALVANERA', true,  false, 9);
$mc($ul3SL, $ul3EL, $footerRow5, 'General Manager',      false, true,  9);

// ── PAID UNDER VOUCHER # ───────────────────────────────────────────
$sheet->mergeCells("{$vS}{$footerRow1}:{$lastColLtr}{$footerRow1}");
$sheet->setCellValue("{$vS}{$footerRow1}", 'PAID UNDER   VOUCHER #');
$sheet->getStyle("{$vS}{$footerRow1}")->getFont()->setBold(true)->setSize(9);
$sheet->getStyle("{$vS}{$footerRow1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Voucher underline (space for writing)
$sheet->mergeCells("{$vS}{$footerRow2}:{$lastColLtr}{$footerRow3}");
$sheet->getStyle("{$vS}{$footerRow2}:{$lastColLtr}{$footerRow3}")
      ->getBorders()->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()->setRGB('999999');

// DATE
$sheet->mergeCells("{$vS}{$footerRow4}:{$lastColLtr}{$footerRow5}");
$sheet->setCellValue("{$vS}{$footerRow4}", 'DATE___________________');
$sheet->getStyle("{$vS}{$footerRow4}")->getFont()->setSize(9);
$sheet->getStyle("{$vS}{$footerRow4}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

/* ── Freeze top rows ─────────────────────────────────────────────── */
$sheet->freezePane('A5');

/* ── Page setup ──────────────────────────────────────────────────── */
$sheet->getPageSetup()
      ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
      ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL)
      ->setFitToPage(true)
      ->setFitToWidth(1)
      ->setFitToHeight(0);

$sheet->getHeaderFooter()
      ->setOddHeader('&C&B San Luis Development Cooperative – Payroll');
$sheet->getHeaderFooter()
      ->setOddFooter('&L' . $from_title . ' – ' . $to_title . '&RPage &P of &N');

/* ── OUTPUT ──────────────────────────────────────────────────────── */
if (ob_get_length()) ob_end_clean();

$filename = 'payroll_' . $from . '_' . $to . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;