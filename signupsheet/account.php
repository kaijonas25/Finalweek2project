<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

// Allow this local XAMPP API to be called from localhost or VS Code Live Server.
$requestOrigin = $_SERVER["HTTP_ORIGIN"] ?? "";
$isLocalOrigin = $requestOrigin === "null"
    || preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $requestOrigin) === 1;

if ($isLocalOrigin) {
    header("Access-Control-Allow-Origin: " . $requestOrigin);
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Vary: Origin");
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "OPTIONS") {
    http_response_code($isLocalOrigin ? 204 : 403);
    exit;
}

session_start();

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    respond(500, ["success" => false, "message" => "The account database is unavailable."]);
}
$conn->set_charset("utf8mb4");

if (!$conn->query(
    "CREATE DATABASE IF NOT EXISTS user_data CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
) || !$conn->select_db("user_data")) {
    respond(500, ["success" => false, "message" => "The account database could not be prepared."]);
}

$createTable = <<<SQL
CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    age TINYINT UNSIGNED NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)
SQL;

if (!$conn->query($createTable)) {
    respond(500, ["success" => false, "message" => "The accounts table could not be prepared."]);
}

$request = json_decode(file_get_contents("php://input"), true);
if (!is_array($request)) {
    $request = $_POST;
}

$action = (string) ($request["action"] ?? "");
$username = trim((string) ($request["username"] ?? ""));
$password = (string) ($request["password"] ?? "");

if ($username === "" || $password === "") {
    respond(400, ["success" => false, "message" => "Please enter both username and password."]);
}

if ($action === "signup") {
    $fullName = trim((string) ($request["fullName"] ?? ""));
    $email = trim((string) ($request["email"] ?? ""));
    $phone = trim((string) ($request["phone"] ?? ""));
    $age = filter_var($request["age"] ?? null, FILTER_VALIDATE_INT);

    if ($fullName === "" || $email === "" || $phone === "" || $age === false) {
        respond(400, ["success" => false, "message" => "Please fill out every signup field."]);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(400, ["success" => false, "message" => "Please enter a valid email address."]);
    }
    if ($age < 13 || $age > 120) {
        respond(400, ["success" => false, "message" => "Age must be between 13 and 120."]);
    }
    if (strlen($password) < 8) {
        respond(400, ["success" => false, "message" => "Password must be at least 8 characters."]);
    }

    $check = $conn->prepare(
        "SELECT username, email FROM accounts WHERE username = ? OR email = ? LIMIT 1"
    );
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing) {
        $message = strcasecmp($existing["username"], $username) === 0
            ? "That username is already taken."
            : "That email address already has an account.";
        respond(409, ["success" => false, "message" => $message]);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $conn->prepare(
        "INSERT INTO accounts (full_name, username, age, email, phone, password_hash)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $insert->bind_param("ssisss", $fullName, $username, $age, $email, $phone, $passwordHash);

    if (!$insert->execute()) {
        respond(500, ["success" => false, "message" => "The account could not be created."]);
    }

    session_regenerate_id(true);
    $_SESSION["titanCurrentUser"] = $username;
    $_SESSION["titanUserId"] = $insert->insert_id;
    $insert->close();
    $conn->close();
    respond(201, ["success" => true, "username" => $username]);
}

if ($action === "login") {
    $lookup = $conn->prepare(
        "SELECT id, username, password_hash FROM accounts WHERE username = ? LIMIT 1"
    );
    $lookup->bind_param("s", $username);
    $lookup->execute();
    $account = $lookup->get_result()->fetch_assoc();
    $lookup->close();

    if (!$account || !password_verify($password, $account["password_hash"])) {
        respond(401, ["success" => false, "message" => "Invalid username or password."]);
    }

    session_regenerate_id(true);
    $_SESSION["titanCurrentUser"] = $account["username"];
    $_SESSION["titanUserId"] = $account["id"];
    $conn->close();
    respond(200, ["success" => true, "username" => $account["username"]]);
}

$conn->close();
respond(400, ["success" => false, "message" => "Invalid account action."]);
?>
