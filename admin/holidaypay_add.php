<?php
include 'includes/session.php';

if(isset($_POST['add'])){

  $employee = $_POST['employee'];
  $date = $_POST['date'];
  $type = $_POST['type'];
  $hours = $_POST['hours'];
  $rate = $_POST['rate'];
  $percentage = $_POST['percentage'];

  $sql = "INSERT INTO holiday_pay (employee_id, date_holiday, type, hours, rate, percentage)
          VALUES ('$employee', '$date', '$type', '$hours', '$rate', '$percentage')";

  if($conn->query($sql)){
    $_SESSION['success'] = "Holiday pay added successfully";
  }
  else{
    $_SESSION['error'] = $conn->error;
  }
}
header('location: holiday_pay.php');