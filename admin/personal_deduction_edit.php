<?php

include 'includes/session.php';

if(isset($_POST['edit'])){

    $id = $_POST['id'];

    $employee_id = $_POST['employee_id'];

    $description = $_POST['description'];

    $amount = $_POST['amount'];

    $sql = "UPDATE personal_deductions
            SET
              description = '$description',
              amount = '$amount'
            WHERE id = '$id'";

    if($conn->query($sql)){
        $_SESSION['success'] =
        'Personal deduction updated successfully';
    }
    else{
        $_SESSION['error'] = $conn->error;
    }
}
else{
    $_SESSION['error'] = 'Select item first';
}

header('location: personal_deduction.php?id='.$employee_id);

?>