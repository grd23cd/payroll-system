<style>
  /* Section dividers inside modal */
  .section-heading {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #fff;
    background: #3a5a8c;
    padding: 6px 12px;
    margin: 18px -15px 14px;
    border-radius: 0;
  }
  .section-heading:first-of-type { margin-top: 0; }

  /* Separation section highlight */
  #separation_section,
  #edit_separation_section {
    background: #fff8f0;
    border: 1px solid #f0c070;
    border-radius: 6px;
    padding: 14px 14px 4px;
    margin-top: 10px;
    display: none; /* hidden by default */
  }
  #separation_section .section-heading,
  #edit_separation_section .section-heading {
    margin: -14px -14px 14px;
    border-radius: 4px 4px 0 0;
    background: #d08030;
  }

  .modal-dialog { width: 700px; }
  .modal-body { max-height: 72vh; overflow-y: auto; padding: 15px 15px 5px; }
  .modal-header { background: #2c3e6b; color: #fff; border-radius: 5px 5px 0 0; }
  .modal-header .close { color: #fff; opacity: 0.8; }
  .modal-title { color: #fff; }

  .form-group { margin-bottom: 10px; }
  .form-group label { font-size: 12.5px; font-weight: 600; color: #444; }
  .form-control { font-size: 13px; }

  /* Two-column grid for compact fields */
  .col-half { width: 50%; float: left; padding: 0 5px; }
  .row-fields::after { content: ''; display: table; clear: both; }

  .required-star { color: #c0392b; }
  .modal-footer { background: #f7f8fa; }
</style>

<!-- =====================================================================
     TRIGGER BUTTONS  (place these wherever you need them on your page)
     FIXED: added data-toggle and data-target so the modals actually open
====================================================================== -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addnew">
  Add New Employee
</button>


<!-- =====================================================================
     ADD EMPLOYEE MODAL
====================================================================== -->
<div class="modal fade" id="addnew" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><b>Add Employee</b></h4>
      </div>

      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="employee_add.php" enctype="multipart/form-data" id="addForm">

          <!-- ── Employment Status ── -->
          <div class="section-heading">Employment Status</div>

          <div class="form-group">
            <label class="col-sm-4 control-label">Employment Status <span class="required-star">*</span></label>
            <div class="col-sm-8">
              <select class="form-control" name="employment_status" id="add_employment_status" required>
                <option value="">- Select -</option>
                <option value="Active" selected>Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <!-- Separation block (shown only when Inactive) -->
          <div id="separation_section">
            <div class="section-heading">Separation Information</div>
            <div class="form-group">
              <label class="col-sm-4 control-label">Status <span class="required-star">*</span></label>
              <div class="col-sm-8">
                <select class="form-control" name="status" id="add_status">
                  <option value="">- Select Status -</option>
                  <option value="Resigned">Resigned</option>
                  <option value="AWOL">AWOL</option>
                  <option value="Forced Leave">Forced Leave</option>
                  <option value="Terminated">Terminated</option>
                  <option value="Retired">Retired</option>
                  <option value="End of Contract">End of Contract</option>
                  <option value="Abandoned">Abandoned</option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 control-label">Separation Date</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" name="separation_date" id="datepicker_sep_add" placeholder="MM/DD/YYYY">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 control-label">Reason / Remarks</label>
              <div class="col-sm-8">
                <textarea class="form-control" name="reason_for_resignation" rows="3" placeholder="Describe the reason..."></textarea>
              </div>
            </div>
          </div>

          <!-- ── Personal Information ── -->
          <div class="section-heading">Personal Information</div>

          <div class="form-group">
            <label class="col-sm-4 control-label">Employee ID <span class="required-star">*</span></label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="employee_id" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label">Biometrics ID</label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="biometrics_id">
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Firstname <span class="required-star">*</span></label>
                <input type="text" class="form-control" name="firstname" required>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Middlename</label>
                <input type="text" class="form-control" name="middlename">
              </div>
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Lastname <span class="required-star">*</span></label>
                <input type="text" class="form-control" name="lastname" required>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Suffix</label>
                <select class="form-control" name="suffix">
                  <option value="">- None -</option>
                  <option>Jr.</option>
                  <option>Sr.</option>
                  <option>II</option>
                  <option>III</option>
                  <option>IV</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Birthdate<span class="required-star">*</span></label>
                <input type="text" class="form-control" id="datepicker_add" name="birthdate" required placeholder="MM/DD/YYYY">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Gender</label>
                <select class="form-control" name="gender">
                  <option value="">- Select -</option>
                  <option>Male</option>
                  <option>Female</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Marital Status <span class="required-star">*</span></label>
                <select class="form-control" name="marital_status" required>
                  <option value="">- Select -</option>
                  <option>Single</option>
                  <option>Married</option>
                  <option>Widowed</option>
                  <option>Separated</option>
                  <option>Divorced</option>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Religion</label>
                <input type="text" class="form-control" name="religion" placeholder="e.g. Roman Catholic">
              </div>
            </div>
          </div>

          <!-- ── Contact Information ── -->
          <div class="section-heading">Contact Information</div>

          <div class="form-group">
            <label class="col-sm-4 control-label">Home Address <span class="required-star">*</span></label>
            <div class="col-sm-8">
              <textarea class="form-control" name="home_address" rows="2" required></textarea>
            </div>
          </div>
          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Contact No.</label>
                <input type="text" class="form-control" name="contact">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Email Address</label>
                <input type="email" class="form-control" name="email_address">
              </div>
            </div>
          </div>

          <!-- ── Educational Background ── -->
          <div class="section-heading">Educational Background</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Highest Educational Attainment <span class="required-star">*</span></label>
                <select class="form-control" name="highest_educational_attainment" required>
                  <option value="">- Select -</option>
                  <option>Elementary Graduate</option>
                  <option>High School Graduate</option>
                  <option>Vocational / Technical</option>
                  <option>Some College</option>
                  <option>College Graduate</option>
                  <option>Post-Graduate (Masteral)</option>
                  <option>Post-Graduate (Doctoral)</option>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Course / Field of Study</label>
                <input type="text" class="form-control" name="course_study" placeholder="e.g. BS Computer Science">
              </div>
            </div>
          </div>

          <!-- ── Emergency Contact ── -->
          <div class="section-heading">Emergency Contact</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Name <span class="required-star">*</span></label>
                <input type="text" class="form-control" name="emergency_contact_name" required>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Relationship <span class="required-star">*</span></label>
                <select class="form-control" name="emergency_contact_relationship" required>
                  <option value="">- Select -</option>
                  <option>Spouse</option>
                  <option>Parent</option>
                  <option>Sibling</option>
                  <option>Child</option>
                  <option>Relative</option>
                  <option>Friend</option>
                  <option>Other</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label">Contact Number <span class="required-star">*</span></label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="emergency_contact_number" required>
            </div>
          </div>

          <!-- ── Job Information ── -->
          <div class="section-heading">Job Information</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Position <span class="required-star">*</span></label>
                <select class="form-control" name="position">
                  <?php
                  $sql = "SELECT * FROM position";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                      echo "<option value='".$row['id']."'>".$row['description']."</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Designation <span class="required-star">*</span></label>
                <input type="text" class="form-control" name="designation" required placeholder="e.g. Team Lead">
              </div>
            </div>
          </div>
          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Schedule</label>
                <select class="form-control" name="schedule">
                  <?php
                  $sql = "SELECT * FROM schedules";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                      echo "<option value='".$row['id']."'>".$row['time_in'].' - '.$row['time_out']."</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Hired Date</label>
                <input type="text" class="form-control" id="datepicker_hired_add" name="hired_date" placeholder="MM/DD/YYYY">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 control-label">Salary <span class="required-star">*</span></label>
            <div class="col-sm-8">
              <input type="number" step="0.01" min="0" class="form-control" name="salary" required placeholder="0.00">
            </div>
          </div>

          <!-- ── Government IDs ── -->
          <div class="section-heading">Government IDs</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>SSS Number</label>
                <input type="text" class="form-control" name="sss_number" placeholder="00-0000000-0">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Pag-IBIG Number</label>
                <input type="text" class="form-control" name="pagibig_number" placeholder="0000-0000-0000">
              </div>
            </div>
          </div>
          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>TIN Number</label>
                <input type="text" class="form-control" name="tin_number" placeholder="000-000-000">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>PhilHealth Number</label>
                <input type="text" class="form-control" name="philhealth_number" placeholder="00-000000000-0">
              </div>
            </div>
          </div>

          <!-- ── Photo ── -->
          <div class="section-heading">Photo</div>
          <div class="form-group">
            <label class="col-sm-4 control-label">Upload Photo</label>
            <div class="col-sm-8">
              <input type="file" name="photo" accept="image/*">
            </div>
          </div>

        </form>
      </div><!-- /modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="addForm" class="btn btn-primary" name="add">Save Employee</button>
      </div>

    </div>
  </div>
</div>


<!-- =====================================================================
     EDIT EMPLOYEE MODAL
====================================================================== -->
<div class="modal fade" id="edit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><b>Edit Employee – <span class="employee_id"></span></b></h4>
      </div>

      <div class="modal-body">
        <form method="POST" action="employee_edit.php" id="editForm">
          <input type="hidden" class="empid" name="id">

          <!-- ── Employment Status ── -->
          <div class="section-heading">Employment Status</div>

          <div class="form-group">
            <label>Employment Status <span class="required-star">*</span></label>
            <select class="form-control" name="employment_status" id="edit_employment_status" required>
              <option value="">- Select -</option>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <!-- Separation block -->
          <div id="edit_separation_section">
            <div class="section-heading">Separation Information</div>
            <div class="form-group">
              <label>Status <span class="required-star">*</span></label>
              <select class="form-control" id="edit_status" name="status">
                <option value="">- Select Status -</option>
                <option value="Resigned">Resigned</option>
                <option value="AWOL">AWOL</option>
                <option value="Forced Leave">Forced Leave</option>
                <option value="Terminated">Terminated</option>
                <option value="Retired">Retired</option>
                <option value="End of Contract">End of Contract</option>
                <option value="Abandoned">Abandoned</option>
              </select>
            </div>
            <div class="form-group">
              <label>Separation Date</label>
              <input type="text" class="form-control" name="separation_date" id="datepicker_sep_edit" placeholder="MM/DD/YYYY">
            </div>
            <div class="form-group">
              <label>Reason / Remarks</label>
              <textarea class="form-control" name="reason_for_resignation" id="edit_reason_for_resignation" rows="3" placeholder="Describe the reason..."></textarea>
            </div>
          </div>

          <!-- ── Personal Information ── -->
          <div class="section-heading">Personal Information</div>

          <div class="form-group">
            <label>Employee ID <span class="required-star">*</span></label>
            <input type="text" class="form-control" id="edit_employee_id" name="employee_id" required>
          </div>
          <div class="form-group">
            <label>Biometrics ID</label>
            <input type="text" class="form-control" id="edit_biometrics_id" name="biometrics_id">
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Firstname <span class="required-star">*</span></label>
                <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Middlename</label>
                <input type="text" class="form-control" id="edit_middlename" name="middlename">
              </div>
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Lastname <span class="required-star">*</span></label>
                <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Suffix</label>
                <select class="form-control" id="edit_suffix" name="suffix">
                  <option value="">- None -</option>
                  <option>Jr.</option>
                  <option>Sr.</option>
                  <option>II</option>
                  <option>III</option>
                  <option>IV</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Birthdate <span class="required-star">*</span></label>
                <input type="text" class="form-control" id="datepicker_edit" name="birthdate" required placeholder="MM/DD/YYYY">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Gender</label>
                <select class="form-control" id="edit_gender" name="gender">
                  <option value="">- Select -</option>
                  <option>Male</option>
                  <option>Female</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Marital Status <span class="required-star">*</span></label>
                <select class="form-control" id="edit_marital_status" name="marital_status" required>
                  <option value="">- Select -</option>
                  <option>Single</option>
                  <option>Married</option>
                  <option>Widowed</option>
                  <option>Separated</option>
                  <option>Divorced</option>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Religion</label>
                <input type="text" class="form-control" id="edit_religion" name="religion" placeholder="e.g. Roman Catholic">
              </div>
            </div>
          </div>

          <!-- ── Contact Information ── -->
          <div class="section-heading">Contact Information</div>

          <div class="form-group">
            <label>Home Address <span class="required-star">*</span></label>
            <textarea class="form-control" id="edit_home_address" name="home_address" rows="2" required></textarea>
          </div>
          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Contact No.</label>
                <input type="text" class="form-control" id="edit_contact" name="contact">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Email Address</span></label>
                <input type="email" class="form-control" id="edit_email_address" name="email_address">
              </div>
            </div>
          </div>

          <!-- ── Educational Background ── -->
          <div class="section-heading">Educational Background</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Highest Educational Attainment <span class="required-star">*</span></label>
                <select class="form-control" id="edit_highest_educational_attainment" name="highest_educational_attainment" required>
                  <option value="">- Select -</option>
                  <option>Elementary Graduate</option>
                  <option>High School Graduate</option>
                  <option>Vocational / Technical</option>
                  <option>Some College</option>
                  <option>College Graduate</option>
                  <option>Post-Graduate (Masteral)</option>
                  <option>Post-Graduate (Doctoral)</option>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Course / Field of Study</label>
                <input type="text" class="form-control" id="edit_course_study" name="course_study" placeholder="e.g. BS Computer Science">
              </div>
            </div>
          </div>

          <!-- ── Emergency Contact ── -->
          <div class="section-heading">Emergency Contact</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Name <span class="required-star">*</span></label>
                <input type="text" class="form-control" id="edit_emergency_contact_name" name="emergency_contact_name" required>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Relationship <span class="required-star">*</span></label>
                <select class="form-control" id="edit_emergency_contact_relationship" name="emergency_contact_relationship" required>
                  <option value="">- Select -</option>
                  <option>Spouse</option>
                  <option>Parent</option>
                  <option>Sibling</option>
                  <option>Child</option>
                  <option>Relative</option>
                  <option>Friend</option>
                  <option>Other</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Emergency Contact Number <span class="required-star">*</span></label>
            <input type="text" class="form-control" id="edit_emergency_contact_number" name="emergency_contact_number" required>
          </div>

          <!-- ── Job Information ── -->
          <div class="section-heading">Job Information</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Position</label>
                <select class="form-control" id="edit_position" name="position">
                  <option id="position_val"></option>
                  <?php
                  $sql = "SELECT * FROM position";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                      echo "<option value='".$row['id']."'>".$row['description']."</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Designation <span class="required-star">*</span></label>
                <input type="text" class="form-control" id="edit_designation" name="designation" required placeholder="e.g. Team Lead">
              </div>
            </div>
          </div>
          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>Schedule</label>
                <select class="form-control" id="edit_schedule" name="schedule">
                  <option id="schedule_val"></option>
                  <?php
                  $sql = "SELECT * FROM schedules";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                      echo "<option value='".$row['id']."'>".$row['time_in'].' - '.$row['time_out']."</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Hired Date</label>
                <input type="text" class="form-control" id="datepicker_hired_edit" name="hired_date" placeholder="MM/DD/YYYY">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Salary <span class="required-star">*</span></label>
            <input type="number" step="0.01" min="0" class="form-control" id="edit_salary" name="salary" required placeholder="0.00">
          </div>

          <!-- ── Government IDs ── -->
          <div class="section-heading">Government IDs</div>

          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>SSS Number</label>
                <input type="text" class="form-control" id="edit_sss_number" name="sss_number" placeholder="00-0000000-0">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>Pag-IBIG Number</label>
                <input type="text" class="form-control" id="edit_pagibig_number" name="pagibig_number" placeholder="0000-0000-0000">
              </div>
            </div>
          </div>
          <div class="row-fields">
            <div class="col-half">
              <div class="form-group">
                <label>TIN Number</label>
                <input type="text" class="form-control" id="edit_tin_number" name="tin_number" placeholder="000-000-000">
              </div>
            </div>
            <div class="col-half">
              <div class="form-group">
                <label>PhilHealth Number</label>
                <input type="text" class="form-control" id="edit_philhealth_number" name="philhealth_number" placeholder="00-000000000-0">
              </div>
            </div>
          </div>

        </form>
      </div><!-- /modal-body -->

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" form="editForm" class="btn btn-success" name="edit">Update Employee</button>
      </div>

    </div>
  </div>
</div>


<!-- =====================================================================
     PHOTO UPDATE MODAL
====================================================================== -->
<div class="modal fade" id="photoModal" tabindex="-1">
  <div class="modal-dialog" style="width:420px;">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title"><b><span id="photo_modal_name"></span></b></h4>
      </div>

      <div class="modal-body" style="padding:20px 24px 0;">
        <form method="POST" action="employee_photo_update.php" enctype="multipart/form-data" id="photoForm">
          <input type="hidden" name="id" id="photo_employee_db_id">

          <div style="text-align:center; margin-bottom:16px;">
            <img id="photo_current_preview" src="" alt="Current Photo"
                 style="width:90px; height:90px; border-radius:50%; object-fit:cover;
                        border:3px solid #3a5a8c; display:none;">
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label style="font-size:12.5px; font-weight:600; color:#444;">Upload New Photo</label>
            <input type="file" name="photo" id="photo_file_input" accept="image/*" style="margin-top:4px;">
            <div style="text-align:center; margin-top:12px; display:none;" id="new_photo_preview_wrap">
              <img id="new_photo_preview" src="" alt="New Photo Preview"
                   style="width:90px; height:90px; border-radius:50%; object-fit:cover;
                          border:3px solid #27ae60;">
            </div>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <span class="glyphicon glyphicon-remove"></span> Close
        </button>
        <button type="submit" form="photoForm" class="btn btn-success" name="update_photo">
          <span class="glyphicon glyphicon-ok"></span> Update
        </button>
      </div>

    </div>
  </div>
</div>

<!-- =====================================================================
     SCRIPTS
====================================================================== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<script>
$(function(){

  // ── Datepickers ──────────────────────────────────────────────────
  var dpOpts = { dateFormat: 'mm/dd/yy', changeMonth: true, changeYear: true, yearRange: '1950:+10' };
  $('#datepicker_add').datepicker(dpOpts);
  $('#datepicker_edit').datepicker(dpOpts);
  $('#datepicker_hired_add').datepicker(dpOpts);
  $('#datepicker_hired_edit').datepicker(dpOpts);
  $('#datepicker_sep_add').datepicker(dpOpts);
  $('#datepicker_sep_edit').datepicker(dpOpts);

  // ── Separation section: only show when Inactive ──────────────────
  function toggleSeparation(selectEl, sectionId) {
    if ($(selectEl).val() === 'Inactive') {
      $(sectionId).slideDown(200);
    } else {
      $(sectionId).slideUp(200);
    }
  }

  // ADD modal
  $('#add_employment_status').on('change', function(){
    toggleSeparation(this, '#separation_section');
    if ($(this).val() !== 'Inactive') {
      $('#add_status').val('');
    }
  });

  // EDIT modal — also trigger on open so it reflects saved DB value
  $('#edit_employment_status').on('change', function(){
    toggleSeparation(this, '#edit_separation_section');
    if ($(this).val() !== 'Inactive') {
      $('#edit_status').val('');
    }
  });

  // ── Show separation block when edit modal opens with Inactive status ──
  // Call this from your edit button click handler after populating the fields:
  //   if (employmentStatus === 'Inactive') { $('#edit_separation_section').show(); }
  // OR wire it here if you populate via AJAX:
  $('#edit').on('shown.bs.modal', function(){
    toggleSeparation('#edit_employment_status', '#edit_separation_section');
  });

  // ── Reset separation section when modals close ───────────────────
  $('#addnew').on('hidden.bs.modal', function(){
    $('#separation_section').hide();
    $('#add_employment_status').val('Active');
    $('#add_status').val('');
  });
  $('#edit').on('hidden.bs.modal', function(){
    $('#edit_separation_section').hide();
    $('#edit_status').val('');
  });

  // ── Edit button handler (example — adapt to your table) ──────────
  // If your edit buttons look like:
  //   <button class="btn-edit" data-id="..." data-firstname="..." ...>Edit</button>
  // then populate and open the modal like this:
  $(document).on('click', '.btn-edit', function(){
    var d = $(this).data();

    // ── CLEAR the form first so no previous employee's data bleeds in ──
    $('#editForm')[0].reset();
    $('#edit_separation_section').hide();
    $('#datepicker_edit').val('');
    $('#datepicker_hired_edit').val('');
    $('#datepicker_sep_edit').val('');

    // Hidden ID
    $('.empid').val(d.id);
    $('.employee_id').text(d.employee_id || d.id);  // ← use snake_case to match data-*

    // Employment status + separation
    $('#edit_employment_status').val(d.employment_status).trigger('change');
    $('#edit_status').val(d.status || '');
    $('#datepicker_sep_edit').val(d.separation_date || '');
    $('#edit_reason_for_resignation').val(d.reason_for_resignation || '');

    // Personal
    $('#edit_employee_id').val(d.employee_id || '');
    $('#edit_biometrics_id').val(d.biometrics_id || '');
    $('#edit_firstname').val(d.firstname || '');
    $('#edit_middlename').val(d.middlename || '');
    $('#edit_lastname').val(d.lastname || '');
    $('#edit_suffix').val(d.suffix || '');
    $('#datepicker_edit').val(d.birthdate || '');
    $('#edit_gender').val(d.gender || '');
    $('#edit_marital_status').val(d.marital_status || '');
    $('#edit_religion').val(d.religion || '');

    // Contact — THIS was the bug: d.homeAddress → d.home_address
    $('#edit_home_address').val(d.home_address || '');
    $('#edit_contact').val(d.contact || '');
    $('#edit_email_address').val(d.email_address || '');

    // Education
    $('#edit_highest_educational_attainment').val(d.highest_educational_attainment || '');
    $('#edit_course_study').val(d.course_study || '');

    // Emergency
    $('#edit_emergency_contact_name').val(d.emergency_contact_name || '');
    $('#edit_emergency_contact_relationship').val(d.emergency_contact_relationship || '');
    $('#edit_emergency_contact_number').val(d.emergency_contact_number || '');

    // Job
    $('#edit_position').val(d.position || '');
    $('#edit_designation').val(d.designation || '');
    $('#edit_schedule').val(d.schedule || '');
    $('#datepicker_hired_edit').val(d.hired_date || '');
    $('#edit_salary').val(d.salary || '');

    // Government IDs
    $('#edit_sss_number').val(d.sss_number || '');
    $('#edit_pagibig_number').val(d.pagibig_number || '');
    $('#edit_tin_number').val(d.tin_number || '');
    $('#edit_philhealth_number').val(d.philhealth_number || '');

    $('#edit').modal('show');
  });

  // ── Photo edit icon click ─────────────────────────────────────────
    $(document).on('click', '.photo', function(){
      var id    = $(this).data('id');
      var name  = $(this).data('name');
      var photo = $(this).data('photo');

      $('#photo_employee_db_id').val(id);
      $('#photo_modal_name').text(name || 'Employee');

      if (photo) {
        $('#photo_current_preview').attr('src', photo).show();
      } else {
        $('#photo_current_preview').hide();
      }

      $('#photo_file_input').val('');
      $('#new_photo_preview_wrap').hide();
      $('#new_photo_preview').attr('src', '');

      $('#photoModal').modal('show');
    });

    // ── Live preview of newly selected photo ─────────────────────────
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

    // ── Reset photo modal on close ────────────────────────────────────
    $('#photoModal').on('hidden.bs.modal', function(){
      $('#photo_file_input').val('');
      $('#new_photo_preview_wrap').hide();
      $('#photo_current_preview').hide().attr('src', '');
    });

});
</script>