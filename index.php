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
            </form>
            <!-- Add ShortCurt Button -->


            <div id="shortcuts-container">
                <!-- Add shortcut button -->
                 <div class="shortcut-tile" id="add-shortcut">
                    <div class="add-icon">
                        <i class="bi bi-bookmark-plus-fill"></i>
                    </div>
                    <div class="shortcut-text">Add shortcut</div>
                 </div>
       
             <!-- Shortcuts will be added here by JavaScript -->
            </div>


      <!-- Modal for adding a new shortcut -->
      <div id="shortcut-modal" class="modal">
   <div class="modal-content">
      <h2>Add Shortcut</h2>
      <label for="site-name">Name:</label>
      <input type="text" id="site-name" placeholder="Google">
      <label for="site-url">URL:</label>
      <input type="url" id="site-url" placeholder="https://www.google.com">
      <div class="modal-buttons">
         <button id="cancel-btn">Cancel</button>
         <button id="add-btn">Add</button>
      </div>
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
                        <div class="dropdown-menu p-0 text-end text-white bg-transparent border-info">
                            <a href="history.php">History</a>
                            <a data-bs-toggle="modal" data-bs-target="#feedbackModal">Feedback</a>
                        </div>
                    </div>
                </li>
            </ul>
        </footer>
        <!-- Chatbot -->
        <div class="chat-icon position-fixed p-2 rounded-5" onclick="toggleChatbot()">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-chat" viewBox="0 0 16 16">
              <path d="M2.678 11.894a1 1 0 0 1 .287.801 11 11 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8 8 0 0 0 8 14c3.996 0 7-2.807 7-6s-3.004-6-7-6-7 2.808-7 6c0 1.468.617 2.83 1.678 3.894m-.493 3.905a22 22 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a10 10 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105"/>
            </svg>
        </div>
        <div class="chatbot-container position-fixed" id="chatbot">
            <div class="chatbot-header">Goggler Chat</div>
            <div class="chatbot-messages" id="chatMessages"></div>
            <form class="chatbot-input" onsubmit="sendMessage(event)">
                <input type="text" id="chatInput" placeholder="URL to test..." autocomplete="off" />
                <button type="submit">Send</button>
            </form>
        </div>
        <!-- Feedback Modal -->
        <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="feedbackModalLabel">Submit Feedback</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="feedbackForm" action="https://matuailtravels.com/submit_feedback.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="feedbackText" class="form-label">Your Feedback</label>
                                <textarea class="form-control" id="feedbackText" name="feedback" rows="4" placeholder="Tell us what you think..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-feedback">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
            function toggleChatbot() {
                const chatbot = document.getElementById("chatbot");
                chatbot.style.display = chatbot.style.display === "flex" ? "none" : "flex";
            }

            function sendMessage(event) {
                event.preventDefault();
                const input = document.getElementById("chatInput");
                const message = input.value.trim();
                if (!message) return;

                const messages = document.getElementById("chatMessages");
                
                // Add user message
                const userMsg = document.createElement("div");
                userMsg.className = "user-message";
                userMsg.textContent = message;
                messages.appendChild(userMsg);

                // Clear input
                input.value = "";

                // Scroll to bottom
                messages.scrollTop = messages.scrollHeight;

                // Send to backend (placeholder)
                fetch("chatbot.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ message: message })
                })
                .then(response => response.json())
                .then(data => {
                    // Add bot response
                    const botMsg = document.createElement("div");
                    botMsg.className = "bot-message";
                    botMsg.innerHTML = data.malicious ? "<p>⚠️ <strong>This URL is malicious!</strong></p><p>🛡️ <strong>Attack Type:</strong>".concat(data.attack_type, "</p>") : "<p>✅ <strong>This URL appears safe.</strong></p>";
                    messages.appendChild(botMsg);
                    messages.scrollTop = messages.scrollHeight;
                })
                .catch(error => {
                    console.error("Chat error:", error);
                    const botMsg = document.createElement("div");
                    botMsg.className = "bot-message";
                    botMsg.textContent = "Error connecting to chatbot.";
                    messages.appendChild(botMsg);
                    messages.scrollTop = messages.scrollHeight;
                });
            }
        </script>
        <script>
            document.getElementById("feedbackForm").addEventListener("submit", function(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ message: formData.get('feedback') })
                })
                .catch(error => {
                    console.error("Feedback error:", error);
                    alert("Error connecting to server.");
                });
                form.reset();
            });
        </script>
        <script>
    // Get DOM elements
    const addShortcutBtn = document.getElementById('add-shortcut');
    const modal = document.getElementById('shortcut-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const addBtn = document.getElementById('add-btn');
    const siteNameInput = document.getElementById('site-name');
    const siteUrlInput = document.getElementById('site-url');
    const shortcutsContainer = document.getElementById('shortcuts-container');


    // Open modal when clicking the add shortcut button
    addShortcutBtn.addEventListener('click', () => {
        modal.style.display = 'block';
        siteNameInput.value = '';
        siteUrlInput.value = '';
    });


    // Close modal when clicking the cancel button
    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });


    // Add new shortcut when clicking the add button
    addBtn.addEventListener('click', () => {
        const name = siteNameInput.value.trim();
        let url = siteUrlInput.value.trim();
       
        if (name && url) {
            // Add http:// if no protocol specified
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
                url = 'http://' + url;
            }
           
            // Create new shortcut element
            const shortcut = createShortcut(name, url);
           
            // Insert the new shortcut before the add button
            shortcutsContainer.insertBefore(shortcut, addShortcutBtn);
           
            // Close the modal
            modal.style.display = 'none';
           
            // Save shortcuts to localStorage
            saveShortcuts();
        }
    });


    // Click outside the modal to close it
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });


    // Function to create a new shortcut element


