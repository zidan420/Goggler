<?php
function callPredictionAPI($url)
{
    $payload = json_encode(["url" => $url]);
    $ch = curl_init("http://localhost:5000/predict");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    /* Get json data from POST request */
    $jsonData = file_get_contents("php://input");

    /* Convert it to associative array that PHP recognizes */
    $url = json_decode($jsonData, true)["message"];
    $result = callPredictionAPI($url);
    echo json_encode($result);
}

?>
