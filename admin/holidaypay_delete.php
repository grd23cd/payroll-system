<?php
include 'includes/session.php';

if(isset($_POST['delete'])){

  $id = $_POST['id'];

  $sql = "DELETE FROM holiday_pay WHERE id='$id'";

  if($conn->query($sql)){
    $_SESSION['success'] = "Holiday pay deleted successfully";
  }
  else{
    $_SESSION['error'] = $conn->error;
  }
}
header('location: holiday_pay.php');