<?php

include 'includes/session.php';

if(isset($_POST['add'])){

    $employee_id = $_POST['employee_id'];

    $description = $_POST['description'];

    $amount = $_POST['amount'];

    $sql = "INSERT INTO personal_deductions
            (employee_id, description, amount)
            VALUES
            ('$employee_id', '$description', '$amount')";

    if($conn->query($sql)){
        $_SESSION['success'] =
        'Personal deduction added successfully';
    }
    else{
        $_SESSION['error'] = $conn->error;
    }
}
else{
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: personal_deduction.php?id='.$employee_id);

?>