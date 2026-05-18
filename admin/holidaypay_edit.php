<?php
include 'includes/session.php';

if(isset($_POST['edit'])){

  $id = $_POST['id'];
  $date = $_POST['date'];
  $type = $_POST['type'];
  $hours = $_POST['hours'];
  $rate = $_POST['rate'];
  $percentage = $_POST['percentage'];

  $sql = "UPDATE holiday_pay 
          SET date_holiday='$date', type='$type', hours='$hours', rate='$rate', percentage='$percentage'
          WHERE id='$id'";

  if($conn->query($sql)){
    $_SESSION['success'] = "Holiday pay updated successfully";
  }
  else{
    $_SESSION['error'] = $conn->error;
  }
}
header('location: holiday_pay.php');