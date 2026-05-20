<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Attendance</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Attendance</li>
      </ol>
    </section>

    <section class="content">

      <?php
        if(isset($_SESSION['error'])){
          echo "<div class='alert alert-danger alert-dismissible'>
                  <button type='button' class='close' data-dismiss='alert'>&times;</button>
                  <h4><i class='icon fa fa-warning'></i> Error!</h4>
                  ".$_SESSION['error']."
                </div>";
          unset($_SESSION['error']);
        }

        if(isset($_SESSION['success'])){
          echo "<div class='alert alert-success alert-dismissible'>
                  <button type='button' class='close' data-dismiss='alert'>&times;</button>
                  <h4><i class='icon fa fa-check'></i> Success!</h4>
                  ".$_SESSION['success']."
                </div>";
          unset($_SESSION['success']);
        }
      ?>

      <div class="row">
        <div class="col-xs-12">
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
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Tools</th>
                </thead>

                <tbody>
                  <?php

                    $sql = "
                      SELECT 
                        attendance.id AS attid,
                        attendance.date,
                        attendance.time_in AS att_time_in,
                        attendance.time_out AS att_time_out,
                        attendance.status,

                        employees.employee_id AS empid,
                        employees.firstname,
                        employees.lastname,

                        schedules.time_out AS schedule_time_out

                      FROM attendance
                      LEFT JOIN employees ON employees.id = attendance.employee_id
                      LEFT JOIN schedules ON schedules.id = employees.schedule_id

                      ORDER BY STR_TO_DATE(attendance.date, '%Y-%m-%d') DESC, attendance.time_in DESC
                    ";

                    $query = $conn->query($sql);

                    while($row = $query->fetch_assoc()){

                      $status = ($row['status'])
                        ? '<span class="label label-warning pull-right">ontime</span>'
                        : '<span class="label label-danger pull-right">late</span>';

                      $undertime = '';

                      $date = $row['date'];
                      $isSaturday = (date('N', strtotime($date)) == 6);

                      $time_in = strtotime($row['att_time_in']);
                      $time_out = strtotime($row['att_time_out']);

                      if($isSaturday){

                        if(!empty($row['att_time_in']) && !empty($row['att_time_out'])){

                          $worked_hours = ($time_out - $time_in) / 3600;

                          if($worked_hours < 3){
                            $undertime = '<span class="label label-info pull-right" style="margin-right:5px;">undertime</span>';
                          }
                        }

                      } else {

                        if(!empty($row['att_time_out']) && !empty($row['schedule_time_out'])){

                          $actual_out = strtotime($row['att_time_out']);
                          $required_out = strtotime($row['schedule_time_out']);

                          if($actual_out < $required_out){
                            $undertime = '<span class="label label-info pull-right" style="margin-right:5px;">undertime</span>';
                          }
                        }
                      }

                      echo "
                        <tr>
                          <td data-order='".date('Y-m-d', strtotime($row['date']))."'>
                            ".date('M d, Y', strtotime($row['date']))."
                          </td>
                          <td>".$row['empid']."</td>
                          <td>".$row['firstname'].' '.$row['lastname']."</td>

                          <td>
                            ".date('h:i A', strtotime($row['att_time_in']))."
                            ".$status."
                            ".$undertime."
                          </td>

                          <td>".date('h:i A', strtotime($row['att_time_out']))."</td>

                          <td>
                            <button class='btn btn-success btn-sm btn-flat edit' data-id='".$row['attid']."'>
                              <i class='fa fa-edit'></i> Edit
                            </button>

                            <button class='btn btn-danger btn-sm btn-flat delete' data-id='".$row['attid']."'>
                              <i class='fa fa-trash'></i> Delete
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
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/attendance_modal.php'; ?>

</div>

<?php include 'includes/scripts.php'; ?>
<?php include 'includes/datatable_initializer.php'; ?>

<script>
$(function(){

  $('.edit').click(function(e){
    e.preventDefault();
    $('#edit').modal('show');
    getRow($(this).data('id'));
  });

  $('.delete').click(function(e){
    e.preventDefault();
    $('#delete').modal('show');
    getRow($(this).data('id'));
  });

});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'attendance_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('#datepicker_edit').val(response.date);
      $('#attendance_date').html(response.date);

      $('#edit_time_in').val(response.time_in);
      $('#edit_time_out').val(response.time_out);

      $('#attid').val(response.attid);
      $('#employee_name').html(response.firstname + ' ' + response.lastname);

      $('#del_attid').val(response.attid);
      $('#del_employee_name').html(response.firstname + ' ' + response.lastname);
    }
  });
}
</script>

</body>
</html>