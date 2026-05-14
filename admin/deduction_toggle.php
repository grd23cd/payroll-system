<?php
include 'includes/session.php';

// check if any are enabled
$sql = "SELECT COUNT(*) AS cnt FROM deductions WHERE status = 1";
$query = $conn->query($sql);
$row = $query->fetch_assoc();

if($row['cnt'] > 0){
    // disable all
    $conn->query("UPDATE deductions SET status = 0");
    $_SESSION['success'] = "All deductions have been disabled.";
}
else{
    // enable all
    $conn->query("UPDATE deductions SET status = 1");
    $_SESSION['success'] = "All deductions have been enabled.";
}

header("location: deduction.php");
exit();
?>