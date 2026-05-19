<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit COLA</h4>
      </div>

      <form action="cola_edit.php" method="POST">

        <div class="modal-body">

          <input type="hidden" class="cola_id" name="id">

          <div class="form-group">
            <label>Amount</label>
            <input type="number" step="0.01" class="form-control" id="edit_amount" name="amount" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">Close</button>
          <button type="submit" name="edit" class="btn btn-success btn-flat">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>