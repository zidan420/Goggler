<?php
require_once "config.php";
require_once "mysql_func.php";

$url_id = isset($_GET["url_id"]) ? (int) $_GET["url_id"] : 0;
$user_id = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : 0;
$target_url = isset($_GET["url"]) ? urldecode($_GET["url"]) : "index.php";

if ($url_id && $user_id) {
    $conn->log_click($url_id, $user_id);
}

header("Location: $target_url");
exit();
?>
