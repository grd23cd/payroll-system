<?php
include 'includes/session.php';

$range = $_POST['date_range'];
$ex = explode(' - ', $range);

$from = date('Y-m-d', strtotime($ex[0]));
$to = date('Y-m-d', strtotime($ex[1]));

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title = date('M d, Y', strtotime($ex[1]));

// Global deductions - fetch full breakdown
$deductions_query = $conn->query("SELECT description, amount FROM deductions");
$global_deductions = [];
$deduction = 0;
while ($ded_row = $deductions_query->fetch_assoc()) {
    $global_deductions[] = $ded_row;
    $deduction += $ded_row['amount'];
}

require_once('../tcpdf/tcpdf.php');

$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Payslip: '.$from_title.' - '.$to_title);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(TRUE, 10);

$pdf->SetFont('helvetica', '', 11);

$logo = dirname(__FILE__) . '/../images/logo.jpg';

// Get attendance grouped per employee
$sql = "
    SELECT attendance.*,
           employees.id AS empid,
           employees.employee_id AS emp_code,
           employees.firstname,
           employees.lastname,
           position.rate
    FROM attendance
    LEFT JOIN employees ON employees.id = attendance.employee_id
    LEFT JOIN position ON position.id = employees.position_id
    WHERE attendance.date BETWEEN '$from' AND '$to'
    ORDER BY employees.lastname ASC, employees.firstname ASC
";

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
        if($hours > 8){
            $hours = 8;
        }
    }

    $employees[$empid]['total_hr'] += $hours;
}

uasort($employees, function($a, $b){
    $cmp = strcmp($a['lastname'], $b['lastname']);
    return ($cmp !== 0) ? $cmp : strcmp($a['firstname'], $b['firstname']);
});

