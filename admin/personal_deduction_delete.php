<?php

include 'includes/session.php';

if(isset($_POST['delete'])){

    $id = $_POST['id'];

    $employee_id = $_POST['employee_id'];

    $sql = "DELETE FROM personal_deductions
            WHERE id = '$id'";

    if($conn->query($sql)){
        $_SESSION['success'] =
        'Personal deduction deleted successfully';
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