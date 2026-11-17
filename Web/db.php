<?php
// db.php

// Database credentials come from environment variables (Railway injects these).
// Local XAMPP defaults are kept as fallbacks so development still works.
$hostname = getenv('MYSQLHOST') ?: '127.0.0.1';
$username = getenv('MYSQLUSER') ?: 'root';
$pwd      = getenv('MYSQLPASSWORD') ?: '';
$db       = getenv('MYSQLDATABASE') ?: 'teambox';
$port     = (int) (getenv('MYSQLPORT') ?: 3306);

$conn = mysqli_connect($hostname, $username, $pwd, $db, $port)
or die("Database connection failed: " . mysqli_connect_error());

mysqli_set_charset($conn, "utf8mb4");
?>
