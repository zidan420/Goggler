<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "zidan";
$password = "n0ts034sy";
$dbname = "goggler";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Read request data
$data = json_decode(file_get_contents("php://input"), true);
$url = isset($data["url"]) ? trim($data["url"]) : '';

if (empty($url)) {
    echo json_encode(["error" => "No URL provided"]);
    exit;
}

// Prepare statement to check if URL exists
$stmt = $conn->prepare("SELECT id FROM urlInfo WHERE url = ?");
$stmt->bind_param("s", $url);
$stmt->execute();
$stmt->store_result();

$response = ["url" => $url, "inDatabase" => $stmt->num_rows > 0];

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode($response);
?>
