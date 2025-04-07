<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goggler - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0a0f1c;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
        }
        .tabs {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid cyan;
            padding-bottom: 10px;
        }
        .tab {
            cursor: pointer;
            padding: 10px 20px;
            background: transparent;
            border: 1px solid cyan;
            color: cyan;
            border-radius: 5px;
        }
        .tab:hover, .tab.active {
            background: cyan;
            color: #0a0f1c;
        }
        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid cyan;
            margin-top: 10px;
            border-radius: 5px;
        }
        .tab-content.active {
            display: block;
        }
        .url-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .url-form input[type="text"] {
            padding: 8px;
            border: 1px solid cyan;
            border-radius: 5px;
            background: #1a2333;
            color: #fff;
            flex-grow: 1;
        }
        .url-form button {
            padding: 8px 16px;
            background: cyan;
            border: none;
            border-radius: 5px;
            color: #0a0f1c;
            cursor: pointer;
        }
        .url-form button:hover {
            background: #00b3b3;
        }
        .result {
            margin-top: 10px;
        }
        /* New styles for the chart */
        .chart-container {
            background: #1a2333;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Goggler Dashboard</h1>
        <div class="tabs">
            <button class="tab active" onclick="openTab(event, 'overview')">Overview</button>
            <button class="tab" onclick="openTab(event, 'performance')">Performance</button>
            <button class="tab" onclick="openTab(event, 'url')">URL Inspection</button>
            <button class="tab" onclick="openTab(event, 'indexing')">Indexing</button>
        </div>
        <div id="overview" class="tab-content active">
            <h2>Overview</h2>
            <div class="chart-container">
                <h3>Performance</h3>
                <p id="totalClicks"></p>
                <canvas id="performanceChart"></canvas>
            </div>
            <p>Summary of website status...</p>
        </div>
        <div id="performance" class="tab-content">
            <h2>Performance</h2>
            <form id="performanceForm" class="url-form">
                <input type="text" id="performanceUrl" placeholder="Enter URL (e.g., https://example.com)" required>
                <button type="submit">Check Speed</button>
            </form>
            <div id="performanceResult" class="result"></div>
        </div>
        <div id="url" class="tab-content">
            <h2>URL Inspection</h2>
            <form id="urlForm" class="url-form">
                <input type="text" id="urlInput" placeholder="Enter URL (e.g., https://example.com)" required>
                <button type="submit">Inspect</button>
            </form>
            <div id="urlResult" class="result"></div>
        </div>
        <div id="indexing" class="tab-content">
            <h2>Indexing</h2>
            <ul>
                <li>Pages</li>
                <li>Removals</li>
            </ul>
            <h2>Fetch Sitemap from URL</h2>
            <form id="sitemapFetchForm">
                <input type="text" id="sitemapUrl" placeholder="Enter sitemap URL (e.g., https://example.com/sitemap.xml)" required>
                <button type="submit">Fetch Sitemap</button>
            </form>
            <div id="sitemapFetchResult" class="result"></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function openTab(evt, tabName) {
            let i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

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

        // Handle URL form submission
        document.getElementById("urlForm").addEventListener("submit", async function(event) {
            event.preventDefault();
            const urlInput = document.getElementById("urlInput").value;
            const resultDiv = document.getElementById("urlResult");
            resultDiv.innerHTML = "Checking...";

            try {
                const response = await fetch("api/check-url.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ url: urlInput }),
                });

                const data = await response.json();
                if (response.ok) {
                    resultDiv.innerHTML = data.inDatabase
                        ? `<p>URL <strong>${urlInput}</strong> is in the database.</p>`
                        : `<p>URL <strong>${urlInput}</strong> is not in the database.</p>`;
                } else {
                    resultDiv.innerHTML = `<p>Error: ${data.error || "Something went wrong"}</p>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<p>Failed to connect to the server: ${error.message}</p>`;
            }
        });

        document.getElementById("sitemapFetchForm").addEventListener("submit", async function(event) {
            event.preventDefault();
            const urlInput = document.getElementById("sitemapUrl").value;
            const resultDiv = document.getElementById("sitemapFetchResult");
            resultDiv.innerHTML = "Fetching sitemap...";

            try {
                const response = await fetch("api/fetch_sitemap.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ url: urlInput }),
                });

                const data = await response.json();
                if (response.ok) {
                    resultDiv.innerHTML = `<p>${data.message}</p>`;
                } else {
                    resultDiv.innerHTML = `<p>Error: ${data.error || "Something went wrong"}</p>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<p>Failed to fetch sitemap: ${error.message}</p>`;
            }
        });

        document.getElementById("performanceForm").addEventListener("submit", async function(event) {
            event.preventDefault();
            const urlInput = document.getElementById("performanceUrl").value;
            const resultDiv = document.getElementById("performanceResult");
            resultDiv.innerHTML = "Fetching...";

            try {
                const response = await fetch("api/performance_check.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ url: urlInput }),
                });

                const data = await response.json();
                if (response.ok) {
                    resultDiv.innerHTML = `<p>Time taken: ${data.timeTaken} seconds</p>`;
                } else {
                    resultDiv.innerHTML = `<p>Error: ${data.error || "Something went wrong"}</p>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<p>Failed to fetch: ${error.message}</p>`;
            }
        });
    </script>
</body>
</html>