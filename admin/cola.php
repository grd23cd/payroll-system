<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/menubar.php'; ?>

<div class="content-wrapper">

<section class="content-header">
  <h1>Cost of Living Allowance</h1>
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
    <h3 class="box-title">COLA Settings</h3>

    <a href="cola_toggle.php" class="btn btn-warning btn-sm btn-flat pull-right">
      <i class="fa fa-power-off"></i> Enable / Disable
    </a>
  </div>

  <div class="box-body">

    <table class="table table-bordered">
      <thead>
        <th>Description</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Tools</th>
      </thead>

      <tbody>
        <?php
          $sql = "SELECT * FROM cola LIMIT 1";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
        ?>

        <tr>
          <td>Cost of Living Allowance</td>
          <td><?= number_format((double)$row['amount'], 2) ?></td>

          <td>
            <?php if($row['status'] == 1): ?>
              <span class="label label-success">Enabled</span>
            <?php else: ?>
              <span class="label label-danger">Disabled</span>
            <?php endif; ?>
          </td>

          <td>
            <button class="btn btn-success btn-sm edit btn-flat" data-id="<?= $row['id'] ?>">
              <i class="fa fa-edit"></i> Edit
            </button>
          </td>
        </tr>

      </tbody>
    </table>

  </div>
</div>

</section>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/cola_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(function(){

  $('.edit').click(function(e){
    e.preventDefault();
    $('#edit').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'cola_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.cola_id').val(response.id);
      $('#edit_amount').val(response.amount);
    }
  });
}
</script>

</body>
</html>