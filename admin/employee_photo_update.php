<?php
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_photo'])) {

    $id = intval($_POST['id']);

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mime    = mime_content_type($_FILES['photo']['tmp_name']);

        if (!in_array($mime, $allowed)) {
            die("Invalid file type.");
        }

        $filename  = basename($_FILES['photo']['name']); // keeps original name e.g. sb.jpg
        $uploadDir = '../images/';

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $sql  = "UPDATE employees SET photo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $filename, $id);
            $stmt->execute();
        }
    }

    header('Location: employee.php');
    exit;
}
?>