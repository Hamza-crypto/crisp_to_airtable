<?php

var_dump(phpinfo());

$host = 'localhost'; // Your database host (e.g., localhost)
$dbname = 'dbx7aq2ztedci4'; // Your database name
$username = 'ugnqlsthbqwbq'; // Your database username
$password = "3c~3@21&)mf5"; // Your database password

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully";

// SQL query to insert data
$sql = "INSERT INTO `cursors` (`id`, `count`, `created_at`, `updated_at`)
        VALUES (NULL, '3', '2021-09-14 00:00:00', '2022-09-25 03:44:53')";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();

?>