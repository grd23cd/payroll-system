<?php
include 'includes/session.php';

$sql = "SELECT * FROM cola LIMIT 1";
$query = $conn->query($sql);
$row = $query->fetch_assoc();

$newStatus = ($row['status'] == 1) ? 0 : 1;

$update = "UPDATE cola SET status = '$newStatus' WHERE id = '".$row['id']."'";

if($conn->query($update)){
  $_SESSION['success'] = "COLA status updated";
}
else{
  $_SESSION['error'] = $conn->error;
}

header('location: cola.php');
?>