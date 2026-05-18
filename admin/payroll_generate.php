<?php
include 'includes/session.php';

function generateRow($from, $to, $conn){

    $contents = '';
    $total = 0;

    // Global deductions
    $deduction = $conn->query("SELECT SUM(amount) as total_amount FROM deductions")
                      ->fetch_assoc()['total_amount'] ?? 0;

    // Get all attendance in range
    $sql = "SELECT attendance.*,
                   employees.id AS empid,
                   employees.employee_id AS emp_code,
                   employees.firstname,
                   employees.lastname,
                   position.rate
            FROM attendance
            LEFT JOIN employees ON employees.id = attendance.employee_id
            LEFT JOIN position ON position.id = employees.position_id
            WHERE attendance.date BETWEEN '$from' AND '$to'";

    $query = $conn->query($sql);

    $employees = [];

    while($row = $query->fetch_assoc()){

        $empid = $row['empid'];

        if(!isset($employees[$empid])){
            $employees[$empid] = [
                'firstname' => $row['firstname'],
                'lastname'  => $row['lastname'],
                'emp_code'  => $row['emp_code'],
                'rate'      => $row['rate'],
                'total_hr'  => 0
            ];
        }

        $hours = $row['num_hr'];

        $day = date('l', strtotime($row['date']));

        if($day == 'Saturday'){
            $hours = ($hours / 3) * 8;
            if($hours > 8) $hours = 8;
        }

        $employees[$empid]['total_hr'] += $hours;
    }

    // Sort by lastname ASC, firstname ASC
    uasort($employees, function($a, $b){
        $cmp = strcmp($a['lastname'], $b['lastname']);
        return ($cmp !== 0) ? $cmp : strcmp($a['firstname'], $b['firstname']);
    });

    foreach($employees as $empid => $emp){

        // Cash advance per employee
        $ca = $conn->query("SELECT SUM(amount) as cashamount
                            FROM cashadvance
                            WHERE employee_id='$empid'
                            AND date_advance BETWEEN '$from' AND '$to'")
                            ->fetch_assoc()['cashamount'] ?? 0;

        // Personal deductions per employee
        $emp_code = $emp['emp_code'];
        $pd = $conn->query("SELECT SUM(amount) as pdamount
                            FROM personal_deductions
                            WHERE employee_id='$emp_code'")
                            ->fetch_assoc()['pdamount'] ?? 0;

        // Regular pay
        $regular = $emp['rate'] * $emp['total_hr'];

        // Overtime
        $ot = $conn->query("SELECT SUM(hours * rate) as total_ot
                            FROM overtime
                            WHERE employee_id='$empid'
                            AND date_overtime BETWEEN '$from' AND '$to'")
                            ->fetch_assoc()['total_ot'] ?? 0;

        // Gross
        $gross = $regular + $ot;

        // Total deductions
        $total_deduction = $deduction + $pd + $ca;

        // Net
        $net = $gross - $total_deduction;

        $total += $net;

        $contents .= '
        <tr>
            <td>'.$emp['lastname'].', '.$emp['firstname'].'</td>
            <td>'.$emp['emp_code'].'</td>
            <td align="right">'.number_format($net, 2).'</td>
        </tr>
        ';
    }

    $contents .= '
        <tr>
            <td colspan="2" align="right"><b>Total</b></td>
            <td align="right"><b>'.number_format($total, 2).'</b></td>
        </tr>
    ';

    return $contents;
}

// =====================
// DATE RANGE
// =====================
$range = $_POST['date_range'];
$ex = explode(' - ', $range);

$from = date('Y-m-d', strtotime($ex[0]));
$to = date('Y-m-d', strtotime($ex[1]));

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title = date('M d, Y', strtotime($ex[1]));

// =====================
// TCPDF
// =====================
require_once('../tcpdf/tcpdf.php');

$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Payroll: '.$from_title.' - '.$to_title);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

// Logo path
$logo = dirname(__FILE__) . '/../images/logo.jpg';

// LOGO - top-left, absolute position
if(file_exists($logo)){
    $pdf->Image($logo, 10, 10, 25, 25);
}

// TITLE - full page width from X=0 so 'C' centers across entire page
$pdf->SetXY(0, 15);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->MultiCell(210, 8, 'San Luis Development Cooperative', 0, 'C', false, 1);
$pdf->SetX(0);
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(210, 6, $from_title.' - '.$to_title, 0, 'C', false, 1);

// Move below header before table
$pdf->SetY(40);

$content = '
<table border="1" cellspacing="0" cellpadding="3">
<tr>
    <th width="40%" align="center"><b>Employee Name</b></th>
    <th width="30%" align="center"><b>Employee ID</b></th>
    <th width="30%" align="center"><b>Net Pay</b></th>
</tr>
';

$content .= generateRow($from, $to, $conn);

$content .= '</table>';

$pdf->writeHTML($content);
$pdf->Output('payroll.pdf', 'I');
?>