foreach($employees as $empid => $emp){

    $pdf->AddPage();

    if(file_exists($logo)){
        $pdf->Image($logo, 10, 10, 25, 25);
    }

    $pdf->SetXY(0, 15);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->MultiCell(210, 8, 'San Luis Development Cooperative', 0, 'C', false, 1);

    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(210, 6, $from_title.' - '.$to_title, 0, 'C', false, 1);

    $pdf->SetY(40);

    $emp_code = $emp['emp_code'];

    // Cash Advance
    $ca = $conn->query("
        SELECT SUM(amount) as cashamount
        FROM cashadvance
        WHERE employee_id = '$empid'
        AND date_advance BETWEEN '$from' AND '$to'
    ")->fetch_assoc()['cashamount'] ?? 0;

    $ca = (float)$ca;

    // Personal Deductions
    $pd_query = $conn->query("
        SELECT description, amount
        FROM personal_deductions
        WHERE employee_id = '$emp_code'
    ");

    $personal_deductions = [];
    $pd_total = 0;

    while($pd_row = $pd_query->fetch_assoc()){
        $personal_deductions[] = $pd_row;
        $pd_total += $pd_row['amount'];
    }

    // Overtime
    $ot_query = $conn->query("
        SELECT date_overtime, hours, rate,
               (hours * rate) AS ot_amount
        FROM overtime
        WHERE employee_id = '$empid'
        AND date_overtime BETWEEN '$from' AND '$to'
    ");

    $overtimes = [];
    $ot_total = 0;

    while($ot_row = $ot_query->fetch_assoc()){
        $overtimes[] = $ot_row;
        $ot_total += $ot_row['ot_amount'];
    }

    // Holiday Pay
    $hp_query = $conn->query("
        SELECT date_holiday, type, hours, rate, percentage,
               (hours * rate * (percentage / 100)) AS hp_amount
        FROM holiday_pay
        WHERE employee_id = '$empid'
        AND date_holiday BETWEEN '$from' AND '$to'
    ");

    $holidays = [];
    $hp_total = 0;

    while($hp_row = $hp_query->fetch_assoc()){
        $holidays[] = $hp_row;
        $hp_total += $hp_row['hp_amount'];
    }

    $regular = $emp['rate'] * $emp['total_hr'];
    $gross = $regular + $ot_total + $hp_total;
    $total_deduction = $deduction + $pd_total + $ca;
    $net = $gross - $total_deduction;

    $contents = '
        <table border="0" cellspacing="0" cellpadding="4">

        <tr>
            <td width="25%" align="right">Employee Name:</td>
            <td width="25%"><b>'.$emp['firstname'].' '.$emp['lastname'].'</b></td>
            <td width="25%" align="right">Rate per Hour:</td>
            <td width="25%" align="right">'.number_format($emp['rate'], 2).'</td>
        </tr>

        <tr>
            <td width="25%" align="right">Employee ID:</td>
            <td width="25%">'.$emp_code.'</td>
            <td width="25%" align="right">Total Hours:</td>
            <td width="25%" align="right">'.number_format($emp['total_hr'], 2).'</td>
        </tr>

        <tr>
            <td></td><td></td>
            <td width="25%" align="right">Regular Pay:</td>
            <td width="25%" align="right">'.number_format($regular, 2).'</td>
        </tr>
    ';

    if(!empty($overtimes)){
        foreach($overtimes as $ot){
            $contents .= '
                <tr>
                    <td></td><td></td>
                    <td width="25%" align="right">
                        OT ('.date('M d', strtotime($ot['date_overtime'])).' / '.$ot['hours'].'hrs):
                    </td>
                    <td width="25%" align="right">
                        '.number_format($ot['ot_amount'], 2).'
                    </td>
                </tr>
            ';
        }
    }

    if(!empty($holidays)){
        foreach($holidays as $hp){
            $contents .= '
                <tr>
                    <td></td><td></td>
                    <td width="25%" align="right">
                        Holiday ('.date('M d', strtotime($hp['date_holiday'])).' / '.$hp['type'].' / '.$hp['percentage'].'%):
                    </td>
                    <td width="25%" align="right">
                        '.number_format($hp['hp_amount'], 2).'
                    </td>
                </tr>
            ';
        }
    }

    $contents .= '
        <tr>
            <td></td><td></td>
            <td width="25%" align="right"><b>Gross Pay:</b></td>
            <td width="25%" align="right"><b>'.number_format($gross, 2).'</b></td>
        </tr>

        <tr><td colspan="4"><br></td></tr>
    ';

    foreach($global_deductions as $ded){
        $contents .= '
            <tr>
                <td></td><td></td>
                <td width="25%" align="right">'.$ded['description'].':</td>
                <td width="25%" align="right">'.number_format($ded['amount'], 2).'</td>
            </tr>
        ';
    }

    foreach($personal_deductions as $pd){
        $contents .= '
            <tr>
                <td></td><td></td>
                <td width="25%" align="right">'.$pd['description'].':</td>
                <td width="25%" align="right">'.number_format($pd['amount'], 2).'</td>
            </tr>
        ';
    }

    if($ca > 0){
        $contents .= '
            <tr>
                <td></td><td></td>
                <td width="25%" align="right">Cash Advance:</td>
                <td width="25%" align="right">'.number_format($ca, 2).'</td>
            </tr>
        ';
    }

    $contents .= '
        <tr>
            <td></td><td></td>
            <td width="25%" align="right"><b>Total Deduction:</b></td>
            <td width="25%" align="right"><b>'.number_format($total_deduction, 2).'</b></td>
        </tr>

        <tr>
            <td></td><td></td>
            <td width="25%" align="right"><b>Net Pay:</b></td>
            <td width="25%" align="right"><b>'.number_format($net, 2).'</b></td>
        </tr>

        </table>
    ';

    $pdf->writeHTML($contents, true, false, true, false, '');
}

if(ob_get_length()){
    ob_end_clean();
}

$pdf->Output('payslip.pdf', 'I');
exit;
?>