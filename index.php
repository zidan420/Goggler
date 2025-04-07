<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once "config.php";

$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in) {
    $user_id = $_SESSION["user_id"];
    $profile_icon = $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg";
} else {
    $profile_icon = "images/default-user.svg";
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Goggler</title>
        <link rel="stylesheet" href="styles/index.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="container-fluid p-0 d-flex flex-column text-white min-vh-100">
        <header class="d-flex justify-content-end">
            <ul class="m-2">
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">Features</a>
                        <div class="dropdown-menu p-0 bg-transparent border-info text-white">
                            <a href="image_search.php">Image Search</a>
                            <a href="advanced_search.php">Advanced Search</a>
                            <a href="web_master.php">Web Master</a>
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
                                <img id="user_icon" src="<?= $profile_icon ?>" alt="User" width="30" height="30" class="rounded-circle" />
                            </a>
                            <div class="dropdown-menu p-0 bg-transparent border-info text-white">
                                <a href="profile.php">Profile</a>
                                <a href="logout.php">Logout</a>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </header>
        <main class="d-flex flex-column justify-content-start align-items-center flex-grow-1">
            <h1>Goggler</h1>
            <form
                action="search.php"
                class="d-flex justify-content-between align-items-center mt-2 col-9 col-md-6 col-lg-5 col-xl-4 position-relative">
                <input
                    id="query"
                    class="w-100 me-2 text-white p-3 fs-4 rounded-start-5 border-info bg-transparent"
                    type="text"
                    name="query"
                    placeholder="Search Your Query Here ..." />
                <button type="submit" class="btn-search border-0 bg-transparent">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                        <path
                            fill="#00FFFF"
                            d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                    </svg>
                </button>
            </form>
        </main>
        <footer class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a>Privacy</a></li>
                <li><a>Terms</a></li>
                <li>
                    <div class="dropdown">
                        <a data-bs-toggle="dropdown">Settings</a>
                        <div class="dropdown-menu p-0 text-end text-white bg-transparent border-info">
                            <a href="history.php">History</a>
                            <a href="feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
    </body>
</html>
