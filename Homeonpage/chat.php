<?php
// Set headers to allow your frontend to communicate with this script
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

// Exit early if it's just a browser preflight check
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the user message from the frontend fetch request
$input = json_decode(file_get_contents('php://input'), true);
$messageText = $input['message'] ?? '';

if (empty($messageText)) {
    echo json_encode(["error" => ["message" => "Empty message received"]]);
    exit;
}

// Keep your API key secure on the server side
$apiKey = "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"; 

$payload = [
    "model" => "gpt-4o-mini",
    "messages" => [
        [
            "role" => "system", 
            "content" => "You are a helpful AI assistant for a website. Answer clearly and politely. Give short and concise answers."
        ],
        ["role" => "user", "content" => $messageText]
    ]
];

// Send the request securely from server to server via cURL
$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $apiKey
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

// Relay OpenAI's response back to your frontend
echo $response;
?>