<?php
declare(strict_types=1);

function connectBookingDatabase(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli("localhost", "root", "");

    if ($conn->connect_error) {
        throw new RuntimeException("The booking database is unavailable.");
    }

    $conn->set_charset("utf8mb4");
    if (!$conn->query(
        "CREATE DATABASE IF NOT EXISTS user_data CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    ) || !$conn->select_db("user_data")) {
        $conn->close();
        throw new RuntimeException("The booking database could not be prepared.");
    }

    return $conn;
}

function ensureBookingsSchema(mysqli $conn): void
{
    $createTable = <<<SQL
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
) ENGINE=InnoDB
SQL;

    if (!$conn->query($createTable)) {
        throw new RuntimeException("The bookings table could not be prepared.");
    }

    $accountColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'account_id'");
    if (!$accountColumn) {
        throw new RuntimeException("The bookings table could not be inspected.");
    }

    if ($accountColumn->num_rows === 0) {
        if (!$conn->query("ALTER TABLE bookings ADD COLUMN account_id INT UNSIGNED NULL AFTER id")) {
            throw new RuntimeException("The bookings table could not be linked to accounts.");
        }

        $usernameColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'username'");
        if ($usernameColumn && $usernameColumn->num_rows > 0) {
            $conn->query(
                "UPDATE bookings AS b
                 INNER JOIN accounts AS a ON a.username = b.username
                 SET b.account_id = a.id
                 WHERE b.account_id IS NULL"
            );
            $conn->query("ALTER TABLE bookings MODIFY username VARCHAR(50) NULL");
        }
    }
    $accountColumn->close();

    $accountDefinition = $conn->query("SHOW COLUMNS FROM bookings LIKE 'account_id'");
    $accountDetails = $accountDefinition ? $accountDefinition->fetch_assoc() : null;
    if ($accountDefinition) {
        $accountDefinition->close();
    }

    if (($accountDetails["Null"] ?? "YES") === "YES") {
        $nullResult = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE account_id IS NULL");
        if ($nullResult) {
            $nullRow = $nullResult->fetch_assoc();
            if ((int) ($nullRow["total"] ?? 0) === 0) {
                $conn->query("ALTER TABLE bookings MODIFY account_id INT UNSIGNED NOT NULL");
            }
            $nullResult->close();
        }
    }

    $accountIndex = $conn->query("SHOW INDEX FROM bookings WHERE Key_name = 'bookings_account_idx'");
    if ($accountIndex && $accountIndex->num_rows === 0) {
        if (!$conn->query("ALTER TABLE bookings ADD INDEX bookings_account_idx (account_id)")) {
            throw new RuntimeException("The booking account index could not be created.");
        }
    }
    if ($accountIndex) {
        $accountIndex->close();
    }

    $foreignKey = $conn->query(
        "SELECT CONSTRAINT_NAME
         FROM information_schema.REFERENTIAL_CONSTRAINTS
         WHERE CONSTRAINT_SCHEMA = DATABASE()
           AND TABLE_NAME = 'bookings'
           AND REFERENCED_TABLE_NAME = 'accounts'
         LIMIT 1"
    );

    if ($foreignKey && $foreignKey->num_rows === 0) {
        if (!$conn->query(
            "ALTER TABLE bookings
             ADD CONSTRAINT bookings_account_fk
             FOREIGN KEY (account_id) REFERENCES accounts(id)
             ON DELETE CASCADE"
        )) {
            throw new RuntimeException("The booking foreign key could not be created.");
        }
    }
    if ($foreignKey) {
        $foreignKey->close();
    }
}

function currentTitanAccountId(mysqli $conn): ?int
{
    $username = trim((string) ($_SESSION["titanCurrentUser"] ?? ""));
    if ($username === "") {
        return null;
    }

    $lookup = $conn->prepare("SELECT id FROM accounts WHERE username = ? LIMIT 1");
    if (!$lookup) {
        throw new RuntimeException("The signed-in account could not be checked.");
    }

    $lookup->bind_param("s", $username);
    $lookup->execute();
    $account = $lookup->get_result()->fetch_assoc();
    $lookup->close();

    if (!$account) {
        return null;
    }

    $accountId = (int) $account["id"];
    $_SESSION["titanUserId"] = $accountId;
    return $accountId;
}
?>
