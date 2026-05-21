<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">

    <section class="content-header">
      <h1>Employee List</h1>

      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Employees</li>
        <li class="active">Employee List</li>
      </ol>
    </section>

    <section class="content">

      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }

        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
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
                  <th>Employee ID</th>
                  <th>Photo</th>
                  <th>Name</th>
                  <th>Position</th>
                  <th>Schedule</th>
                  <th>Member Since</th>
                  <th>Tools</th>
                </thead>

                <tbody>
                  <?php
                    $sql = "SELECT *, employees.id AS empid
                            FROM employees
                            LEFT JOIN position ON position.id=employees.position_id
                            LEFT JOIN schedules ON schedules.id=employees.schedule_id";

                    $query = $conn->query($sql);

                    while($row = $query->fetch_assoc()){
                  ?>
                    <tr>
                      <td><?php echo $row['employee_id']; ?></td>

                      <td>
                        <img src="<?php echo (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg'; ?>"
                             width="30px" height="30px">

                        <!-- FIXED: added data-name and data-photo, removed data-toggle/href that pointed to old #edit_photo -->
                        <a href="javascript:void(0)"
                           class="pull-right photo"
                           data-id="<?php echo $row['empid']; ?>"
                           data-name="<?php echo strtoupper($row['lastname'].', '.$row['firstname']); ?>"
                           data-photo="<?php echo (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg'; ?>">
                          <span class="fa fa-edit"></span>
                        </a>
                      </td>

                      <td><?php echo $row['firstname'].' '.$row['lastname']; ?></td>
                      <td><?php echo $row['description']; ?></td>
                      <td><?php echo date('h:i A', strtotime($row['time_in'])).' - '.date('h:i A', strtotime($row['time_out'])); ?></td>
                      <td>
                        <?php 
                          echo (!empty($row['hired_date']))
                            ? date('M d, Y', strtotime($row['hired_date']))
                            : 'N/A';
                        ?>
                      </td>
                      <td>
                        <button class="btn btn-success btn-sm edit btn-flat"
                                data-id="<?php echo $row['empid']; ?>">
                          <i class="fa fa-edit"></i> Edit
                        </button>

                        <button class="btn btn-danger btn-sm delete btn-flat"
                                data-id="<?php echo $row['empid']; ?>">
                          <i class="fa fa-trash"></i> Delete
                        </button>

                        <!-- PERSONAL DEDUCTIONS BUTTON -->
                        <a href="personal_deduction.php?id=<?php echo $row['employee_id']; ?>"
                           class="btn btn-warning btn-sm btn-flat">
                          <i class="fa fa-money"></i> Personal Deductions
                        </a>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>

              </table>
            </div>

          </div>
        </div>
      </div>

    </section>
  </div>

  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/employee_modal.php'; ?>

</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){

  // ── Edit employee ────────────────────────────────────────────────
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    getRow($(this).data('id'), 'edit');
  });

  // ── Delete employee ──────────────────────────────────────────────
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    getRow($(this).data('id'), 'delete');
  });

  // ── Photo edit icon ───────────────────────────────────────
  $(document).on('click', '.photo', function(e){
    e.preventDefault();

    var id    = $(this).data('id');
    var name  = $(this).data('name');
    var photo = $(this).data('photo');

    // Populate the photo modal directly from data attributes
    $('#photo_employee_db_id').val(id);
    $('#photo_modal_name').text(name);

    // Show current photo preview
    if (photo) {
      $('#photo_current_preview').attr('src', photo).show();
    } else {
      $('#photo_current_preview').hide();
    }

    // Reset file input and new preview
    $('#photo_file_input').val('');
    $('#new_photo_preview_wrap').hide();
    $('#new_photo_preview').attr('src', '');

    $('#photoModal').modal('show');
  });

  // ── Live preview when a new photo is selected ────────────────────
  $(document).on('change', '#photo_file_input', function(){
    var file = this.files[0];
    if (file && file.type.startsWith('image/')) {
      var reader = new FileReader();
      reader.onload = function(e){
        $('#new_photo_preview').attr('src', e.target.result);
        $('#new_photo_preview_wrap').show();
      };
      reader.readAsDataURL(file);
    } else {
      $('#new_photo_preview_wrap').hide();
    }
  });

  // ── Reset photo modal on close ───────────────────────────────────
  $('#photoModal').on('hidden.bs.modal', function(){
    $('#photo_file_input').val('');
    $('#new_photo_preview_wrap').hide();
    $('#photo_current_preview').hide().attr('src', '');
  });

});

