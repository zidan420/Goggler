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

    // Check if the user is a web master of any site
    $result = $conn->is_web_master($user_id);

    // If the user is a web master of any site
    if ($result->num_rows > 0) {
        $_SESSION["is_web_master"] = true;
        header("Location: webmaster/dashboard.php");
        exit();
    } else {
        $web_master_sites = null;
    }
    // Handle site verification
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["verify_site_url"]) && !isset($_POST["complete_verification"])) {
            $site_url = $_POST["verify_site_url"];
            $verification_code = generate_unique_code();
            // Store the code and timestamp in session
            $_SESSION["verification_code"] = $verification_code;
            $_SESSION["verification_url"] = $site_url;
            $_SESSION["verification_start"] = time();
            $show_verification_step = true;
        } elseif (isset($_POST["complete_verification"]) && isset($_SESSION["verification_code"])) {
            $site_url = $_SESSION["verification_url"];
            $verification_code = $_SESSION["verification_code"];
            $start_time = $_SESSION["verification_start"];

            // Check if within 3 minutes (180 seconds)
            if (time() - $start_time <= 180) {
                $verification_success = verify_site_ownership($site_url, $user_id, $verification_code);
                if ($verification_success) {
                    $conn->insert_data(["user_id" => $user_id, "site_url" => $site_url], "user_sites");
                    /*$message = "Site successfully verified and added to your web master list!"; */
                    unset($_SESSION["verification_code"]);
                    unset($_SESSION["verification_url"]);
                    unset($_SESSION["verification_start"]);
                    $_SESSION["is_web_master"] = true;
                    header("Location: webmaster/dashboard.php");
                    exit();
                } else {
                    $message = "Verification failed. The code wasn't found on your site.";
                }
            } else {
                $message = "Verification timed out. Please try again.";
                unset($_SESSION["verification_code"]);
                unset($_SESSION["verification_url"]);
                unset($_SESSION["verification_start"]);
            }
        }
    }
} else {
    header("Location: login.php");
    exit();
}

function generate_unique_code()
{
    // generate a random 9-digit code
    return str_pad(rand(100000000, 999999999), 9, "0", STR_PAD_LEFT);
}

function verify_site_ownership($site_url, $user_id, $verification_code)
{
    // fetch the html
    $site_content = file_get_contents($site_url);

    // match the meta tag
    $pattern = '/<meta\s+name=["\']goggler-site-verification["\']\s+content=["\']' . $verification_code . '["\']/i';
    if (preg_match($pattern, $site_content)) {
        return true;
    }
    return false;
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Performance | Goggler</title>
        <link rel="stylesheet" href="template.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function startTimer(duration, display) {
                var timer = duration, minutes, seconds;
                var interval = setInterval(function () {
                    minutes = parseInt(timer / 60, 10);
                    seconds = parseInt(timer % 60, 10);
                    seconds = seconds < 10 ? "0" + seconds : seconds;
                    display.textContent = minutes + ":" + seconds;

                    if (--timer < 0) {
                        clearInterval(interval);
                        document.getElementById("completeVerificationBtn").disabled = true;
                        display.textContent = "Time's up!";
                    }
                }, 1000);
            }
            
            function copyToClipboard() {
                var metaTag = document.getElementById("metaTag").textContent;
                navigator.clipboard.writeText(metaTag).then(function() {
                    var copyButton = document.getElementById("copyButton");
                    copyButton.textContent = "Copied!";
                    setTimeout(function() {
                        copyButton.textContent = "Copy";
                    }, 2000);
                }, function(err) {
                    console.error('Could not copy text: ', err);
                });
            }
            
            window.onload = function () {
                <?php if (isset($show_verification_step) && $show_verification_step): ?>
                    var threeMinutes = 180;
                    var display = document.querySelector('#timer');
                    startTimer(threeMinutes, display);
                <?php endif; ?>
            };
        </script>
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
        <main class="d-flex flex-column justify-content-start align-items-center flex-grow-1">
            <h1>Web Master</h1>

            <?php if ($user_logged_in && $web_master_sites): ?>
                <h3>You are the web master of the following sites:</h3>
                <ul>
                    <?php foreach ($web_master_sites as $site): ?>
                        <li><a href="<?= $site ?>"><?= $site ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php elseif ($user_logged_in): ?>
                <?php if (isset($show_verification_step) && $show_verification_step): ?>
                    <div class="text-center">
                        <p>Please add this meta tag to your site's HTML head section:</p>
                        <div class="d-flex align-items-center justify-content-center">
                            <code id="metaTag">&lt;meta name="goggler-site-verification" content="<?= $verification_code ?>"&gt;</code>
                            <button type="button" id="copyButton" class="btn btn-sm btn-outline-info ms-2" onclick="copyToClipboard()">Copy</button>
                        </div>
                        <p class="mt-2">Time remaining: <span id="timer">3:00</span></p>
                        <form method="POST">
                            <input type="hidden" name="complete_verification" value="1">
                            <button type="submit" id="completeVerificationBtn" class="btn btn-info">Complete Verification</button>
                        </form>
                    </div>
                <?php else: ?>
                    <p>You are not a web master of any site.</p>
                    <h3>Verify your site ownership:</h3>
                    <?php if (isset($message)) {
                        echo "<p>$message</p>";
                    } ?>
                    <form method="POST" class="d-flex justify-content-center mt-2">
                        <input type="url" name="verify_site_url" class="form-control" placeholder="Enter your site URL" required />
                        <button type="submit" class="btn btn-info ms-2">Verify Site</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p>Please log in to verify your web master sites.</p>
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
