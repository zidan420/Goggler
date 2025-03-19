<?php
header("Content-Type: application/json");

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = "localhost";
$username = "zidan";
$password = "n0ts034sy";
$dbname = "goggler";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["url"])) {
    echo json_encode(["error" => "No URL provided."]);
    exit;
}

$sitemapUrl = filter_var($data["url"], FILTER_VALIDATE_URL);
if (!$sitemapUrl) {
    echo json_encode(["error" => "Invalid URL."]);
    exit;
}

// Fetch sitemap
$sitemapXml = @file_get_contents($sitemapUrl);
if ($sitemapXml === false) {
    echo json_encode(["error" => "Failed to fetch sitemap."]);
    exit;
}

// Check if the response is actually XML
libxml_use_internal_errors(true);
$xml = simplexml_load_string($sitemapXml);
if ($xml === false) {
    echo json_encode(["error" => "Invalid XML format."]);
    exit;
}

$inserted = 0;
foreach ($xml->url as $urlElement) {
    $url = $conn->real_escape_string((string) $urlElement->loc);
    $sql = "INSERT IGNORE INTO urlInfo (url) VALUES ('$url') ON DUPLICATE KEY UPDATE url=url";
    
    if ($conn->query($sql) === TRUE) {
        $inserted++;
    }
}

echo json_encode(["message" => "Sitemap processed. $inserted URLs added."]);

$conn->close();
?>
