<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <form method="POST" action="holidaypay_add.php">
      <div class="modal-content">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4>Add Holiday Pay</h4>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label>Employee</label>
            <select name="employee" class="form-control" required>
              <?php
                $sql = "SELECT * FROM employees";
                $query = $conn->query($sql);
                while($row = $query->fetch_assoc()){
                  echo "<option value='".$row['id']."'>".$row['employee_id'].' - '.$row['firstname']."</option>";
                }
              ?>
            </select>
          </div>

          <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" class="form-control" required>
          </div>

          <div class="form-group">
            <label>Type</label>
            <input type="text" name="type" class="form-control" placeholder="Regular/Special" required>
          </div>

          <div class="form-group">
            <label>Hours</label>
            <input type="number" step="0.01" name="hours" class="form-control" placeholder="Hours" required>
          </div>

          <div class="form-group">
            <label>Rate</label>
            <input type="number" step="0.01" name="rate" class="form-control" placeholder="Rate" required>
          </div>

          <div class="form-group">
            <label>Percentage (%)</label>
            <input type="number" step="0.01" name="percentage" class="form-control" placeholder="e.g. 130" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" name="add" class="btn btn-primary">Save</button>
        </div>

      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <form method="POST" action="holidaypay_edit.php">
      <div class="modal-content">

        <input type="hidden" id="hid_edit" name="id">

        <div class="modal-header">
          <h4>Edit Holiday Pay</h4>
        </div>

        <div class="modal-body">

          <div class="form-group">
            <label>Date</label>
            <input type="date" id="date_edit" name="date" class="form-control">
          </div>

          <div class="form-group">
            <label>Type</label>
            <input type="text" id="type_edit" name="type" class="form-control">
          </div>

          <div class="form-group">
            <label>Hours</label>
            <input type="number" step="0.01" id="hours_edit" name="hours" class="form-control">
          </div>

          <div class="form-group">
            <label>Rate</label>
            <input type="number" step="0.01" id="rate_edit" name="rate" class="form-control">
          </div>

          <div class="form-group">
            <label>Percentage (%)</label>
            <input type="number" step="0.01" id="percentage_edit" name="percentage" class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" name="edit" class="btn btn-success">Update</button>
        </div>

      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <form method="POST" action="holidaypay_delete.php">
      <div class="modal-content">

        <input type="hidden" id="hid_delete" name="id">

        <div class="modal-header">
          <h4>Delete Holiday Pay</h4>
        </div>

        <div class="modal-body">
          <p>Are you sure you want to delete the holiday pay record for <b class="holiday_name"></b>?</p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
          <button type="submit" name="delete" class="btn btn-danger">Delete</button>
        </div>

      </div>
    </form>
  </div>
</div>