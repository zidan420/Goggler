<?php
require_once "config.php";
ini_set("display_errors", 1);
error_reporting(E_ALL);

$now = new DateTime();
print_r($now);

/*
$query = $_GET["q"] ?? "";
if ($query) {
    [$results, $count] = $conn->search($query, 0, 10);
    foreach ($results as $result) {
        print_r($result);
        echo "<br>";
    }
    echo "<br><br>";
    echo $count;
    echo "<br><br><br>";
    $results = $conn->query($query, 0, 10);
    while ($row = $results->fetch_assoc()) {
        print_r($row);
        echo "<br>";
    }
    //foreach ($results as $result) {
    //    echo "<h2>{$result["title"]}</h2>";
    //    echo "<p>{$result["description"]}</p>";
    //    echo "<a href='{$result["url"]}'>{$result["url"]}</a><br><br>";
    //}
}*/

?>