function createShortcut(name, url) {
    const shortcut = document.createElement('a');
    shortcut.className = 'site-shortcut';
    shortcut.href = url;
    shortcut.target = '_blank'; // Open in new tab
   
    const icon = document.createElement('div');
    icon.className = 'site-icon';
    icon.textContent = name.charAt(0).toUpperCase();
   
    const text = document.createElement('div');
    text.className = 'shortcut-text';
    text.textContent = name;
   
    // Create delete button
    const deleteBtn = document.createElement('div');
    deleteBtn.className = 'delete-btn';
    deleteBtn.innerHTML = '×';
    deleteBtn.title = 'Delete shortcut';
   
    // Add click event to delete button
    deleteBtn.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent the link from being followed
        e.stopPropagation(); // Prevent event bubbling
        shortcutsContainer.removeChild(shortcut);
        saveShortcuts(); // Update localStorage after deletion
    });
   
    shortcut.appendChild(icon);
    shortcut.appendChild(text);
    shortcut.appendChild(deleteBtn);
   
    return shortcut;
}


    // Function to save shortcuts to localStorage
    function saveShortcuts() {
        const shortcuts = [];
        const shortcutElements = document.querySelectorAll('.site-shortcut');
       
        shortcutElements.forEach(el => {
            if (!el.id || el.id !== 'add-shortcut') {
                shortcuts.push({
                    name: el.querySelector('.shortcut-text').textContent,
                    url: el.href
                });
            }
        });
       
        localStorage.setItem('shortcuts', JSON.stringify(shortcuts));
    }


    // Function to load shortcuts from localStorage
    function loadShortcuts() {
        const saved = localStorage.getItem('shortcuts');
       
        if (saved) {
            const shortcuts = JSON.parse(saved);
           
            shortcuts.forEach(item => {
                const shortcut = createShortcut(item.name, item.url);
                shortcutsContainer.insertBefore(shortcut, addShortcutBtn);
            });
        }
    }


    // Load saved shortcuts when the page loads
    document.addEventListener('DOMContentLoaded', () => {
        loadShortcuts();
    });
</script>
        <script src="sw-register.js"></script>
    </body>
</html>
