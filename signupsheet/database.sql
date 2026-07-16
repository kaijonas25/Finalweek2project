CREATE DATABASE IF NOT EXISTS user_data
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE user_data;

CREATE TABLE IF NOT EXISTS accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  age TINYINT UNSIGNED NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(30) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  account_id INT UNSIGNED NOT NULL,
  username VARCHAR(50) NULL,
  program VARCHAR(80) NOT NULL,
  trainer VARCHAR(100) NOT NULL,
  booking_date DATE NOT NULL,
  booking_time TIME NOT NULL,
  notes VARCHAR(500) NOT NULL DEFAULT '',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX bookings_account_idx (account_id),
  UNIQUE KEY unique_trainer_slot (trainer, booking_date, booking_time),
  CONSTRAINT bookings_account_fk
    FOREIGN KEY (account_id) REFERENCES accounts(id)
    ON DELETE CASCADE
) ENGINE=InnoDB;
