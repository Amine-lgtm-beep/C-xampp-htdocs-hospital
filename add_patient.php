<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['name'], $_POST['age'], $_POST['gender'], $_POST['phone'], $_POST['note'])
    ) {
        $name   = trim($_POST['name']);
        $age    = (int)$_POST['age'];
        $gender = trim($_POST['gender']);
        $phone  = trim($_POST['phone']);
        $note   = trim($_POST['note']);

        // تحقق من الحقول الأساسية
        if (!empty($name) && $age > 0 && !empty($gender)) {
            $sql = "INSERT INTO patients (name, age, gender, phone, note) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sisss", $name, $age, $gender, $phone, $note);
                $stmt->execute();
                header("Location: index.php?status=success");
                exit;
            } else {
                header("Location: index.php?status=error");
                exit;
            }
        } else {
            header("Location: index.php?status=missing");
            exit;
        }
    } else {
        header("Location: index.php?status=incomplete");
        exit;
    }
} else {
    // منع الوصول المباشر
    header("Location: index.php");
    exit;
}
?>
