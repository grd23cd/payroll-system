<?php
include 'includes/session.php';

if(isset($_POST['add'])){

    $employee_id    = $_POST['employee_id'];
    $biometrics_id  = $_POST['biometrics_id'];
    $firstname      = $_POST['firstname'];
    $middlename     = $_POST['middlename'];
    $lastname       = $_POST['lastname'];
    $suffix         = $_POST['suffix'];
    $gender         = $_POST['gender'];
    $marital_status = $_POST['marital_status'];
    $religion       = $_POST['religion'];

    // form sends 'home_address' → DB column is 'address'
    $address        = $_POST['home_address'];
    // form sends 'contact' → DB column is 'contact_info'
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

    // ── DATE CONVERSION: MM/DD/YYYY → YYYY-MM-DD ──────────────────
    function convertDate($d) {
        if (empty(trim($d))) return null;
        $dt = DateTime::createFromFormat('m/d/Y', trim($d));
        return $dt ? $dt->format('Y-m-d') : null;
    }

    $birthdate       = convertDate($_POST['birthdate']);
    $hired_date      = convertDate($_POST['hired_date']);
    $separation_date = convertDate($_POST['separation_date'] ?? '');

    // Use NULL in SQL if date is empty, otherwise quote it
    $birthdate_sql       = $birthdate       ? "'$birthdate'"       : "NULL";
    $hired_date_sql      = $hired_date      ? "'$hired_date'"      : "NULL";
    $separation_date_sql = $separation_date ? "'$separation_date'" : "NULL";
    // ──────────────────────────────────────────────────────────────

    // Photo upload
    $filename = $_FILES['photo']['name'];
    if(!empty($filename)){
        $ext      = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = $employee_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
    }

    // CHECK DUPLICATE EMPLOYEE ID
    $check = "SELECT * FROM employees WHERE employee_id = '$employee_id'";
    $result = $conn->query($check);

    if($result->num_rows > 0){
        $_SESSION['error'] = 'Employee ID already exists';
    }
    else{

        $sql = "INSERT INTO employees (
            employee_id,
            biometrics_id,
            firstname,
            middlename,
            lastname,
            suffix,
            birthdate,
            gender,
            marital_status,
            religion,
            address,
            contact_info,
            email_address,
            highest_educational_attainment,
            course_study,
            emergency_contact_name,
            emergency_contact_relationship,
            emergency_contact_number,
            position_id,
            designation,
            schedule_id,
            hired_date,
            salary,
            sss_number,
            pagibig_number,
            tin_number,
            philhealth_number,
            employment_status,
            status,
            separation_date,
            reason_for_resignation,
            photo,
            created_on
        ) VALUES (
            '$employee_id',
            '$biometrics_id',
            '$firstname',
            '$middlename',
            '$lastname',
            '$suffix',
            $birthdate_sql,
            '$gender',
            '$marital_status',
            '$religion',
            '$address',
            '$contact_info',
            '$email_address',
            '$highest_educational_attainment',
            '$course_study',
            '$emergency_contact_name',
            '$emergency_contact_relationship',
            '$emergency_contact_number',
            '$position',
            '$designation',
            '$schedule',
            $hired_date_sql,
            '$salary',
            '$sss_number',
            '$pagibig_number',
            '$tin_number',
            '$philhealth_number',
            '$employment_status',
            '$status',
            $separation_date_sql,
            '$reason_for_resignation',
            '$filename',
            NOW()
        )";

        if($conn->query($sql)){
            $_SESSION['success'] = 'Employee added successfully';
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    }

}
else{
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: employee.php');
?>