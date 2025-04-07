<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
require "config.php";
$user_id = $_SESSION["user_id"];

/* Fetch user details */
$profile_icon = $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg";

/* profile picture upload */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_icon"])) {
    // Allow only image files
    $allowed_types = ["image/jpeg", "image/png", "image/svg+xml"];
    if (in_array($_FILES["profile_icon"]["type"], $allowed_types)) {
        $upload_dir = "uploads/";
        // Get file details
        $original_filename = pathinfo($_FILES["profile_icon"]["name"], PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($_FILES["profile_icon"]["name"], PATHINFO_EXTENSION));
        /* Only alphanumeric, underscore and dash is allowed */
        $safe_filename = preg_replace("/[^a-zA-Z0-9_-]/", "", $original_filename);
        $target_file = $upload_dir . $safe_filename . "." . $extension;

        // Check if file exists and add an incrementing number if needed
        $counter = 1;
        while (file_exists($target_file)) {
            $target_file = $upload_dir . $safe_filename . $counter . "." . $extension;
            $counter++;
        }
        /* Mvoe the image from temporary location to permanent folder */
        if (move_uploaded_file($_FILES["profile_icon"]["tmp_name"], $target_file)) {
            /* Delete the previous profile icon */
            if ($profile_icon !== "images/default-user.svg" && file_exists($profile_icon)) {
                unlink($profile_icon);
            }
            /* update database with new profile icon */
            $conn->set_profile_icon($target_file, $user_id);

            /* Refresh to show new icon */
            header("Location: profile.php");
            exit();
        } else {
            $error = "Failed to upload file.";
        }
    } else {
        $error = "Invalid file format. Please upload JPEG, PNG, or SVG.";
    }
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Goggler</title>
        <link rel="stylesheet" href="styles/profile.css" />
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
                        <div class="dropdown-menu p-0 bg-transparent border-info text-white">
                            <a href="image_search.php">Image Search</a>
                            <a href="advanced_search.php">Advanced Search</a>
                            <a href="web_master.php">Web Master</a>
                        </div>
                    </div>
                </li>
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
            </ul>
        </header>
        <main class="d-flex flex-column align-items-center mt-4 flex-grow-1">
        <h2>Profile</h2>
        
        <!-- Display Profile Picture -->
        <img src="<?= $profile_icon ?>" alt="Profile Picture" class="rounded-circle border border-info" width="200" height="200">

        <!-- Upload Form -->
        <form action="profile.php" method="POST" enctype="multipart/form-data" class="mt-3">
            <input type="file" name="profile_icon" accept="image/*" required>
            <button type="submit" class="btn btn-info mt-2">Update Profile Picture</button>
        </form>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <p class="text-danger"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
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
