<?php
include 'includes/session.php';

if(isset($_POST['add'])){
    $employee = $_POST['employee'];
    $date = $_POST['date'];

    $time_in = date('H:i:s', strtotime($_POST['time_in']));
    $time_out = date('H:i:s', strtotime($_POST['time_out']));

    $sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
    $query = $conn->query($sql);

    if($query->num_rows < 1){
        $_SESSION['error'] = 'Employee not found';
    }
    else{
        $row = $query->fetch_assoc();
        $emp = $row['id'];

        $sql = "SELECT * FROM attendance 
                WHERE employee_id = '$emp' AND date = '$date'";
        $query = $conn->query($sql);

        if($query->num_rows > 0){
            $_SESSION['error'] = 'Employee attendance for the day exist';
        }
        else{

            $sched = $row['schedule_id'];
            $sql = "SELECT * FROM schedules WHERE id = '$sched'";
            $squery = $conn->query($sql);
            $scherow = $squery->fetch_assoc();

            $logstatus = ($time_in > $scherow['time_in']) ? 0 : 1;

            $sql = "INSERT INTO attendance 
                    (employee_id, date, time_in, time_out, status) 
                    VALUES ('$emp', '$date', '$time_in', '$time_out', '$logstatus')";

            if($conn->query($sql)){
                $_SESSION['success'] = 'Attendance added successfully';
                $id = $conn->insert_id;

                $sql = "SELECT * FROM employees 
                        LEFT JOIN schedules ON schedules.id=employees.schedule_id 
                        WHERE employees.id = '$emp'";
                $query = $conn->query($sql);
                $srow = $query->fetch_assoc();

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

                $sql = "UPDATE attendance SET num_hr = '$int' WHERE id = '$id'";
                $conn->query($sql);
            }
            else{
                $_SESSION['error'] = $conn->error;
            }
        }
    }
}
else{
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: attendance.php');
?>