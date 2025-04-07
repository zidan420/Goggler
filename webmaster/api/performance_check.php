<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["url"])) {
    echo json_encode(["error" => "No URL provided."]);
    exit;
}

$url = filter_var($data["url"], FILTER_VALIDATE_URL);
if (!$url) {
    echo json_encode(["error" => "Invalid URL."]);
    exit;
}

// Start time tracking
$startTime = microtime(true);

// Fetch the file
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0"); 
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// End time tracking
$endTime = microtime(true);
$timeTaken = round($endTime - $startTime, 3); // Round to milliseconds

if ($httpCode >= 200 && $httpCode < 400) {
    echo json_encode(["timeTaken" => $timeTaken]);
} else {
    echo json_encode(["error" => "Failed to fetch URL. HTTP Code: $httpCode"]);
}
?>
