<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
$root = "..";
require_once "$root/config.php";

/* User must be logged in AND user must be a web master */
$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in && $_SESSION["is_web_master"]) {
    $user_id = $_SESSION["user_id"];
    $profile_icon =
        "$root/" . ($conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg");
    $performance_data = $conn->get_performance_data($user_id, "1m");
} else {
    header("Location: $root/index.php");
    exit();
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>URL Indexing | Goggler</title>
        <link rel="stylesheet" href="styles/sitemap.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="container-fluid p-0 d-flex flex-column text-white min-vh-100">
        <header class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a href="<?= $root ?>/index.php">Home</a></li>
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">Features</a>
                        <div class="dropdown-menu p-0 border-info text-white">
                            <a href="<?= $root ?>/image_search.php">Image Search</a>
                            <a href="<?= $root ?>/advanced_search.php">Advanced Search</a>
                            <a href="<?= $root ?>/webmaster/dashboard.php">Overview</a>
                            <a href="<?= $root ?>/webmaster/sitemap.php">URL Indexing</a>
                        </div>
                    </div>
                </li>
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
                            <a href="<?= $root ?>/profile.php">Profile</a>
                            <a href="<?= $root ?>/logout.php">Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </header>
        <main class="d-flex flex-column justify-content-start align-items-center flex-grow-1">
            <h1>Site Map - Index Your Website</h1>
            <div class="container">
                <div class="row justify-content-center">
                    <hr class="w-100 w-lg-75 mt-2 mb-4">
                    <div class="col-12 col-md-8 col-lg-6">
                        <form id="sitemapFetchForm" class="d-flex flex-column gap-3">
                            <input type="text" class="bg-secondary text-white border-info p-3" id="sitemapUrl" placeholder="Sitemap URL" required>
                            <button type="submit" class="btn btn-primary">Fetch Sitemap</button>
                        </form>
                        <div id="sitemapFetchResult" class="d-none mt-4 p-3 bg-dark bg-opacity-25 rounded"></div>
                    </div>
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
                            <a href="<?= $root ?>/history.php">History</a>
                            <a href="<?= $root ?>/feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
        <script>
            const sitemapFetchForm = document.getElementById("sitemapFetchForm");
            const urlInput = document.getElementById("sitemapUrl");
            const resultDiv = document.getElementById("sitemapFetchResult");

            sitemapFetchForm.addEventListener("submit", async (event) => {
                event.preventDefault();
                const url = urlInput.value.trim();
                if (!url) {
                    resultDiv.classList.remove("d-none");
                    resultDiv.innerHTML = '<p class="text-danger">Please enter a valid URL</p>';
                    return;
                }

                resultDiv.classList.remove("d-none");
                resultDiv.innerHTML = '<p class="text-info">Fetching sitemap...</p>';
                try {
                    const response = await fetch("api/fetch_sitemap.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ url })
                    });

                    const data = await response.json();
                    if (response.ok) {
                        resultDiv.innerHTML = `<p class="text-success">${data.message}</p>`;
                    } else {
                        resultDiv.innerHTML = `<p class="text-danger">Error: ${data.error || "Something went wrong"}</p>`;
                    }
                } catch (error) {
                    resultDiv.innerHTML = `<p class="text-danger">Failed to fetch sitemap: ${error.message}</p>`;
                }
            });
        </script>
    </body>
</html>
