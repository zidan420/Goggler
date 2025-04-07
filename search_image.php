<!Doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once "config.php";
require_once "mysql_func.php";

$img = trim($_GET["img"] ?? "");
if (!$img) {
    header("Location: image_search.html");
    exit();
}

$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in) {
    $user_id = $_SESSION["user_id"];

    /* get profile icon */
    $profile_icon = $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg";

    /* Save search history if user is logged in */
    $conn->insert_data(["user_id" => $user_id, "query" => $img], "search_history");
} else {
    $profile_icon = "images/default-user.svg";
}

$results_per_page = 5;
$page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$offset = ($page - 1) * $results_per_page; /* 0, $results_per_page, $results_per_page*2 ... */

$results = $conn->query_images($img, $offset, $results_per_page);

// Get total results count for pagination
$total_results = $conn->query_images_count($img)->fetch_assoc()["count(*)"];
$total_pages = ceil($total_results / $results_per_page);
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Results | Goggler</title>
    <link rel="stylesheet" href="styles/search_image.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <main class="container flex-grow-1 py-4">
        <form
            action="search_image.php"
            class="d-flex justify-content-between align-items-center mt-2 col-9 col-md-6 col-lg-5 col-xl-4 position-relative">
            <input
                id="img"
                class="w-100 me-2 text-white p-3 fs-4 rounded-start-5 border-info bg-transparent"
                type="text"
                name="img"
                placeholder="Search Your Image Here ..." />
            <button
                        type="button"
                        class="btn-search border-0 bg-transparent"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse">
                <svg viewBox="0 -960 960 960" xmlns="http://www.w3.org/2000/svg">
                    <path
                                fill="#00FFFF"
                                d="M480-320q-50 -49-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35Zm240 160q-33 0-56.5-23.5T640-240q0-33 23.5-56.5T720-320q33 0 56.5 23.5T800-240q0 33-23.5 56.5T720-160Zm-440 40q-66 0-113-47t-47-113v-80h80v80q0 33 23.5 56.5T280-200h200v80H280Zm480-320v-160q0-33-23.5-56.5T680-680H280q-33 0-56.5 23.5T200-600v120h-80v-120q0-66 47-113t113-47h80l40-80h160l40 80h80q66 0 113 47t47 113v160h-80Z"></path>
                </svg>
            </button>
            <div class="collapse rounded-5 w-100 p-3 top-0 position-absolute" id="collapse">
                <div class="d-flex justify-content-between">
                    <h5 class="text-dark">Search any image with Goggler</h5>
                    <button
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse"
                                class="btn-close"
                                type="button"></button>
                </div>
                <div id="collapse-body" class="d-flex justify-content-center align-items-center rounded-3">
                    <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="30px"
                                height="30px"
                                fill="grey"
                                class="bi bi-images"
                                viewBox="0 0 16 16">
                        <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
                        <path
                                    d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z" />
                    </svg>
                    <span class="text-dark mx-1">Drag an image here or</span>
                    <a id="upload-link" class="text-primary text-decoration-none" accept="image/">upload a file</a>
                    <input type="file" id="file-input" class="d-none" />
                </div>
                <div id="search-results"></div>
            </div>
        </form>
        <hr>
        <h2>Search Results for "<?= htmlspecialchars($img) ?>"</h2>
        <p><?= $total_results ?> results found</p>

        <!-- Search Results -->
        <?php if (!empty($results) && $results->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($row = $results->fetch_assoc()): ?>
                    <li class="list-group-item search-result">
                        <h3>
                            <a href='<?= htmlspecialchars($row["url"]) ?>' target="_blank">
                                <?= htmlspecialchars($row["title"] ?? "No Title") ?>
                            </a>
                        </h3>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&img=<?= urlencode($img) ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <!-- No pagination if there is only 1 page -->
                    <?php if ($total_pages == 1) {
                        break;
                    } ?>
                    <li class='page-item <?= $i === $page ? "active" : "" ?>'>
                        <a class="page-link" href="?page=<?= $i ?>&img=<?= urlencode($img) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&img=<?= urlencode($img) ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
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
    <script>
            document.addEventListener("DOMContentLoaded", function () {
                function handleFileUpload(file) {
                    if (!file.type.startsWith("image/")) {
                        alert("Please upload an image file");
                        return;
                    }

                    let reader = new FileReader();

                    reader.onload = function (e) {
                        collapseBody.innerHTML = "";

                        // Create and insert image
                        let img = document.createElement("img");
                        img.src = e.target.result;
                        img.alt = "Uploaded Image";

                        collapseBody.appendChild(img);
                    };

                    reader.readAsDataURL(file);

                    // Send to server using AJAX
                    let formData = new FormData();
                    formData.append("image", file);
                    fetch("search_hash.php", {
                        method: "POST",
                        body: formData
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === "success") {
                                displayResult(data.data);
                            } else if (data.status === "not_found") {
                                displayResult("not_found");
                            }
                        })
                        .catch((error) => console.error("Error:", error));
                }

                function displayResult(result) {
                    let resultContainer = document.getElementById("search-results");

                    if (result == "not_found") resultContainer.innerHTML = "<h5>Not Found</h5>";
                    else {
                        resultContainer.innerHTML = `
                        <h5>${result.title}</h5>
                        <p><a href="${result.url}" target="_blank">${result.url}</a></p>
                    `;
                    }
                }

                let collapseBody = document.getElementById("collapse-body");

                collapseBody.addEventListener("dragover", function (event) {
                    event.preventDefault();
                });

                /* Add Listener to DOM as file-input is dynamic */
                document.addEventListener("change", function (event) {
                    if (event.target.id === "file-input") {
                        let file = event.target.files[0];
                        handleFileUpload(file);
                    }
                });

                collapseBody.addEventListener("drop", function (event) {
                    event.preventDefault();
                    let file = event.dataTransfer.files[0];
                    handleFileUpload(file);
                });

                /* Trigger click on file-input
                 * Add Listener to DOM as upload-link is dynamic */
                document.addEventListener("click", function (event) {
                    if (event.target.id === "upload-link") {
                        event.preventDefault();
                        document.getElementById("file-input").click();
                    }
                });

                /* Reset the #collapseBody */
                document.getElementById("collapse").addEventListener("hidden.bs.collapse", function () {
                    document.getElementById("collapse-body").innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="30px" height="30px" fill="grey" class="bi bi-images" viewBox="0 0 16 16">
                    <path d="M4.502 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                    <path d="M14.002 13a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2V5A2 2 0 0 1 2 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v8a2 2 0 0 1-1.998 2M14 2H4a1 1 0 0 0-1 1h9.002a2 2 0 0 1 2 2v7A1 1 0 0 0 15 11V3a1 1 0 0 0-1-1M2.002 4a1 1 0 0 0-1 1v8l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094l1.777 1.947V5a1 1 0 0 0-1-1z"/>
                </svg>
                <span class="text-dark mx-1">Drag an image here or</span>
                <a id="upload-link" class="text-primary text-decoration-none" accept="image/">upload a file</a>
                <input type="file" id="file-input" class="d-none" />
            `;
                    /* Reset the search results */
                    document.getElementById("search-results").innerHTML = ``;
                });
            });
        </script>
</body>
</html>
