<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_data";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("connection failed: ".$conn->connect_error)
}

$name = $_POST['name']??";
$age = $_POST['age']??";
$email = $_POST['email']??";
$phone = $_POST['phone']??";

if(empty($name) || empty($age) || empty($email) || empty($phone)){
    echo "Please fill out all fields.";
    exit;
}
$sql = "INSERT INTO users (name, age, email, phone) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siss", $name, $age, $email, $phone);
if ($stmt->execute()) {
    echo "data saved succesfully!!!";

} else {
    echo "error: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>