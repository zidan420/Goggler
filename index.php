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
                <button type="button" id="bookmark-btn" class="btn btn-outline-info ms-2" title="Bookmark this query">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.546a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"/>
                    </svg>
                </button>
            </form>
            <!-- Display Bookmarks -->
            <div class="mt-4 text-center">
                <h3>Your Bookmarks</h3>
                <ul class="list-group list-group-flush w-50 mx-auto" id="bookmark-list">
                </ul>
            </div>
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
        <script>
            /* load bookmarks from localStorage */
            function loadBookmarks() {
                const bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
                const bookmarkList = document.getElementById('bookmark-list');
                bookmarkList.innerHTML = ''; // Clear existing list

                bookmarks.slice(0, 5).forEach((query, index) => { // Limit to 5
                    const li = document.createElement('li');
                    li.className = 'list-group-item bg-transparent text-white border-info d-flex justify-content-between align-items-center';
                    li.innerHTML = `
                        <a href="search.php?query=${encodeURIComponent(query)}" class="text-info">
                            ${query}
                        </a>
                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteBookmark(${index})">X</button>
                    `;
                    bookmarkList.appendChild(li);
                });
            }

            function saveBookmark(query) {
                const bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
                if (!bookmarks.includes(query)) { // Avoid duplicates
                    bookmarks.unshift(query); // Add to start
                    localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
                }
            }

            function deleteBookmark(index) {
                const bookmarks = JSON.parse(localStorage.getItem('bookmarks') || '[]');
                bookmarks.splice(index, 1); // Remove at index
                localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
                loadBookmarks(); // Refresh list
            }

            /* Bookmark button event listener */
            document.getElementById('bookmark-btn').addEventListener('click', function() {
                const query = document.getElementById('query').value.trim();
                if (query) {
                    saveBookmark(query);
                    alert('Query bookmarked successfully!');
                    loadBookmarks();
                } else {
                    alert('Please enter a query to bookmark.');
                }
            });

            /* Load bookmarks on page load */
            window.onload = loadBookmarks;
        </script>
        <script src="sw-register.js"></script>
    </body>
</html>
