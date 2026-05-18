<?php
include 'includes/session.php';

if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $date = $_POST['edit_date'];

    $time_in = date('H:i:s', strtotime($_POST['edit_time_in']));
    $time_out = date('H:i:s', strtotime($_POST['edit_time_out']));

    $sql = "UPDATE attendance 
            SET date = '$date', time_in = '$time_in', time_out = '$time_out' 
            WHERE id = '$id'";

    if($conn->query($sql)){
        $_SESSION['success'] = 'Attendance updated successfully';

        $sql = "SELECT * FROM attendance WHERE id = '$id'";
        $query = $conn->query($sql);
        $row = $query->fetch_assoc();
        $emp = $row['employee_id'];

        $sql = "SELECT * FROM employees 
                LEFT JOIN schedules ON schedules.id=employees.schedule_id 
                WHERE employees.id = '$emp'";
        $query = $conn->query($sql);
        $srow = $query->fetch_assoc();

        $logstatus = ($time_in > $srow['time_in']) ? 0 : 1;

        if($srow['time_in'] > $time_in){
            $time_in = $srow['time_in'];
        }

        if($srow['time_out'] < $time_out){
            $time_out = $srow['time_out'];
        }

        $time_in = new DateTime($time_in);
        $time_out = new DateTime($time_out);

        $interval = $time_in->diff($time_out);
        $int = $interval->h + ($interval->i / 60);

        // lunch deduction
        $lunch_start = new DateTime('12:00:00');
        $lunch_end = new DateTime('13:00:00');

        if($time_in < $lunch_start && $time_out > $lunch_end){
            $int -= 1;
        }

        $sql = "UPDATE attendance 
                SET num_hr = '$int', status = '$logstatus' 
                WHERE id = '$id'";
        $conn->query($sql);
    }
    else{
        $_SESSION['error'] = $conn->error;
    }
}
else{
    $_SESSION['error'] = 'Fill up edit form first';
}

header('location:attendance.php');
?>