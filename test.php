<?php
require_once 'includes/db.php';

// Simulate User Registration
$_POST['email'] = 'testuser@example.com';
$_POST['password'] = 'password123';
$_POST['first_name'] = 'Test';
$_POST['last_name'] = 'User';
$_SERVER['REQUEST_METHOD'] = 'POST';
require 'register.php';
?>
