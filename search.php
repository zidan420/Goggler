<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once "config.php";
require_once "mysql_func.php";

// Initialize Memcached
$memcached = new Memcached();
$memcached->addServer("127.0.0.1", 11211);

// Get advanced search parameters
$and_query = trim($_GET["and"] ?? "");
$or_query = trim($_GET["or"] ?? "");
$nor_query = trim($_GET["nor"] ?? "");
$basic_query = trim($_GET["query"] ?? "");

// Determine query type
$is_advanced = !empty($and_query) || !empty($or_query) || !empty($nor_query);
$query = $is_advanced ? compact("and_query", "or_query", "nor_query") : $basic_query;

// Redirect to index if no query provided
if (!$basic_query && !$is_advanced) {
    header("Location: index.php");
    exit();
}

$user_logged_in = isset($_SESSION["user_id"]);
$user_id = $user_logged_in ? $_SESSION["user_id"] : 0;
if ($user_logged_in) {
    $user_id = $_SESSION["user_id"];

    /* get profile icon */
    $profile_icon = $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg";

    /* Save search history (use basic_query or a stringified advanced query) */
    $history_query = $is_advanced ? json_encode($query) : $basic_query;
    $conn->insert_data(["user_id" => $user_id, "query" => $history_query], "search_history");
} else {
    $profile_icon = "images/default-user.svg";
}

$results_per_page = 10;
$page = isset($_GET["page"]) ? max(1, intval($_GET["page"])) : 1;
$offset = ($page - 1) * $results_per_page; /* 0, $results_per_page, $results_per_page*2 ... */

// Create a unique cache key based on query and page
$cache_key = $is_advanced
    ? "search:adv:" . md5(strtolower($and_query . $or_query . $nor_query)) . ":page" . $page
    : "search:" . md5(strtolower($query)) . ":page" . $page;

// Check Memcached for cached results
$cached_data = $memcached->get($cache_key);
if ($cached_data !== false) {
    // Cache hit: Decode cached results
    [$results, $total_results] = json_decode($cached_data, true);
    $source = "Cached";
} else {
    // Cache miss: Query the database
    [$results, $total_results] = $is_advanced
        ? $conn->advanced_search($and_query, $or_query, $nor_query, $offset, $results_per_page)
        : $conn->search($query, $offset, $results_per_page);

    // Store results in Memcached for 60 seconds
    $memcached->set($cache_key, json_encode([$results, $total_results]), 60);
    $source = "Fresh";
}
$total_pages = ceil($total_results / $results_per_page);

$display_query = $is_advanced
    ? ($and_query ? "AND: $and_query " : "") .
        ($or_query ? "OR: $or_query " : "") .
        ($nor_query ? "NOT: $nor_query" : "")
    : $basic_query;
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Results | Goggler</title>
    <link rel="stylesheet" href="styles/search.css">
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
            action="search.php"
            class="d-flex justify-content-between align-items-center mt-2 col-9 col-md-6">
            <input
                id="query"
                class="w-100 me-2 text-white p-3 fs-4 border-info bg-transparent"
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
        <hr>
        <h2>Search Results for "<?= htmlspecialchars($display_query) ?>" (<?= $source ?>)</h2>
        <p><?= $total_results ?> results found</p>

        <!-- Search Results -->
        <?php if (!empty($results)): ?>
            <ul class="list-group">
                <?php foreach ($results as $row): ?>
                    <?php
                    $user_id = htmlspecialchars($user_id);
                    $url_id = htmlspecialchars($conn->get_url_id($row["url"], "urlInfo"));
                    $url = urlencode($row["url"]);
                    $target_url = "click.php?user_id=" . $user_id . "&" . "url_id=" . $url_id . "&" . "url=" . $url;
                    ?>
                    <li class="list-group-item search-result">
                        <h3>
                            <!-- To-do: href is vulnerable to XSS -->
                            <a href='<?= $target_url ?>'>
                                <?= htmlspecialchars($row["title"] ?? "No Title") ?>
                            </a>
                        </h3>
                        <p><?= htmlspecialchars($row["description"] ?? "No Description") ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center mt-4">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&query=<?= urlencode($query) ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <!-- No pagination if there is only 1 page -->
                    <?php if ($total_pages == 1) {
                        break;
                    } ?>
                    <li class='page-item <?= $i === $page ? "active" : "" ?>'>
                        <a class="page-link" href="?page=<?= $i ?>&query=<?= urlencode($query) ?>"><?= $i ?></a>
                    </li>
                    <!-- Stop after 10 pages -->
                    <?php if ($i == 10) {
                        break;
                    } ?>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&query=<?= urlencode($query) ?>">Next</a>
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
    function logClick(urlId, userId) {
        if (!userId) return; // Skip if not logged in
        
        /* AJAX request to log_click.php */
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "log_click.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify({ url_id: urlId, user_id: userId }));
    }
    </script>
</body>
</html>
