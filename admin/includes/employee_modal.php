<!-- Add -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title"><b>Add Employee</b></h4>
            </div>

            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="employee_add.php" enctype="multipart/form-data">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Employee ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="employee_id" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Firstname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="firstname" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Lastname</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="lastname" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Address</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="address"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Birthdate</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="datepicker_add" name="birthdate">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Contact Info</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="contact">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Gender</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="gender">
                                <option value="">- Select -</option>
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Position</label>
                        <div class="col-sm-9">
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

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Schedule</label>
                        <div class="col-sm-9">
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

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" name="photo">
                        </div>
                    </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" name="add">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT MODAL (unchanged logic) -->
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><b><span class="employee_id"></span></b></h4>
            </div>

            <div class="modal-body">
                <form method="POST" action="employee_edit.php">

                    <input type="hidden" class="empid" name="id">

                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" class="form-control" id="edit_employee_id" name="employee_id">
                    </div>

                    <div class="form-group">
                        <label>Firstname</label>
                        <input type="text" class="form-control" id="edit_firstname" name="firstname">
                    </div>

                    <div class="form-group">
                        <label>Lastname</label>
                        <input type="text" class="form-control" id="edit_lastname" name="lastname">
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" id="edit_address" name="address"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Birthdate</label>
                        <input type="text" class="form-control" id="datepicker_edit" name="birthdate">
                    </div>

                    <div class="form-group">
                        <label>Contact</label>
                        <input type="text" class="form-control" id="edit_contact" name="contact">
                    </div>

                    <div class="form-group">
                        <label>Gender</label>
                        <select class="form-control" name="gender">
                            <option id="gender_val"></option>
                            <option>Male</option>
                            <option>Female</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Position</label>
                        <select class="form-control" name="position">
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

                    <div class="form-group">
                        <label>Schedule</label>
                        <select class="form-control" name="schedule">
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

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" name="edit">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>