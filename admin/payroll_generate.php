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

$ex = explode(' - ', $range);

$from = date('Y-m-d', strtotime($ex[0]));
$to   = date('Y-m-d', strtotime($ex[1]));

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title   = date('M d, Y', strtotime($ex[1]));

include 'includes/payroll_computation.php';

$rows = $payroll_rows;

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
    if (count($r['pd_items']) > $max_pd) {
        $max_pd = count($r['pd_items']);
    }
}

$all_ded_labels = [];

foreach ($rows[0]['global_ded'] ?? [] as $d) {
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

$ded_start_col = 6;
$ded_cols      = $DEDS_PER_ROW * 2;
$ded_end_col   = $ded_start_col + $ded_cols - 1;

$total_ded_col = $ded_end_col + 1;
$net_col       = $total_ded_col + 1;
$sig_col       = $net_col + 1;

$last_col      = $sig_col;
$lastColLtr    = colLetter($last_col);

/* ── column widths ───────────────────────────────────────────────── */

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

/* ═══════════════════════════════════════════════════════════════════
   TITLE ROWS
═══════════════════════════════════════════════════════════════════ */

$sheet->mergeCells("A1:{$lastColLtr}1");
$sheet->setCellValue('A1', 'San Luis Development Cooperative');

$sheet->getStyle('A1')->getFont()
      ->setBold(true)
      ->setSize(14);

center($sheet, "A1:{$lastColLtr}1");
applyBg($sheet, "A1:{$lastColLtr}1", $HEADER_BG);

$sheet->getRowDimension(1)->setRowHeight(22);

$sheet->mergeCells("A2:{$lastColLtr}2");
$sheet->setCellValue('A2', "PAYROLL: {$from_title} – {$to_title}");

$sheet->getStyle('A2')->getFont()
      ->setBold(true)
      ->setSize(11);

center($sheet, "A2:{$lastColLtr}2");
applyBg($sheet, "A2:{$lastColLtr}2", $HEADER_BG);

$sheet->getRowDimension(2)->setRowHeight(18);

/* ═══════════════════════════════════════════════════════════════════
   HEADER ROWS
═══════════════════════════════════════════════════════════════════ */

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

$sheet->mergeCells(colLetter($total_ded_col)."3:".colLetter($total_ded_col)."4");
$sheet->setCellValue(colLetter($total_ded_col)."3", "TOTAL\nDEDUCTIONS");

$sheet->mergeCells(colLetter($net_col)."3:".colLetter($net_col)."4");
$sheet->setCellValue(colLetter($net_col)."3", "NET\nAMOUNT");

$sigColLtr = colLetter($sig_col);

$sheet->mergeCells("{$sigColLtr}3:{$sigColLtr}4");
$sheet->setCellValue("{$sigColLtr}3", "SIGNATURE");

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

/* ═══════════════════════════════════════════════════════════════════
   DATA ROWS
═══════════════════════════════════════════════════════════════════ */

$currentRow = 5;

$grandGross = 0;
$grandDed   = 0;
$grandNet   = 0;

foreach ($rows as $r) {

    $emp      = $r['emp'];
    $name     = strtoupper($emp['lastname'].', '.$emp['firstname']);
    $position = strtoupper($emp['position_name'] ?? 'N/A');  // Get position

    $all_ded_values = [];

    foreach ($r['global_ded'] as $d) {
        $all_ded_values[] = [
            'label'  => strtoupper($d['description']),
            'amount' => $d['amount']
        ];
    }

    for ($i = 0; $i < $max_pd; $i++) {

        if (isset($r['pd_items'][$i])) {

            $all_ded_values[] = [
                'label'  => $r['pd_items'][$i]['description'],
                'amount' => $r['pd_items'][$i]['amount']
            ];

        } else {

            $all_ded_values[] = [
                'label'  => '',
                'amount' => null
            ];
        }
    }

    $all_ded_values[] = [
        'label'  => ($r['ca'] > 0 ? 'Cash Advance' : ''),
        'amount' => ($r['ca'] > 0 ? $r['ca'] : null)
    ];

    $rowStart = $currentRow;
    $rowEnd   = $currentRow + $EMP_BLOCK_ROWS - 1;

    /* NAME */

    $sheet->mergeCells("A{$rowStart}:A{$rowEnd}");
    $sheet->setCellValue("A{$rowStart}", $name);
    center($sheet, "A{$rowStart}:A{$rowEnd}");

    /* DESIGNATION — populated with position_name */

    $sheet->mergeCells("B{$rowStart}:B{$rowEnd}");
    $sheet->setCellValue("B{$rowStart}", $position);
    center($sheet, "B{$rowStart}:B{$rowEnd}");

    /* REGULAR */

    $sheet->setCellValue("C{$rowStart}", $r['regular']);
    money($sheet, "C{$rowStart}");

    /* ADDONS */

    $addons = $r['ot'] + $r['hp'] + $r['cola_value'];

    $sheet->setCellValue("D{$rowStart}", $addons);
    money($sheet, "D{$rowStart}");

    /* GROSS */

    $sheet->mergeCells("E{$rowStart}:E{$rowEnd}");
    $sheet->setCellValue("E{$rowStart}", $r['gross']);
    money($sheet, "E{$rowStart}");

    /* DEDUCTIONS */

    $dedIdx = 0;

    for ($dr = 0; $dr < $ded_rows_needed; $dr++) {

        $dataRow = $rowStart + $dr;

        for ($dp = 0; $dp < $DEDS_PER_ROW; $dp++) {

            if ($dedIdx >= count($all_ded_values)) {
                break;
            }

            $item = $all_ded_values[$dedIdx];

            $lblCol = colLetter($ded_start_col + ($dp * 2));
            $valCol = colLetter($ded_start_col + ($dp * 2) + 1);

            $sheet->setCellValue("{$lblCol}{$dataRow}", $item['label']);

            if ($item['amount'] !== null) {
                $sheet->setCellValue("{$valCol}{$dataRow}", $item['amount']);
                money($sheet, "{$valCol}{$dataRow}");
            }

            $dedIdx++;
        }
    }

    /* TOTAL DEDUCTIONS */

    $tdCol = colLetter($total_ded_col);

    $sheet->mergeCells("{$tdCol}{$rowStart}:{$tdCol}{$rowEnd}");
    $sheet->setCellValue("{$tdCol}{$rowStart}", $r['total_deduction']);

    money($sheet, "{$tdCol}{$rowStart}");

    /* NET */

    $npCol = colLetter($net_col);

    $sheet->mergeCells("{$npCol}{$rowStart}:{$npCol}{$rowEnd}");
    $sheet->setCellValue("{$npCol}{$rowStart}", $r['net']);

    money($sheet, "{$npCol}{$rowStart}");

    /* SIGNATURE */

    $scLtr = colLetter($sig_col);

    $sheet->mergeCells("{$scLtr}{$rowStart}:{$scLtr}{$rowEnd}");

    /* BORDER */

    $sheet->getStyle("A{$rowStart}:{$lastColLtr}{$rowEnd}")
          ->applyFromArray($thin);

    $grandGross += $r['gross'];
    $grandDed   += $r['total_deduction'];
    $grandNet   += $r['net'];

    $currentRow += $EMP_BLOCK_ROWS;
}

/* ── FOOTER ROWS ─────────────────────────────────────────────────── */

$footerSpacer = $currentRow + 1;

$footerRow1 = $currentRow + 2;
$footerRow2 = $currentRow + 3;
$footerRow3 = $currentRow + 4;
$footerRow4 = $currentRow + 5;
$footerRow5 = $currentRow + 6;

$sheet->getRowDimension($footerSpacer)->setRowHeight(12);

$sheet->getRowDimension($footerRow1)->setRowHeight(16);
$sheet->getRowDimension($footerRow2)->setRowHeight(20);
$sheet->getRowDimension($footerRow3)->setRowHeight(20);
$sheet->getRowDimension($footerRow4)->setRowHeight(16);
$sheet->getRowDimension($footerRow5)->setRowHeight(16);

/* ── SIGNATORY COLUMN GROUPS ─────────────────────────────────────── */

$totalSigCols = $net_col;
$third = (int)floor($totalSigCols / 3);

$lbl1S = 1;
$lbl1E = $third;

$lbl2S = $third + 1;
$lbl2E = $third * 2;

$lbl3S = $third * 2 + 1;
$lbl3E = $net_col - 1;

$ul1S = $lbl1S + 1;
$ul1E = $lbl1E - 1;

$ul2S = $lbl2S + 1;
$ul2E = $lbl2E - 1;

$ul3S = $lbl3S + 1;
$ul3E = $lbl3E - 1;

$vS = colLetter($sig_col);

$lbl1SL = colLetter($lbl1S);
$lbl1EL = colLetter($lbl1E);

$lbl2SL = colLetter($lbl2S);
$lbl2EL = colLetter($lbl2E);

$lbl3SL = colLetter($lbl3S);
$lbl3EL = colLetter($lbl3E);

$ul1SL = colLetter($ul1S);
$ul1EL = colLetter($ul1E);

$ul2SL = colLetter($ul2S);
$ul2EL = colLetter($ul2E);

$ul3SL = colLetter($ul3S);
$ul3EL = colLetter($ul3E);

/* ── HELPER ─────────────────────────────────────────────────────── */

$mc = function(
    string $s,
    string $e,
    int $row,
    string $val,
    bool $bld,
    bool $itl,
    int $sz
) use ($sheet) {

    $sheet->mergeCells("{$s}{$row}:{$e}{$row}");

    $sheet->setCellValue("{$s}{$row}", $val);

    $f = $sheet->getStyle("{$s}{$row}")->getFont();

    $f->setBold($bld)
      ->setItalic($itl)
      ->setSize($sz);

    $sheet->getStyle("{$s}{$row}")
          ->getAlignment()
          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
          ->setVertical(Alignment::VERTICAL_CENTER);
};

/* ── PREPARED BY ─────────────────────────────────────────────────── */

$mc($ul1SL, $ul1EL, $footerRow1, 'Prepared by:', true, true, 10);

$sheet->mergeCells("{$ul1SL}{$footerRow2}:{$ul1EL}{$footerRow3}");

$sheet->getStyle("{$ul1SL}{$footerRow2}:{$ul1EL}{$footerRow3}")
      ->getBorders()
      ->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()
      ->setRGB('999999');

$mc($ul1SL, $ul1EL, $footerRow4,
    'MARLYN B. FORMENTO',
    true,
    false,
    9
);

$mc($ul1SL, $ul1EL, $footerRow5,
    'Bookkeeper',
    false,
    true,
    9
);

/* ── CORRECTED BY ────────────────────────────────────────────────── */

$mc($ul2SL, $ul2EL, $footerRow1, 'Corrected by:', true, true, 10);

$sheet->mergeCells("{$ul2SL}{$footerRow2}:{$ul2EL}{$footerRow3}");

$sheet->getStyle("{$ul2SL}{$footerRow2}:{$ul2EL}{$footerRow3}")
      ->getBorders()
      ->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()
      ->setRGB('999999');

$mc($ul2SL, $ul2EL, $footerRow4,
    'LAILA B. BARRETTO',
    true,
    false,
    9
);

$mc($ul2SL, $ul2EL, $footerRow5,
    'Head Cash & Disbursement',
    false,
    true,
    9
);

/* ── APPROVED BY ─────────────────────────────────────────────────── */

$mc($ul3SL, $ul3EL, $footerRow1, 'Approved by:', true, true, 10);

$sheet->mergeCells("{$ul3SL}{$footerRow2}:{$ul3EL}{$footerRow3}");

$sheet->getStyle("{$ul3SL}{$footerRow2}:{$ul3EL}{$footerRow3}")
      ->getBorders()
      ->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()
      ->setRGB('999999');

$mc($ul3SL, $ul3EL, $footerRow4,
    'ROWENA T. SALVANERA',
    true,
    false,
    9
);

$mc($ul3SL, $ul3EL, $footerRow5,
    'General Manager',
    false,
    true,
    9
);

/* ── PAID UNDER VOUCHER ──────────────────────────────────────────── */

$sheet->mergeCells("{$vS}{$footerRow1}:{$lastColLtr}{$footerRow1}");

$sheet->setCellValue(
    "{$vS}{$footerRow1}",
    'PAID UNDER   VOUCHER #'
);

$sheet->getStyle("{$vS}{$footerRow1}")
      ->getFont()
      ->setBold(true)
      ->setSize(9);

$sheet->mergeCells("{$vS}{$footerRow2}:{$lastColLtr}{$footerRow3}");

$sheet->getStyle("{$vS}{$footerRow2}:{$lastColLtr}{$footerRow3}")
      ->getBorders()
      ->getBottom()
      ->setBorderStyle(Border::BORDER_THIN)
      ->getColor()
      ->setRGB('999999');

$sheet->mergeCells("{$vS}{$footerRow4}:{$lastColLtr}{$footerRow5}");

$sheet->setCellValue(
    "{$vS}{$footerRow4}",
    'DATE___________________'
);

$sheet->getStyle("{$vS}{$footerRow4}")
      ->getFont()
      ->setSize(9);

/* ── Freeze ─────────────────────────────────────────────────────── */

$sheet->freezePane('A5');

/* ── Page setup ─────────────────────────────────────────────────── */

$sheet->getPageSetup()
      ->setOrientation(
          \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
      )
      ->setPaperSize(
          \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL
      );

/* ── OUTPUT ─────────────────────────────────────────────────────── */

if (ob_get_length()) {
    ob_end_clean();
}

$filename = 'payroll_'.$from.'_'.$to.'.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

header(
    'Content-Disposition: attachment; filename="'.$filename.'"'
);

header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
?>