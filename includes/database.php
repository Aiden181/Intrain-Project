<?php
$host = "localhost";
$username = "root";
$password = "root";


// Create connection
$conn = mysqli_connect($host, $username, $password);

// Check connection
if ($conn -> connect_error) {
    die("Connection failed: " . $conn -> connect_error);
}
// else {
//     echo "<p>Connected successfully</p>";
// }


// Make intrain the current database
$db = mysqli_select_db($conn, "intrain");

if (!$db) {
    // If we couldn't, then it either doesn't exist or we can't see it
    $sql = 'CREATE DATABASE `intrain`';

    if (mysqli_query($conn, $sql)) {
        // echo "Database intrain created successfully\n";
    } else {
        echo 'Error creating database: ' . mysqli_error($conn) . "\n";
    }
}

// drop table query for testing
// $conn->query("DROP TABLE IF EXISTS `customer`");
// $conn->query("DROP TABLE IF EXISTS `admin`");

// --------------------------------- //
// ----- create customer table ----- //
// --------------------------------- //
// id : customer id
// last_name : customer last name
// first_name : customer first name
// email : customer email
// phone_number : customer phone number
// username : customer username
// password : customer password
$sql = "CREATE TABLE IF NOT EXISTS `customer` (
    `id` INT(11) NOT NULL,
    `last_name` VARCHAR(64) NOT NULL,
    `first_name` VARCHAR(64) NOT NULL,
    `email` VARCHAR(64) NOT NULL,
    `phone_number` VARCHAR(32) NOT NULL,
    `username` VARCHAR(32) NOT NULL UNIQUE,
    `password` VARCHAR(128) NOT NULL,
    PRIMARY KEY (`id`)
)";
$conn->query($sql);

// --------------------------------- //
// ----- create admin table -------- //
// --------------------------------- //
// id : admin id
// last_name : admin last name
// first_name : admin first name
// email : admin email
// phone_number : admin phone number
// username : admin username
// password : admin password
// flag: admin flag (e.g. z = root power, a = create, b = read, c = update, etc...)
$sql = "CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT(11) NOT NULL,
    `last_name` VARCHAR(64) NOT NULL,
    `first_name` VARCHAR(64) NOT NULL,
    `email` VARCHAR(64) NOT NULL,
    `phone_number` VARCHAR(32) NOT NULL,
    `username` VARCHAR(32) NOT NULL UNIQUE,
    `password` VARCHAR(128) NOT NULL,
    `flag` VARCHAR(16) NOT NULL,
    PRIMARY KEY (`id`)
)";
$conn->query($sql);


// automatically add root admin
$sql = "INSERT INTO `admin`(`id`, `last_name`, `first_name`, `email`, `phone_number`, `username`, `password`, `flag`) VALUES (1, '', '', '', '','root', PASSWORD('1ntrainr00t!'), 'z')";
$conn->query($sql);

// add a user for debug
$sql = "INSERT INTO `customer`(`id`, `last_name`, `first_name`, `email`, `phone_number`, `username`, `password`) VALUES (1, 'john', 'doe', 'john.doe@ahamail.com', '0901234567','user1', PASSWORD('12345'))";
$conn->query($sql);

$sql = "ALTER TABLE `customer` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;";
$conn->query($sql);

$sql = "ALTER TABLE `admin` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;";
$conn->query($sql);

?>
