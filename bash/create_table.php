<?php
$servername = $argv[2];
$username = $argv[3];
$password =$argv[4];
$dbname = $argv[5];

$tableName = $argv[1]; // First argument passed from the bash script

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// SQL to create table
$sql = "CREATE TABLE $tableName (
       id int(11) unsigned NOT NULL AUTO_INCREMENT,
       name varchar(256) DEFAULT NULL,
       slug varchar(256) DEFAULT NULL,
       seo_title varchar(256) DEFAULT NULL,
       seo_description text,
       seo_keywords text,
       enabled tinyint(1) NOT NULL DEFAULT '1',
       removed tinyint(1) NOT NULL DEFAULT '0',
       created_at datetime NULL DEFAULT NULL,
       updated_at datetime NULL DEFAULT NULL,
       deleted_at datetime NULL DEFAULT NULL,
       PRIMARY KEY (id)
     )";

if ($conn->query($sql) === TRUE) {
	echo "Table $tableName created successfully";
} else {
	echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
