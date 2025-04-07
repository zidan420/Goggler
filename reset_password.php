<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION["verified_reset"]) || !isset($_SESSION["reset_email"])) {
    header("Location: forgot_password.php");
    exit();
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION["reset_email"];
    $new_password = $_POST["password"];

    /*  Password validation */
    if (strlen($new_password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        /* Update password in database */
        $result = $conn->update_password_hash($hashed_password, $email);

        if ($result) {
            /* Destroy session and redirect to login */
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error updating password. Please try again.";
        }
    }
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Reset Password | Goggler</title>
        <link rel="stylesheet" href="styles/reset_password.css" />
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
                <li><a href="register.php">Sign Up</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </header>
        <main class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
            <h2>Reset Password</h2>
            <form action="#" method="POST" class="w-100 p-3">
                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success mt-3 w-100 text-center">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger mt-3 w-100 text-center">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>
            </form>
        </main>
        <footer class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a>Privacy</a></li>
                <li><a>Terms</a></li>
                <li>
                    <div class="dropdown">
                        <a data-bs-toggle="dropdown">Settings</a>
                        <div class="dropdown-menu p-0 text-end text-white border-info">
                            <a>History</a>
                            <a>Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
    </body>
</html>
