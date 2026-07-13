CREATE DATABASE user_data;
USE user_data;
CREATE TABLE user(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    age INT
    email VARCHAR(100),
    phone VARCHAR(20),
    password VARCHAR(100)
)