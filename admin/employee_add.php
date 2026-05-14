<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){

		$employee_id = $_POST['employee_id'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$address = $_POST['address'];
		$birthdate = $_POST['birthdate'];
		$contact = $_POST['contact'];
		$gender = $_POST['gender'];
		$position = $_POST['position'];
		$schedule = $_POST['schedule'];

		$filename = $_FILES['photo']['name'];

		if(!empty($filename)){
			move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);
		}

		// CHECK DUPLICATE EMPLOYEE ID
		$check = "SELECT * FROM employees WHERE employee_id = '$employee_id'";
		$result = $conn->query($check);

		if($result->num_rows > 0){
			$_SESSION['error'] = 'Employee ID already exists';
		}
		else{

			$sql = "INSERT INTO employees 
				(employee_id, firstname, lastname, address, birthdate, contact_info, gender, position_id, schedule_id, photo, created_on) 
				VALUES 
				('$employee_id', '$firstname', '$lastname', '$address', '$birthdate', '$contact', '$gender', '$position', '$schedule', '$filename', NOW())";

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