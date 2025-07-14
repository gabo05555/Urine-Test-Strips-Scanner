<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();
session_destroy();

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    // AJAX/fetch request
    echo json_encode(['success' => true]);
    exit;
} else {
    // Direct access, redirect to login page
    header('Location: login.php');
    exit;
}
?>