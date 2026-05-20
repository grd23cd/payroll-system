# payslip_generate.php

```php
<?php
include 'includes/session.php';

$range = $_POST['date_range'];
$ex = explode(' - ', $range);

$from = date('Y-m-d', strtotime($ex[0]));
$to   = date('Y-m-d', strtotime($ex[1]));

$from_title = date('M d, Y', strtotime($ex[0]));
$to_title   = date('M d, Y', strtotime($ex[1]));

include 'includes/payroll_computation.php';

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
   PDF GENERATION
========================= */

foreach($payroll_rows as $row){

    $emp = $row['emp'];

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
        <td>'.number_format($row['regular'],2).'</td>
    </tr>';

    if($row['ot'] > 0){
        $contents .= '
        <tr>
            <td>Overtime:</td>
            <td>'.number_format($row['ot'],2).'</td>
        </tr>';
    }

    if($row['hp'] > 0){
        $contents .= '
        <tr>
            <td>Holiday Pay:</td>
            <td>'.number_format($row['hp'],2).'</td>
        </tr>';
    }

    if($row['cola_value'] > 0){
        $contents .= '
        <tr>
            <td>COLA:</td>
            <td>'.number_format($row['cola_value'],2).'</td>
        </tr>';
    }

    $contents .= '
    <tr>
        <td><b>Gross Pay:</b></td>
        <td><b>'.number_format($row['gross'],2).'</b></td>
    </tr>

    <tr><td colspan="2"><hr></td></tr>';

    foreach($row['global_ded'] as $d){
        $contents .= '
        <tr>
            <td>'.$d['description'].'</td>
            <td>'.number_format($d['amount'],2).'</td>
        </tr>';
    }

    foreach($row['pd_items'] as $p){
        $contents .= '
        <tr>
            <td>'.$p['description'].'</td>
            <td>'.number_format($p['amount'],2).'</td>
        </tr>';
    }

    if($row['ca'] > 0){
        $contents .= '
        <tr>
            <td>Cash Advance</td>
            <td>'.number_format($row['ca'],2).'</td>
        </tr>';
    }

    $contents .= '
    <tr>
        <td><b>Total Deduction:</b></td>
        <td><b>'.number_format($row['total_deduction'],2).'</b></td>
    </tr>

    <tr><td colspan="2"><hr></td></tr>

    <tr>
        <td><b>Net Pay:</b></td>
        <td><b>'.number_format($row['net'],2).'</b></td>
    </tr>

    </table>';

    $pdf->writeHTML($contents, true, false, true, false, '');
}

if(ob_get_length()){
    ob_end_clean();
}

$pdf->Output('payslip.pdf', 'I');
?>
```
