<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'];
    redirect($role === 'lecturer' ? 'views/dashboard_lecturer.php' : 'views/dashboard_student.php');
} else {
    redirect('login.php');
}
?>
