<?php
// includes/auth_session.php
session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function checkAdmin() {
    checkLogin();
    if ($_SESSION['role'] !== 'admin') {
        echo "Access Denied. You do not have permission to view this page.";
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
