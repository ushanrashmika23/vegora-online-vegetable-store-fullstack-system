<?php
// controllers/authController.php

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($pdo);

// Determine action (register, login, logout)
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ----- REGISTRATION -----
    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['auth_error'] = 'All fields are required.';
            header('Location: ../register.php');
            exit;
        }

        // Email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_error'] = 'Invalid email format.';
            header('Location: ../register.php');
            exit;
        }

        // Password matching
        if ($password !== $confirm) {
            $_SESSION['auth_error'] = 'Passwords do not match.';
            header('Location: ../register.php');
            exit;
        }

        // Prevent duplicate email
        if ($userModel->findByEmail($email)) {
            $_SESSION['auth_error'] = 'Email is already registered. Please login.';
            header('Location: ../register.php');
            exit;
        }

        // Create the user
        if ($userModel->create($name, $email, $password)) {
            $_SESSION['auth_success'] = 'Account created successfully! You can now login.';
            header('Location: ../login.php');
            exit;
        } else {
            $_SESSION['auth_error'] = 'An error occurred during registration. Please try again.';
            header('Location: ../register.php');
            exit;
        }
    } 
    
    // ----- LOGIN -----
    elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['auth_error'] = 'Please fill in both email and password.';
            header('Location: ../login.php');
            exit;
        }

        // Find user
        $user = $userModel->findByEmail($email);

        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Success: Create session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role']; // Store RBAC role
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            // Failed login
            $_SESSION['auth_error'] = 'Incorrect email or password.';
            header('Location: ../login.php');
            exit;
        }
    }

} 
// ----- LOGOUT -----
elseif ($action === 'logout') {
    // Unset all session variables and destroy session
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
} else {
    // Invalid action or direct access
    header('Location: ../index.php');
    exit;
}
?>
