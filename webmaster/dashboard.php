<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
$root = "..";
require_once "$root/config.php";

/* User must be logged in AND user must be a web master */
$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in && $_SESSION["is_web_master"]) {
    $user_id = $_SESSION["user_id"];
    $profile_icon =
        "$root/" . ($conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? "images/default-user.svg");
    $performance_data = $conn->get_performance_data($user_id, "1m");
} else {
    header("Location: $root/index.php");
    exit();
}
?>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Overview | Goggler</title>
        <link rel="stylesheet" href="styles/dashboard.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="container-fluid p-0 d-flex flex-column text-white min-vh-100">
        <header class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a href="<?= $root ?>/index.php">Home</a></li>
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">Features</a>
                        <div class="dropdown-menu p-0 border-info text-white">
                            <a href="<?= $root ?>/image_search.php">Image Search</a>
                            <a href="<?= $root ?>/advanced_search.php">Advanced Search</a>
                            <a href="<?= $root ?>/webmaster/dashboard.php">Overview</a>
                            <a href="<?= $root ?>/web_master.php">Performance</a>
                            <a href="<?= $root ?>/web_master.php">URL Indexing</a>
                            <a href="<?= $root ?>/web_master.php">Removals</a>
                        </div>
                    </div>
                </li>
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
                            <a href="<?= $root ?>/profile.php">Profile</a>
                            <a href="<?= $root ?>/logout.php">Logout</a>
                        </div>
                    </div>
                </li>
            </ul>
        </header>
        <main class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
            <h1>Overview</h1>
            <hr>
            <div class="chart-container">
                <h3>Performance</h3>
                <div class="range-buttons mb-3">
                    <button class="btn btn-outline-light btn-sm me-1" data-range="7d">7 Days</button>
                    <button class="btn btn-outline-light btn-sm me-1 active" data-range="1m">1 Month</button>
                    <button class="btn btn-outline-light btn-sm me-1" data-range="1y">1 Year</button>
                    <button class="btn btn-outline-light btn-sm" data-range="all">Older</button>
                </div>
                <p id="totalClicks"></p>
                <canvas id="performanceChart" width="800" height="400"></canvas>
            </div>
        </main>
        <footer class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a>Privacy</a></li>
                <li><a>Terms</a></li>
                <li>
                    <div class="dropdown">
                        <a data-bs-toggle="dropdown">Settings</a>
                        <div class="dropdown-menu p-0 text-end text-white border-info">
                            <a href="<?= $root ?>/history.php">History</a>
                            <a href="<?= $root ?>/feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
        const mockData = {
            labels: ['3/8/25', '3/12/25', '3/16/25', '3/20/25', '3/24/25', '3/28/25', '4/1/25'],
            clicks: [24, 16, 8, 4, 0, 1, 0],
            totalClicks: 72
        };
        const performanceData = <?php echo json_encode($performance_data); ?>;
        let chart = null;
        function renderPerformanceChart(data, range='1m') {
            const ctx = document.getElementById("performanceChart").getContext("2d");
            document.getElementById("totalClicks").textContent = `${data.totalClicks} total web search clicks`;
            /* determine step size dynamically */
            const stepSize = Math.ceil((Math.ceil(Math.max(...data.clicks)/10)*10)/4) || 1;
            
            /* destroy previous chart */
            if (chart){
                chart.destroy();
            }

            /* draw the new chart */
            chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "Web Search Clicks",
                        data: data.clicks,
                        borderColor: "#0059ff",
                        backgroundColor: "rgba(0, 89, 255, 0.1)",
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: "Clicks", color: "#fff" },
                            ticks: { color: "#fff", stepSize: stepSize },
                            grid: { color: "rgba(255, 255, 255, 0.1)" }
                        },
                        x: {
                            title: { display: true, text: "Date", color: "#fff" },
                            ticks: { color: "#fff" },
                            grid: { color: "rgba(255, 255, 255, 0.1)" }
                        }
                    },
                    plugins: {
                        legend: { labels: { color: "#fff" } }
                    }
                }
            });
        }
        
        /* AJAX request to get_performance_data.php */
        const buttons = document.querySelectorAll(".range-buttons button");
        buttons.forEach(button => {
            button.addEventListener("click", async () => {
                const range = button.getAttribute("data-range");
                buttons.forEach(btn => btn.classList.remove("active"));
                button.classList.add("active");

                try {
                    const response = await fetch(`api/get_performance_data.php?range=${range}`);
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    const newData = await response.json();
                    if (newData.error) {
                        console.error("Server error:", newData.error);
                        renderPerformanceChart(mockData, range);
                        return;
                    }
                    renderPerformanceChart(newData, range);
                } catch (error) {
                    console.error("Fetch error:", error);
                    renderPerformanceChart(mockData, range);
                }
            });
        });
        
        /* render the chart with performanceData */
        renderPerformanceChart(performanceData, '1m');
        </script>
    </body>
</html>
