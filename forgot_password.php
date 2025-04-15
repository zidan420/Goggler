<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

require_once "config.php";

$success_message = $error_message = "";
$step = 1; /* Step 1: Enter email, Step 2: Enter token */

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["email"])) {
        $email = trim($_POST["email"]);

        /* Check if email exists */
        $result = $conn->email_exist($email);

        if ($result->num_rows > 0) {
            $user_id = $result->fetch_assoc()["id"];
            $token = rand(100000, 999999); /* 6-digit OTP token */
            $expiry = time() + 300; /* Token expires in 5 minutes */

            /* Store token */
            $conn->set_token($token, $expiry, $user_id);

            /* Send request for email with token */
            $ch = curl_init("https://matuailtravels.com/reset_password.php");

            /* Send json POST data to email server (api) */
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["token" => $token, "email" => $email]));

            curl_exec($ch);

            $_SESSION["reset_email"] = $email;
            $success_message = "A reset code has been sent to your email.";
            $step = 2; /* Move to token */
        } else {
            $error_message = "No account found with that email.";
        }
    } elseif (isset($_POST["token"])) {
        /* Token Verification */
        $email = $_SESSION["reset_email"] ?? "";
        $token = trim($_POST["token"]);

        $result = $conn->get_reset_expiry($email, $token);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (time() < $row["reset_expiry"]) {
                $_SESSION["verified_reset"] = true;
                header("Location: reset_password.php");
                exit();
            } else {
                $error_message = "Token has expired.";
            }
        } else {
            $error_message = "Invalid token.";
            $step = 2;
        }
    }
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Forgot Password | Goggler</title>
        <link rel="stylesheet" href="styles/forgot_password.css" />
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
            <h2>Forgot Password</h2>
            <?php if ($step == 1): ?>
                <p>Enter your email to receive a password reset code.</p>
                <form action="#" method="POST" class="w-100 p-3">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reset Code</button>
                </form>
            <?php elseif ($step == 2): ?>
                <p>Enter the reset code sent to your email.</p>
                <form action="#" method="POST" class="w-100 p-3">
                    <div class="mb-3">
                        <label for="token" class="form-label">Reset Code</label>
                        <input type="text" name="token" id="token" class="form-control" placeholder="Enter your reset code" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verify Code</button>
                </form>
            <?php endif; ?>

            <!-- Display Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success mt-3 w-100 text-center">
                    <?= $success_message ?>
                </div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-danger mt-3 w-100 text-center">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>
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
