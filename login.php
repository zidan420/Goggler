<!DOCTYPE html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}
require "config.php";

$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    /* Fetch user data */
    $user_data = $conn->get_password_hash($username);

    if ($user_data->num_rows > 0) {
        $user_data = $user_data->fetch_assoc();
        $id = $user_data["id"];
        $password_hash = $user_data["password_hash"];

        /* Verify password matches the hash */
        if (password_verify($password, $password_hash)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["username"] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Incorrect username or password";
        }
    } else {
        $error_message = "Incorrect username or password";
    }
}
?>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Login | Goggler</title>
        <link rel="stylesheet" href="styles/login.css" />
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
            <h2>Login</h2>
            <form action="#" method="POST" class="w-100 p-3">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="mb-3 text-end">
                    <a href="forgot_password.php" class="text-light">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <!-- Display Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger mt-3 w-100 text-center" >
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
                            <a href="history.php">History</a>
                            <a href="feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
    </body>
</html>
