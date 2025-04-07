<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
$root = "../";
require_once $root . "config.php";

$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in) {
    $user_id = $_SESSION["user_id"];
    $profile_icon =
        $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? $root . "images/default-user.svg";
} else {
    $profile_icon = $root . "images/default-user.svg";
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Performance | Goggler</title>
        <link rel="stylesheet" href="styles/performance.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body class="container-fluid p-0 d-flex flex-column text-white min-vh-100">
        <header class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a href="<?= $root ?>index.php">Home</a></li>
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">Features</a>
                        <div class="dropdown-menu p-0 border-info text-white">
                            <a href="<?= $root ?>image_search.php">Image Search</a>
                            <a href="<?= $root ?>advanced_search.php">Advanced Search</a>
                            <a href="<?= $root ?>web_master.php">Web Master</a>
                        </div>
                    </div>
                </li>
                <?php if (!$user_logged_in): ?>
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="login.php">Login</a></li>
                <?php else: ?>
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">
                            <img
                                id="user_icon"
                                src="<?= $profile_icon ?>"
                                alt="User"
                                width="30"
                                height="30"
                                class="rounded-circle" />
                        </a>
                        <div class="dropdown-menu p-0 bg-transparent border-info text-white">
                            <a href="<?= $root ?>profile.php">Profile</a>
                            <a href="<?= $root ?>logout.php">Logout</a>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </header>
        <main class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
            <div class="dashboard-container">
                <h1>Goggler Dashboard</h1>
                <div class="nav-tabs">
                    <a href="dashboard.php">Overview</a>
                    <a href="performance.php" class="active">Performance</a>
                    <a href="url_inspection.php">URL Inspection</a>
                    <a href="indexing.php">Indexing</a>
                </div>
                <div class="content">
                    <h2>Performance</h2>
                    <form id="performanceForm" class="url-form">
                        <input type="text" id="performanceUrl" placeholder="Enter URL (e.g., https://example.com)" required>
                        <button type="submit">Check Speed</button>
                    </form>
                    <div id="performanceResult" class="result"></div>
                </div>
            </div>
        </main>
        <footer class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a>Privacy</a></li>
                <li><a>Terms</a></li>
                <li>
                    <div class="dropdown">
                        <a data-bs-toggle="dropdown">Settings</a>
                        <div class="dropdown-menu p-0 text-end text-white border-info">
                            <a href="<?= $root ?>history.php">History</a>
                            <a href="<?= $root ?>feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
        <script>
            document.getElementById("performanceForm").addEventListener("submit", async function(event) {
                event.preventDefault();
                const urlInput = document.getElementById("performanceUrl").value;
                const resultDiv = document.getElementById("performanceResult");
                resultDiv.innerHTML = "Fetching...";

                try {
                    const response = await fetch("api/performance_check.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ url: urlInput }),
                    });

                    const data = await response.json();
                    if (response.ok) {
                        resultDiv.innerHTML = `<p>Time taken: ${data.timeTaken} seconds</p>`;
                    } else {
                        resultDiv.innerHTML = `<p>Error: ${data.error || "Something went wrong"}</p>`;
                    }
                } catch (error) {
                    resultDiv.innerHTML = `<p>Failed to fetch: ${error.message}</p>`;
                }
            });
        </script>
    </body>
</html>