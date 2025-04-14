<!DOCTYPE html>
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
    $url = $_POST["url"] ?? "";
    $result = callPredictionAPI($url);
}
?>

<html lang="en">
<head>
    <title>AI Chatbot URL Checker</title>
</head>
<body>
    <h2>🧠 AI Chatbot - Is this URL Malicious?</h2>
    <form method="POST">
        <input type="text" name="url" placeholder="Enter a URL..." style="width: 400px;" required>
        <button type="submit">Check</button>
    </form>

    <?php if (!empty($result)): ?>
        <div style="margin-top: 20px;">
            <?php if ($result["attack_type"] !== "NONE"): ?>
                <p>⚠️ <strong>This URL is malicious!</strong></p>
                <p>🛡️ <strong>Attack Type:</strong> <?= htmlspecialchars($result["attack_type"]) ?></p>
            <?php else: ?>
                <p>✅ <strong>This URL appears safe.</strong></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</body>
</html>
