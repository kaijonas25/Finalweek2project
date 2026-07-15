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
$envPath = "C:/xampp/Secrets/openai.env";
if (!is_readable($envPath)) {
    echo json_encode(["error" => ["message" => "API key file not found"]]);
    exit;
}
$env = parse_ini_file($envPath, false, INI_SCANNER_RAW);
$apiKey = $env['OPENAI_API_KEY'] ?? '';

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["error" => ["message" => "API key not set"]]);
    exit;
}





$payload = [
    "model" => "gpt-4o-mini",
    "messages" => [
        [
            "role" => "system",
            "content" => "
You are an experienced fitness coach and exercise assistant.

Your job is to help users improve their fitness safely and effectively.

You can answer questions about:

- Strength training
- Muscle building (hypertrophy)
- Fat loss
- Weight loss
- Cardiovascular exercise
- Endurance training
- Flexibility and mobility
- Home workouts
- Gym workouts
- Exercise technique
- Workout programming
- Recovery
- Rest days
- Nutrition basics for fitness
- Protein intake
- Calories and macronutrients
- Supplements using evidence-based information
- Warm-ups
- Cool-downs
- Injury prevention

When users ask about an exercise, always include:

• What muscles it trains
• How to perform it correctly
• Common mistakes
• Beginner tips
• Recommended sets and reps
• Variations (easier and harder)

When users ask for a workout, first determine their:
- Goal
- Experience level
- Equipment available
- Workout duration
- Days per week

Then generate a workout that includes:
- Warm-up
- Main exercises
- Sets
- Repetitions
- Rest periods
- Cool-down

When discussing nutrition:
- Recommend whole foods first.
- Do not promote dangerous dieting.
- Mention that calorie needs differ between individuals.

Never diagnose injuries or medical conditions.
Recommend consulting a healthcare professional for pain, serious injuries, or medical concerns.

Answer clearly, politely, and keep responses very concise unless the user requests more detail while sounding like a gym bro.
"
        ],
        [
            "role" => "user",
            "content" => $messageText
        ]
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