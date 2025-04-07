<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once "config.php";

$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in) {
    $user_id = $_SESSION["user_id"];

    /* Get profile icon */
    $profile_icon = $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg";

    /* Get search history */
    $result = $conn->get_search_history($user_id);
} else {
    header("Location: login.php");
    exit();
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>History | Goggler</title>
        <link rel="stylesheet" href="template.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="container-fluid p-0 d-flex flex-column text-white min-vh-100">
        <header class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a href="index.php">Home</a></li>
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">Features</a>
                        <div class="dropdown-menu p-0 border-info text-white">
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
                            <img
                                id="user_icon"
                                src="<?= $profile_icon ?>"
                                alt="User"
                                width="30"
                                height="30"
                                class="rounded-circle" />
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
        <main class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
    <h2 class="text-center">Your Search History</h2>
    <div class="table-responsive mt-4">
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-dark table-bordered">
                <thead>
                    <tr>
                        <th>Search Query</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["query"]) ?></td>
                            <td><?= $row["search_time"] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center fs-4 mt-3">No search history available.</p>
        <?php endif; ?>
    </div>
    <a href="index.php" class="btn btn-info mt-3">Back to Search</a>
</main>
        <footer class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a>Privacy</a></li>
                <li><a>Terms</a></li>
                <li>
                    <div class="dropdown">
                        <a data-bs-toggle="dropdown">Settings</a>
                        <div class="dropdown-menu p-0 text-end text-white border-info">
                            <a href="history.php">History</a>
                            <a href="feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
    </body>
</html>
