<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
$root = "../..";
require_once "$root/config.php";
require_once "$root/mysql_func.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"]) || !$_SESSION["is_web_master"]) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = (int) $_SESSION["user_id"];
$range = isset($_GET["range"]) ? $_GET["range"] : "1m";

/** @var array{labels: string[], clicks: int[], totalClicks: int} $performance_data */
$performance_data = $conn->get_performance_data($user_id, $range);

if (empty($performance_data["labels"])) {
    http_response_code(404);
    echo json_encode(["error" => "No data found"]);
    exit();
}

echo json_encode($performance_data, JSON_NUMERIC_CHECK);
?>
