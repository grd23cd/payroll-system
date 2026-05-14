<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		$id = $_POST['id'];
		$date = $_POST['date'];

		$hrs = (float) $_POST['hours'];
		$mins = (float) $_POST['mins'];
		$hours = $hrs + ($mins / 60);

		$rate = (float) $_POST['rate'];

		$sql = "UPDATE overtime 
				SET hours = '$hours', 
					rate = '$rate', 
					date_overtime = '$date' 
				WHERE id = '$id'";

		if($conn->query($sql)){
			$_SESSION['success'] = 'Overtime updated successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Fill up edit form first';
	}

	header('location:overtime.php');
?>