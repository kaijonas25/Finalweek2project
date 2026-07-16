<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

session_start();
require_once __DIR__ . "/booking-database.php";

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
    respond(405, ["success" => false, "message" => "Only POST requests are allowed."]);
}

$username = trim((string) ($_SESSION["titanCurrentUser"] ?? ""));
if ($username === "") {
    respond(401, ["success" => false, "message" => "Please log in before booking a session."]);
}

$request = json_decode(file_get_contents("php://input"), true);
if (!is_array($request)) {
    respond(400, ["success" => false, "message" => "Invalid booking request."]);
}

$program = trim((string) ($request["program"] ?? ""));
$trainer = trim((string) ($request["trainer"] ?? ""));
$bookingDate = trim((string) ($request["bookingDate"] ?? ""));
$bookingTime = trim((string) ($request["bookingTime"] ?? ""));
$notes = trim((string) ($request["notes"] ?? ""));

$allowedPrograms = [
    "Weightlifting",
    "Speed Drills",
    "Sport-Specific Conditioning",
];
$allowedTrainers = [
    "Coach Damon Okafor",
    "Coach Goblinstein",
];
$allowedTimes = [
    "06:00", "07:30", "09:00", "10:30", "12:00",
    "13:30", "15:00", "16:30", "18:00",
];

if (!in_array($program, $allowedPrograms, true)) {
    respond(422, ["success" => false, "message" => "Please select a valid program."]);
}
if (!in_array($trainer, $allowedTrainers, true)) {
    respond(422, ["success" => false, "message" => "Please select a valid trainer."]);
}
if (!in_array($bookingTime, $allowedTimes, true)) {
    respond(422, ["success" => false, "message" => "Please select a valid training time."]);
}
if (strlen($notes) > 500) {
    respond(422, ["success" => false, "message" => "Training notes must be 500 characters or fewer."]);
}

$dateObject = DateTimeImmutable::createFromFormat("!Y-m-d", $bookingDate);
$dateErrors = DateTimeImmutable::getLastErrors();
$dateIsValid = $dateObject !== false
    && ($dateErrors === false || ($dateErrors["warning_count"] === 0 && $dateErrors["error_count"] === 0))
    && $dateObject->format("Y-m-d") === $bookingDate;

if (!$dateIsValid) {
    respond(422, ["success" => false, "message" => "Please select a valid booking date."]);
}

$today = new DateTimeImmutable("today");
$latestDate = $today->modify("+90 days");
if ($dateObject < $today || $dateObject > $latestDate) {
    respond(422, ["success" => false, "message" => "Bookings must be within the next 90 days."]);
}

try {
    $conn = connectBookingDatabase();
    ensureBookingsSchema($conn);
    $accountId = currentTitanAccountId($conn);
} catch (RuntimeException $error) {
    respond(500, ["success" => false, "message" => $error->getMessage()]);
}

if ($accountId === null) {
    $conn->close();
    respond(401, ["success" => false, "message" => "Please log in before booking a session."]);
}

$insert = $conn->prepare(
    "INSERT INTO bookings (account_id, username, program, trainer, booking_date, booking_time, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
if (!$insert) {
    respond(500, ["success" => false, "message" => "The booking could not be prepared."]);
}

$insert->bind_param("issssss", $accountId, $username, $program, $trainer, $bookingDate, $bookingTime, $notes);
if (!$insert->execute()) {
    $isDuplicateSlot = $insert->errno === 1062;
    $insert->close();
    $conn->close();

    if ($isDuplicateSlot) {
        respond(409, ["success" => false, "message" => "That trainer is already booked at this time. Please choose another slot."]);
    }

    respond(500, ["success" => false, "message" => "The booking could not be saved."]);
}

$bookingId = $insert->insert_id;
$insert->close();
$conn->close();

$displayDate = $dateObject->format("F j, Y");
$displayTime = DateTimeImmutable::createFromFormat("H:i", $bookingTime)->format("g:i A");
respond(201, [
    "success" => true,
    "bookingId" => $bookingId,
    "message" => "Booked with {$trainer} on {$displayDate} at {$displayTime}.",
]);
?>
