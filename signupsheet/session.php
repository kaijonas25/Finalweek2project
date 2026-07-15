<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

session_start();

echo json_encode([
    "authenticated" => isset($_SESSION["titanCurrentUser"]),
]);
?>
