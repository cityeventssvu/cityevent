<?php
// Define connection parameters locally
$host = 'localhost';
$dbname = 'city_events';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4'; 

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
     $pdo = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {

     die("Database connection failed");
}
?>