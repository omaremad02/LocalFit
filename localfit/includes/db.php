<?php
require_once 'config.php';

// Database connection
function getDbConnection() {
    $host = 'localhost';
    $dbname = 'localfit';
    $username = 'root';
    $password = '';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit();
    }
}

// Get connection
$conn = getDbConnection();
?>