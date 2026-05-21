<?php
include 'includes/session.php';

if(isset($_POST['edit'])){

    $empid          = $_POST['id'];
    $employee_id    = $_POST['employee_id'];
    $biometrics_id  = $_POST['biometrics_id'];
    $firstname      = $_POST['firstname'];
    $middlename     = $_POST['middlename'];
    $lastname       = $_POST['lastname'];
    $suffix         = $_POST['suffix'];
    $gender         = $_POST['gender'];
    $marital_status = $_POST['marital_status'];
    $religion       = $_POST['religion'];

    // Contact
    $address        = $_POST['home_address'];
    $contact_info   = $_POST['contact'];
    $email_address  = $_POST['email_address'];

    // Education
    $highest_educational_attainment = $_POST['highest_educational_attainment'];
    $course_study   = $_POST['course_study'];

    // Emergency
    $emergency_contact_name         = $_POST['emergency_contact_name'];
    $emergency_contact_relationship = $_POST['emergency_contact_relationship'];
    $emergency_contact_number       = $_POST['emergency_contact_number'];

    // Job
    $position       = $_POST['position'];
    $designation    = $_POST['designation'];
    $schedule       = $_POST['schedule'];
    $salary         = $_POST['salary'];

    // Government IDs
    $sss_number         = $_POST['sss_number'];
    $pagibig_number     = $_POST['pagibig_number'];
    $tin_number         = $_POST['tin_number'];
    $philhealth_number  = $_POST['philhealth_number'];

    // Employment status / separation
    $employment_status      = $_POST['employment_status'];
    $status                 = $_POST['status'] ?? '';
    $reason_for_resignation = $_POST['reason_for_resignation'] ?? '';

    // ── DATE CONVERSION ───────────────────────────────
    function convertDate($d){

        if(empty($d)){
            return NULL;
        }

        $d = trim($d);

        return date('Y-m-d', strtotime($d));
    }

    $birthdate       = convertDate($_POST['birthdate'] ?? '');
    $hired_date      = convertDate($_POST['hired_date'] ?? '');
    $separation_date = convertDate($_POST['separation_date'] ?? '');

    $birthdate_sql       = $birthdate ? "'$birthdate'" : "NULL";
    $hired_date_sql      = $hired_date ? "'$hired_date'" : "NULL";
    $separation_date_sql = $separation_date ? "'$separation_date'" : "NULL";
    // ─────────────────────────────────────────────────

    // CHECK DUPLICATE EMPLOYEE ID
    $check = "SELECT * FROM employees 
              WHERE employee_id = '$employee_id' 
              AND id != '$empid'";
    $result = $conn->query($check);

    if($result->num_rows > 0){
        $_SESSION['error'] = 'Employee ID already exists';
    }
    else{

        $sql = "UPDATE employees SET 
            employee_id                     = '$employee_id',
            biometrics_id                   = '$biometrics_id',
            firstname                       = '$firstname',
            middlename                      = '$middlename',
            lastname                        = '$lastname',
            suffix                          = '$suffix',
            birthdate                       = $birthdate_sql,
            gender                          = '$gender',
            marital_status                  = '$marital_status',
            religion                        = '$religion',
            address                         = '$address',
            contact_info                    = '$contact_info',
            email_address                   = '$email_address',
            highest_educational_attainment  = '$highest_educational_attainment',
            course_study                    = '$course_study',
            emergency_contact_name          = '$emergency_contact_name',
            emergency_contact_relationship  = '$emergency_contact_relationship',
            emergency_contact_number        = '$emergency_contact_number',
            position_id                     = '$position',
            designation                     = '$designation',
            schedule_id                     = '$schedule',
            hired_date                      = $hired_date_sql,
            salary                          = '$salary',
            sss_number                      = '$sss_number',
            pagibig_number                  = '$pagibig_number',
            tin_number                      = '$tin_number',
            philhealth_number               = '$philhealth_number',
            employment_status               = '$employment_status',
            status                          = '$status',
            separation_date                 = $separation_date_sql,
            reason_for_resignation          = '$reason_for_resignation'
            WHERE id = '$empid'";

        if($conn->query($sql)){
            $_SESSION['success'] = 'Employee updated successfully';
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    }

}
else{
    $_SESSION['error'] = 'Select employee to edit first';
}

header('location: employee.php');
?>