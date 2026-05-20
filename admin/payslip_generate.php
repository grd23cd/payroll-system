<?php
include 'includes/session.php';

$range = $_POST['date_range'];
$ex = explode(' - ', $range);

$from = date('Y-m-d', strtotime($ex[0]));
$to = date('Y-m-d', strtotime($ex[1]));

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title = date('M d, Y', strtotime($ex[1]));

/* GLOBAL DEDUCTIONS */
$deductions_query = $conn->query("SELECT description, amount FROM deductions");
$global_deductions = [];
$deduction = 0;

while($row = $deductions_query->fetch_assoc()){
    $global_deductions[] = $row;
    $deduction += $row['amount'];
}

/* COLA */
$cola_q = $conn->query("SELECT amount, status FROM cola WHERE id=1");
$cola = $cola_q->fetch_assoc();

$cola_enabled = $cola['status'] ?? 0;
$cola_amount = $cola['amount'] ?? 0;

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

/* =========================
   ATTENDANCE QUERY
========================= */
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

/* =========================
   STEP 1: COLLECT RAW DATA PER DAY
========================= */
$employees = [];
$daily = [];

/* STEP 1: COLLECT RAW HOURS PER DAY */
while($row = $query->fetch_assoc()){

    $empid = $row['empid'];
    $date = $row['date'];

    if(!isset($employees[$empid])){
        $employees[$empid] = [
            'firstname'=>$row['firstname'],
            'lastname'=>$row['lastname'],
            'emp_code'=>$row['emp_code'],
            'rate'=>$row['rate'],
            'total_hr'=>0
        ];
    }

    if(!isset($daily[$empid])){
        $daily[$empid] = [];
    }

    if(!isset($daily[$empid][$date])){
        $daily[$empid][$date] = 0;
    }

    $daily[$empid][$date] += floatval($row['num_hr']);
}

/* STEP 2: APPLY 3:8 RATIO CORRECTLY PER DAY */
foreach($daily as $empid => $dates){

    foreach($dates as $date => $hours){

        $day = date('l', strtotime($date));

        if($hours > 8){
            $hours = 8;
        }

        if($day == 'Saturday'){

            if($hours > 0 && $hours <= 3){
                $hours = 8;
            } else {
                $hours = ($hours / 3) * 8;
                if($hours > 8){
                    $hours = 8;
                }
            }
        }

        $employees[$empid]['total_hr'] += $hours;
    }
}

/* =========================
   STEP 2: APPLY SATURDAY 3:8 RATIO PER DAY
========================= */
foreach($daily_hours as $empid => $dates){

    foreach($dates as $date => $hours){

        $day = date('l', strtotime($date));

        if($day == 'Saturday'){
            $hours = $hours * (8 / 3);

            if($hours > 8){
                $hours = 8;
            }
        }

        $employees[$empid]['total_hr'] += $hours;
    }
}

/* =========================
   PDF GENERATION
========================= */
foreach($employees as $empid => $emp){

    $pdf->AddPage();

    if(file_exists($logo)){
        $pdf->Image($logo, 35, 10, 25, 25);
    }

    $pdf->SetY(15);

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'San Luis Development Cooperative', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 6, $from_title.' - '.$to_title, 0, 1, 'C');

    $pdf->SetY(40);

    /* COMPUTATIONS */
    $regular = $emp['rate'] * $emp['total_hr'];

    $ot = $conn->query("SELECT SUM(hours * rate) as ot
                        FROM overtime
                        WHERE employee_id='$empid'
                        AND date_overtime BETWEEN '$from' AND '$to'")
                        ->fetch_assoc()['ot'] ?? 0;

    $hp = $conn->query("SELECT SUM(hours * rate * (percentage/100)) as hp
                        FROM holiday_pay
                        WHERE employee_id='$empid'
                        AND date_holiday BETWEEN '$from' AND '$to'")
                        ->fetch_assoc()['hp'] ?? 0;

    $ca = $conn->query("SELECT SUM(amount) as ca
                        FROM cashadvance
                        WHERE employee_id='$empid'
                        AND date_advance BETWEEN '$from' AND '$to'")
                        ->fetch_assoc()['ca'] ?? 0;

    /* PERSONAL DEDUCTIONS */
    $pd_query = $conn->query("SELECT description, amount
                              FROM personal_deductions
                              WHERE employee_id='".$emp['emp_code']."'");

    $pd = 0;
    $pd_breakdown = [];

    while($row = $pd_query->fetch_assoc()){
        $pd_breakdown[] = $row;
        $pd += $row['amount'];
    }

    $cola_value = ($cola_enabled == 1) ? $cola_amount : 0;

    $gross = $regular + $ot + $hp + $cola_value;
    $total_deduction = $deduction + $pd + $ca;
    $net = $gross - $total_deduction;

    $contents = '
    <table cellpadding="4">

    <tr>
        <td width="30%"><b>Employee Name:</b></td>
        <td width="70%">'.$emp['firstname'].' '.$emp['lastname'].'</td>
    </tr>

    <tr>
        <td><b>Employee ID:</b></td>
        <td>'.$emp['emp_code'].'</td>
    </tr>

    <br>

    <tr>
        <td>Regular Pay:</td>
        <td>'.number_format($regular,2).'</td>
    </tr>';

    if($ot > 0){
        $contents .= '
        <tr>
            <td>Overtime:</td>
            <td>'.number_format($ot,2).'</td>
        </tr>';
    }

    if($hp > 0){
        $contents .= '
        <tr>
            <td>Holiday Pay:</td>
            <td>'.number_format($hp,2).'</td>
        </tr>';
    }

    if($cola_value > 0){
        $contents .= '
        <tr>
            <td>COLA:</td>
            <td>'.number_format($cola_value,2).'</td>
        </tr>';
    }

    $contents .= '
    <tr>
        <td><b>Gross Pay:</b></td>
        <td><b>'.number_format($gross,2).'</b></td>
    </tr>

    <tr><td colspan="2"><hr></td></tr>';

    foreach($global_deductions as $d){
        $contents .= '
        <tr>
            <td>'.$d['description'].'</td>
            <td>'.number_format($d['amount'],2).'</td>
        </tr>';
    }

    foreach($pd_breakdown as $p){
        $contents .= '
        <tr>
            <td>'.$p['description'].'</td>
            <td>'.number_format($p['amount'],2).'</td>
        </tr>';
    }

    if($ca > 0){
        $contents .= '
        <tr>
            <td>Cash Advance</td>
            <td>'.number_format($ca,2).'</td>
        </tr>';
    }

    $contents .= '
    <tr>
        <td><b>Total Deduction:</b></td>
        <td><b>'.number_format($total_deduction,2).'</b></td>
    </tr>

    <tr><td colspan="2"><hr></td></tr>

    <tr>
        <td><b>Net Pay:</b></td>
        <td><b>'.number_format($net,2).'</b></td>
    </tr>

    </table>';

    $pdf->writeHTML($contents, true, false, true, false, '');
}

if(ob_get_length()){
    ob_end_clean();
}

$pdf->Output('payslip.pdf', 'I');
?>