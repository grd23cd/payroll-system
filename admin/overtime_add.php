<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){

		$employee = $_POST['employee'];
		$date = $_POST['date'];

		// Convert to numeric values safely
		$hrs = (float) $_POST['hours'];
		$mins = (float) $_POST['mins'];

		// Total overtime hours
		$hours = $hrs + ($mins / 60);

		$rate = $_POST['rate'];

		$sql = "SELECT * FROM employees WHERE employee_id = '$employee'";
		$query = $conn->query($sql);

		if($query->num_rows < 1){
			$_SESSION['error'] = 'Employee not found';
		}
		else{
			$row = $query->fetch_assoc();
			$employee_id = $row['id'];

			$sql = "INSERT INTO overtime (employee_id, date_overtime, hours, rate) 
					VALUES ('$employee_id', '$date', '$hours', '$rate')";

			if($conn->query($sql)){
				$_SESSION['success'] = 'Overtime added successfully';
			}
			else{
				$_SESSION['error'] = $conn->error;
			}
		}
	}	
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: overtime.php');
?>