<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/menubar.php'; ?>

<div class="content-wrapper">

<section class="content-header">
  <h1>Holiday Pay</h1>
</section>

<section class="content">

<?php
if(isset($_SESSION['error'])){
  echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
  unset($_SESSION['error']);
}
if(isset($_SESSION['success'])){
  echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
  unset($_SESSION['success']);
}
?>

<div class="box">
  <div class="box-header with-border">
    <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat">
      <i class="fa fa-plus"></i> New
    </a>
  </div>

  <div class="box-body">
    <table id="example1" class="table table-bordered">
      <thead>
        <th>Date</th>
        <th>Employee ID</th>
        <th>Name</th>
        <th>Type</th>
        <th>Hours</th>
        <th>Rate</th>
        <th>Percentage (%)</th>
        <th>Tools</th>
      </thead>

      <tbody>
      <?php
        $sql = "SELECT holiday_pay.*, 
                       holiday_pay.id AS hid,
                       employees.employee_id AS empid,
                       employees.firstname,
                       employees.lastname
                FROM holiday_pay
                LEFT JOIN employees ON employees.id = holiday_pay.employee_id
                ORDER BY date_holiday DESC";

        $query = $conn->query($sql);

        while($row = $query->fetch_assoc()){
          echo "
          <tr>
            <td>".date('M d, Y', strtotime($row['date_holiday']))."</td>
            <td>".$row['empid']."</td>
            <td>".$row['firstname'].' '.$row['lastname']."</td>
            <td>".$row['type']."</td>
            <td>".$row['hours']."</td>
            <td>".$row['rate']."</td>
            <td>".$row['percentage']."%</td>
            <td>
              <button class='btn btn-success btn-sm edit' data-id='".$row['hid']."'>
                <i class='fa fa-edit'></i>
              </button>
              <button class='btn btn-danger btn-sm delete' data-id='".$row['hid']."'>
                <i class='fa fa-trash'></i>
              </button>
            </td>
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
<?php include 'includes/holidaypay_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){

  $(document).on('click', '.edit', function(){
    $('#edit').modal('show');
    getRow($(this).data('id'));
  });

  $(document).on('click', '.delete', function(){
    $('#delete').modal('show');
    getRow($(this).data('id'));
  });

});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'holidaypay_row.php',
    data: {id: id},
    dataType: 'json',
    success: function(response){
      $('.holiday_name').html(response.firstname + ' ' + response.lastname);
      $('#hid_edit').val(response.hid);
      $('#hid_delete').val(response.hid);
      $('#date_edit').val(response.date_holiday);
      $('#type_edit').val(response.type);
      $('#hours_edit').val(response.hours);
      $('#rate_edit').val(response.rate);
      $('#percentage_edit').val(response.percentage);
    }
  });
}
</script>

<?php include 'includes/datatable_initializer.php'; ?>
</body>
</html>