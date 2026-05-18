<?php
include 'includes/session.php';

if(isset($_POST['id'])){

  $id = $_POST['id'];

  $sql = "SELECT holiday_pay.*, 
                 holiday_pay.id AS hid,
                 employees.firstname,
                 employees.lastname
          FROM holiday_pay
          LEFT JOIN employees ON employees.id = holiday_pay.employee_id
          WHERE holiday_pay.id = '$id'";

  $query = $conn->query($sql);
  $row = $query->fetch_assoc();

  echo json_encode($row);
}
?>