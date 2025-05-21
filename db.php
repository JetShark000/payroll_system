<?php
$host = 'localhost';
$port = '3306';
$db = 'payroll_db';
$user = 'root';
$pass = '';
// db.php - database connection file
$conn = new mysqli("localhost", "root", "", "payroll_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>