// ── getRow: used by Edit and Delete buttons ──────────────────────────
function getRow(id, action){
  $.ajax({
    type: 'POST',
    url: 'employee_row.php',
    data: {id: id},
    dataType: 'json',
    success: function(r){

      // ── Reset form first ─────────────────────────────────────────
      $('#editForm')[0].reset();
      $('#edit_separation_section').hide();
      $('#datepicker_edit').val('');
      $('#datepicker_hired_edit').val('');
      $('#datepicker_sep_edit').val('');

      // ── Shared ───────────────────────────────────────────────────
      $('.empid').val(r.empid);
      $('.employee_id').html(r.employee_id);
      $('.del_employee_name').html(r.firstname + ' ' + r.lastname);

      // ── Employment status ────────────────────────────────────────
      $('#edit_employment_status').val(r.employment_status).trigger('change');
      $('#edit_status').val(r.status || '');
      $('#datepicker_sep_edit').val(r.separation_date || '');
      $('#edit_reason_for_resignation').val(r.reason_for_resignation || '');

      // ── Personal ─────────────────────────────────────────────────
      $('#edit_employee_id').val(r.employee_id);
      $('#edit_biometrics_id').val(r.biometrics_id || '');
      $('#edit_firstname').val(r.firstname);
      $('#edit_middlename').val(r.middlename || '');
      $('#edit_lastname').val(r.lastname);
      $('#edit_suffix').val(r.suffix || '');
      $('#datepicker_edit').val(r.birthdate || '');
      $('#edit_gender').val(r.gender || '');
      $('#edit_marital_status').val(r.marital_status || '');
      $('#edit_religion').val(r.religion || '');

      // ── Contact ──────────────────────────────────────────────────
      $('#edit_home_address').val(r.address || '');  
      $('#edit_contact').val(r.contact_info || '');
      $('#edit_email_address').val(r.email_address || '');

      // ── Education ────────────────────────────────────────────────
      $('#edit_highest_educational_attainment').val(r.highest_educational_attainment || '');
      $('#edit_course_study').val(r.course_study || '');

      // ── Emergency contact ────────────────────────────────────────
      $('#edit_emergency_contact_name').val(r.emergency_contact_name || '');
      $('#edit_emergency_contact_relationship').val(r.emergency_contact_relationship || '');
      $('#edit_emergency_contact_number').val(r.emergency_contact_number || '');

      // ── Job ──────────────────────────────────────────────────────
      $('#position_val').val(r.position_id).html(r.description);
      $('#edit_designation').val(r.designation || '');
      $('#schedule_val').val(r.schedule_id).html(r.time_in + ' - ' + r.time_out);
      $('#datepicker_hired_edit').val(r.hired_date || '');
      $('#edit_salary').val(r.salary || '');

      // ── Government IDs ───────────────────────────────────────────
      $('#edit_sss_number').val(r.sss_number || '');
      $('#edit_pagibig_number').val(r.pagibig_number || '');
      $('#edit_tin_number').val(r.tin_number || '');
      $('#edit_philhealth_number').val(r.philhealth_number || '');

      // ── Open modal ───────────────────────────────────────────────
      if (action === 'edit')        $('#edit').modal('show');
      else if (action === 'delete') $('#delete').modal('show');
    }
  });
}
</script>

<?php include 'includes/datatable_initializer.php'; ?>
</body>
</html>