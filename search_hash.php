<?php
require_once "config.php";
require_once "mysql_func.php";

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Check if the file was uploaded
if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["status" => "error", "message" => "No image file received"]);
    exit();
}
// Read file contents to generate hash
$imageData = file_get_contents($_FILES["image"]["tmp_name"]);
$hash = md5($imageData);
$results = $conn->query_hash($hash);

// Check if results exist
if ($results->num_rows) {
    echo json_encode(["status" => "success", "data" => $results->fetch_assoc()]);
} else {
    echo json_encode(["status" => "not_found"]);
}

?>
