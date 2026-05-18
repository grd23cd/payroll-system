<?php include 'includes/session.php'; ?>
<?php include '../timezone.php'; ?>

<?php
  $range_to = date('m/d/Y');
  $range_from = date('m/d/Y', strtotime('-30 day', strtotime($range_to)));
?>

<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/menubar.php'; ?>

<div class="content-wrapper">

<section class="content-header">
  <h1>Payroll</h1>

  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Payroll</li>
  </ol>
</section>

<section class="content">

<div class="box">

<div class="box-header with-border">

<div class="pull-right">

<form method="POST" class="form-inline" id="payForm">

<div class="input-group">
  <div class="input-group-addon">
    <i class="fa fa-calendar"></i>
  </div>

  <input type="text"
         class="form-control"
         id="reservation"
         name="date_range"
         value="<?php echo (isset($_GET['range'])) ? $_GET['range'] : $range_from.' - '.$range_to; ?>">
</div>

<button type="button" class="btn btn-success btn-sm btn-flat" id="payroll"><span class="glyphicon glyphicon-print"></span> Payroll</button>
<button type="button" class="btn btn-primary btn-sm btn-flat" id="payslip"><span class="glyphicon glyphicon-print"></span> Payslip</button>

</form>

</div>

</div>

<div class="box-body">

<table id="example1" class="table table-bordered">

<thead>
  <th>Employee Name</th>
  <th>Employee ID</th>
  <th>Gross</th>
  <th>Deductions</th>
  <th>Cash Advance</th>
  <th>Net Pay</th>
</thead>

<tbody>

<?php

$deduction = $conn->query("SELECT SUM(amount) as total_amount FROM deductions WHERE status=1")
                  ->fetch_assoc()['total_amount'] ?? 0;

$to = date('Y-m-d');
$from = date('Y-m-d', strtotime('-30 day', strtotime($to)));

if(isset($_GET['range'])){
  $ex = explode(' - ', $_GET['range']);
  $from = date('Y-m-d', strtotime($ex[0]));
  $to = date('Y-m-d', strtotime($ex[1]));
}

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
      'lastname' => $row['lastname'],
      'emp_code' => $row['emp_code'],
      'rate' => $row['rate'],
      'total_hr' => 0
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

foreach($employees as $empid => $emp){

  $id = $empid;

  $ca = $conn->query("SELECT SUM(amount) as cashamount
                      FROM cashadvance
                      WHERE employee_id='$id'
                      AND date_advance BETWEEN '$from' AND '$to'")
                      ->fetch_assoc()['cashamount'] ?? 0;

  $emp_code = $emp['emp_code'];

  $pd = $conn->query("SELECT SUM(amount) as pdamount
                      FROM personal_deductions
                      WHERE employee_id='$emp_code'")
                      ->fetch_assoc()['pdamount'] ?? 0;

  $regular = $emp['rate'] * $emp['total_hr'];

  $ot = $conn->query("SELECT SUM(hours * rate) as total_ot
                      FROM overtime
                      WHERE employee_id='$id'
                      AND date_overtime BETWEEN '$from' AND '$to'")
                      ->fetch_assoc()['total_ot'] ?? 0;

  $gross = $regular + $ot;

  $total_deduction = $deduction + $pd + $ca;

  $net = $gross - $total_deduction;

  echo "
    <tr>
      <td>".$emp['lastname'].", ".$emp['firstname']."</td>
      <td>".$emp['emp_code']."</td>
      <td>".number_format($gross,2)."</td>
      <td>".number_format($total_deduction,2)."</td>
      <td>".number_format($ca,2)."</td>
      <td>".number_format($net,2)."</td>
    </tr>
  ";
}

?>

</tbody>
</table>

</div>

</div>

</section>
</div>

<?php include 'includes/footer.php'; ?>

</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){

  $("#reservation").on('change', function(){
    window.location = 'payroll.php?range=' + encodeURI($(this).val());
  });

  $('#payroll').click(function(){
    $('#payForm').attr('action','payroll_generate.php').submit();
  });

  $('#payslip').click(function(){
    $('#payForm').attr('action','payslip_generate.php').submit();
  });

});
</script>

<?php include 'includes/datatable_initializer.php'; ?>

</body>
</html>