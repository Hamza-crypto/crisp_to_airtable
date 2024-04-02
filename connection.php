<?php

$host = '127.0.0.1'; // Your database host (e.g., localhost)
$dbname = 'airtable_crisp'; // Your database name
$username = 'root'; // Your database username
$password = ""; // Your database password


// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
