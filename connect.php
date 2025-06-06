<?php
// Database configuration
$host = "localhost";      
$port = "5432";           
$dbname = 'FINAL-DB';
$user = "postgres";       // <--- fix this
$password = "12345";

// Connection string
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
} 
?>