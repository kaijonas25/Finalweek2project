<?php
// Backward-compatible entry point. New signup requests are handled by account.php.
$_POST["action"] = "signup";
$_POST["fullName"] = $_POST["fullName"] ?? $_POST["name"] ?? "";
require __DIR__ . "/account.php";
?>
