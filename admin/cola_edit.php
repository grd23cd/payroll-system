<?php
include 'includes/session.php';

if(isset($_POST['edit'])){

  $id = $_POST['id'];
  $amount = (double) $_POST['amount'];

  $sql = "UPDATE cola SET amount = '$amount' WHERE id = '$id'";

  if($conn->query($sql)){
    $_SESSION['success'] = "COLA updated successfully";
  }
  else{
    $_SESSION['error'] = $conn->error;
  }

  header('location: cola.php');
}
?>