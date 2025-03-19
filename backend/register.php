<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $course_year = trim($_POST['course_year']);
    $email = trim($_POST['email']);
    $password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: ../frontend/register.html");
        exit();
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already registered!";
        header("Location: ../frontend/register.html");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO users (name, course_year_section, email, password) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$fullname, $course_year, $email, $hashed_password])) {
        $_SESSION['success'] = "Registration successful! Please log in.";
        header("Location: ../frontend/index.html");
    } else {
        $_SESSION['error'] = "Registration failed! Please try again.";
        header("Location: ../frontend/register.html");
    }
    exit();
}
?>
