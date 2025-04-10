<!doctype html>
<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
$root = "../";
require_once $root . "config.php";

$user_logged_in = isset($_SESSION["user_id"]);
if ($user_logged_in && $_SESSION["is_web_master"]) {
    $user_id = $_SESSION["user_id"];
    $profile_icon =
        $conn->get_profile_icon($user_id)->fetch_assoc()["profile_icon"] ?? $root . "images/default-user.svg";
} else {
    header("Location: ../index.php");
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    <body class="container-fluid p-0 d-flex flex-column text-white min-vh-100">
        <header class="d-flex justify-content-end">
            <ul class="m-2">
                <li><a href="<?= $root ?>index.php">Home</a></li>
                <li>
                    <div class="dropdown">
                        <a class="dropdown-toggle" data-bs-toggle="dropdown">Features</a>
                        <div class="dropdown-menu p-0 border-info text-white">
                            <a href="<?= $root ?>image_search.php">Image Search</a>
                            <a href="<?= $root ?>advanced_search.php">Advanced Search</a>
                            <a href="<?= $root ?>web_master.php">Web Master</a>
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
                            <a href="<?= $root ?>profile.php">Profile</a>
                            <a href="<?= $root ?>logout.php">Logout</a>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
            </ul>
        </header>
        <main class="d-flex flex-column justify-content-center align-items-center flex-grow-1">
            <div class="dashboard-container">
                <h1>Goggler Dashboard</h1>
                <div class="nav-tabs">
                    <a href="dashboard.php" class="active">Overview</a>
                    <a href="performance.php">Performance</a>
                    <a href="url_inspection.php">URL Inspection</a>
                    <a href="indexing.php">Indexing</a>
                </div>
                <div class="content">
                    <h2>Overview</h2>
                    <div class="chart-container">
                        <h3>Performance</h3>
                        <p id="totalClicks"></p>
                        <canvas id="performanceChart"></canvas>
                    </div>
                    <p>Summary of website status...</p>
                </div>
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
                            <a href="<?= $root ?>history.php">History</a>
                            <a href="<?= $root ?>feedback.php">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
        <script>
            // Mock data as fallback
            const mockData = {
                labels: ['3/8/25', '3/12/25', '3/16/25', '3/20/25', '3/24/25', '3/28/25', '4/1/25'],
                clicks: [24, 16, 8, 4, 0, 1, 0],
                totalClicks: 72
            };

            async function fetchPerformanceData() {
                try {
                    const response = await fetch("api/performance_data.php");
                    const data = await response.json();
                    if (response.ok) {
                        return data;
                    } else {
                        console.error("Error fetching performance data:", data.error);
                        return mockData;
                    }
                } catch (error) {
                    console.error("Failed to fetch performance data:", error.message);
                    return mockData;
                }
            }

            async function renderPerformanceChart() {
                const data = await fetchPerformanceData();
                const ctx = document.getElementById("performanceChart").getContext("2d");
                document.getElementById("totalClicks").textContent = `${data.totalClicks} total web search clicks`;

                new Chart(ctx, {
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
                                title: {
                                    display: true,
                                    text: "Clicks",
                                    color: "#fff"
                                },
                                ticks: {
                                    color: "#fff"
                                },
                                grid: {
                                    color: "rgba(255, 255, 255, 0.1)"
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: "Date",
                                    color: "#fff"
                                },
                                ticks: {
                                    color: "#fff"
                                },
                                grid: {
                                    color: "rgba(255, 255, 255, 0.1)"
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    color: "#fff"
                                }
                            }
                        }
                    }
                });
            }

            // Render chart on page load
            renderPerformanceChart();
        </script>
    </body>
</html>
