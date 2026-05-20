<?php
include 'includes/session.php';

if(isset($_POST['add'])){
    $employee = $_POST['employee'];
    $date = $_POST['date'];

    // KEEP RAW TIME FIRST
    $raw_time_in = $_POST['time_in'];
    $raw_time_out = $_POST['time_out'];

    $time_in = date('H:i:s', strtotime($raw_time_in));
    $time_out = date('H:i:s', strtotime($raw_time_out));

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

            // ===============================
            // GRACE PERIOD LOGIC
            // ===============================
            $sched_time_in = new DateTime($date . ' ' . $scherow['time_in']);
            $actual_time_in = new DateTime($date . ' ' . $time_in);

            $late_minutes = ($actual_time_in->getTimestamp() - $sched_time_in->getTimestamp()) / 60;

            $logstatus = ($late_minutes <= 15) ? 1 : 0;

            $sql = "INSERT INTO attendance 
                    (employee_id, date, time_in, time_out, status) 
                    VALUES ('$emp', '$date', '$time_in', '$time_out', '$logstatus')";

            if($conn->query($sql)){
                $_SESSION['success'] = 'Attendance added successfully';
                $id = $conn->insert_id;

                // GET SCHEDULE (DO NOT TOUCH TIME-IN)
                $sql = "SELECT * FROM employees 
                        LEFT JOIN schedules ON schedules.id=employees.schedule_id 
                        WHERE employees.id = '$emp'";
                $query = $conn->query($sql);
                $srow = $query->fetch_assoc();

                // ONLY USE SCHEDULE FOR COMPARISON, NOT OVERWRITE
                $sched_in = $srow['time_in'];
                $sched_out = $srow['time_out'];

                // COMPUTE HOURS USING ACTUAL INPUT
                $time_in_obj = new DateTime($date . ' ' . $time_in);
                $time_out_obj = new DateTime($date . ' ' . $time_out);

                $interval = $time_in_obj->diff($time_out_obj);
                $int = $interval->h + ($interval->i / 60);

                // lunch deduction
                $lunch_start = new DateTime($date . ' 12:00:00');
                $lunch_end = new DateTime($date . ' 13:00:00');

                if($time_in_obj < $lunch_start && $time_out_obj > $lunch_end){
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