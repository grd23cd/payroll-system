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

<button type="button" class="btn btn-success btn-sm btn-flat" id="payroll">Payroll</button>
<button type="button" class="btn btn-primary btn-sm btn-flat" id="payslip">Payslip</button>

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
  <th>Net Pay</th>
</thead>

<tbody>

<?php

$to = date('Y-m-d');
$from = date('Y-m-d', strtotime('-30 day', strtotime($to)));

if(isset($_GET['range'])){
    $ex = explode(' - ', $_GET['range']);

    $from = date('Y-m-d', strtotime($ex[0]));
    $to   = date('Y-m-d', strtotime($ex[1]));
}

include 'includes/payroll_computation.php';

foreach($payroll_rows as $row){

    $emp = $row['emp'];

    echo "
    <tr>
      <td>{$emp['lastname']}, {$emp['firstname']}</td>
      <td>{$emp['emp_code']}</td>
      <td>".number_format($row['gross'],2)."</td>
      <td>".number_format($row['total_deduction'],2)."</td>
      <td>".number_format($row['net'],2)."</td>
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