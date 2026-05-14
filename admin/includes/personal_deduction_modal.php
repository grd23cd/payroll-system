<!-- Add -->
<div class="modal fade" id="addnew">

<div class="modal-dialog">
<div class="modal-content">

<form class="form-horizontal"
      method="POST"
      action="personal_deduction_add.php">

<div class="modal-header">

<button type="button"
        class="close"
        data-dismiss="modal">

<span aria-hidden="true">&times;</span>

</button>

<h4 class="modal-title">
<b>Add Personal Deduction</b>
</h4>

</div>

<div class="modal-body">

<input type="hidden"
       name="employee_id"
       id="employee_id">

<div class="form-group">

<label class="col-sm-3 control-label">
Description
</label>

<div class="col-sm-9">

<input type="text"
       class="form-control"
       name="description"
       required>

</div>
</div>

<div class="form-group">

<label class="col-sm-3 control-label">
Amount
</label>

<div class="col-sm-9">

<input type="number"
       step="0.01"
       class="form-control"
       name="amount"
       required>

</div>
</div>

</div>

<div class="modal-footer">

<button type="button"
        class="btn btn-default btn-flat pull-left"
        data-dismiss="modal">

<i class="fa fa-close"></i> Close

</button>

<button type="submit"
        class="btn btn-primary btn-flat"
        name="add">

<i class="fa fa-save"></i> Save

</button>

</div>

</form>

</div>
</div>
</div>

<!-- Edit -->
<div class="modal fade" id="edit">

<div class="modal-dialog">
<div class="modal-content">

<form class="form-horizontal"
      method="POST"
      action="personal_deduction_edit.php">

<div class="modal-header">

<button type="button"
        class="close"
        data-dismiss="modal">

<span aria-hidden="true">&times;</span>

</button>

<h4 class="modal-title">
<b>Edit Personal Deduction</b>
</h4>

</div>

<div class="modal-body">

<input type="hidden"
       class="pdid"
       name="id">

<input type="hidden"
       name="employee_id"
       id="employee_id_edit">

<div class="form-group">

<label class="col-sm-3 control-label">
Description
</label>

<div class="col-sm-9">

<input type="text"
       class="form-control"
       id="edit_description"
       name="description"
       required>

</div>
</div>

<div class="form-group">

<label class="col-sm-3 control-label">
Amount
</label>

<div class="col-sm-9">

<input type="number"
       step="0.01"
       class="form-control"
       id="edit_amount"
       name="amount"
       required>

</div>
</div>

</div>

<div class="modal-footer">

<button type="button"
        class="btn btn-default btn-flat pull-left"
        data-dismiss="modal">

<i class="fa fa-close"></i> Close

</button>

<button type="submit"
        class="btn btn-success btn-flat"
        name="edit">

<i class="fa fa-check-square-o"></i> Update

</button>

</div>

</form>

</div>
</div>
</div>

<!-- Delete -->
<div class="modal fade" id="delete">

<div class="modal-dialog">
<div class="modal-content">

<form class="form-horizontal"
      method="POST"
      action="personal_deduction_delete.php">

<div class="modal-header">

<button type="button"
        class="close"
        data-dismiss="modal">

<span aria-hidden="true">&times;</span>

</button>

<h4 class="modal-title">
<b>Deleting...</b>
</h4>

</div>

<div class="modal-body">

<input type="hidden"
       class="pdid"
       name="id">

<input type="hidden"
       name="employee_id"
       id="employee_id_delete">

<div class="text-center">

<p>DELETE PERSONAL DEDUCTION</p>

<h2 id="del_personal_deduction"
    class="bold">
</h2>

</div>

</div>

<div class="modal-footer">

<button type="button"
        class="btn btn-default btn-flat pull-left"
        data-dismiss="modal">

<i class="fa fa-close"></i> Close

</button>

<button type="submit"
        class="btn btn-danger btn-flat"
        name="delete">

<i class="fa fa-trash"></i> Delete

</button>

</div>

</form>

</div>
</div>
</div>