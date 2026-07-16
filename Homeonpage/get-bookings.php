<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

session_start();
require_once __DIR__ . "/booking-database.php";

function respondWithBookings(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "GET") {
    respondWithBookings(405, ["success" => false, "message" => "Only GET requests are allowed."]);
}

if (!isset($_SESSION["titanCurrentUser"])) {
    respondWithBookings(401, ["success" => false, "message" => "Please log in to view bookings."]);
}

try {
    $conn = connectBookingDatabase();
    ensureBookingsSchema($conn);
    $accountId = currentTitanAccountId($conn);
} catch (RuntimeException $error) {
    respondWithBookings(500, ["success" => false, "message" => $error->getMessage()]);
}

if ($accountId === null) {
    $conn->close();
    respondWithBookings(401, ["success" => false, "message" => "The signed-in account no longer exists."]);
}

$query = $conn->prepare(
    "SELECT
        b.id,
        b.program,
        b.trainer,
        DATE_FORMAT(b.booking_date, '%Y-%m-%d') AS booking_date,
        TIME_FORMAT(b.booking_time, '%H:%i') AS booking_time,
        b.notes
     FROM bookings AS b
     INNER JOIN accounts AS a ON a.id = b.account_id
     WHERE a.id = ?
     ORDER BY b.booking_date, b.booking_time"
);

if (!$query) {
    $conn->close();
    respondWithBookings(500, ["success" => false, "message" => "Bookings could not be retrieved."]);
}

$query->bind_param("i", $accountId);
$query->execute();
$result = $query->get_result();
$bookings = [];

while ($booking = $result->fetch_assoc()) {
    $bookings[] = $booking;
}

$query->close();
$conn->close();

respondWithBookings(200, ["success" => true, "bookings" => $bookings]);
?>
