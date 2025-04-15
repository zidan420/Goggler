<?php
header("Content-Type: application/json");

// Enable error reporting for debugging (remove in production)
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/* Change to appropriate directory */
chdir("/var/www/html/goggler_zidan/");

require_once "config.php";
require_once "stop_words.php";
require_once "process_dom.php";

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["url"])) {
    echo json_encode(["error" => "No URL provided."]);
    exit();
}

$sitemapUrl = filter_var($data["url"], FILTER_VALIDATE_URL);
if (!$sitemapUrl) {
    echo json_encode(["error" => "Invalid URL."]);
    exit();
}

// Fetch sitemap
$sitemapXml = @file_get_contents($sitemapUrl);
if ($sitemapXml === false) {
    echo json_encode(["error" => "Failed to fetch sitemap."]);
    exit();
}

// Check if the response is actually XML
libxml_use_internal_errors(true);
$xml = simplexml_load_string($sitemapXml);
if ($xml === false) {
    echo json_encode(["error" => "Invalid XML format."]);
    exit();
}

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$inserted = 0;
$keywords_inserted = 0;
$links_inserted = 0;

foreach ($xml->url as $urlElement) {
    $url = (string) $urlElement->loc;
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        continue;
    }

    // Fetch URL content
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);

    if ($response === false) {
        error_log("cURL error for $url: " . curl_error($ch));
        continue;
    }

    // Extract headers and body
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // Check content type
    if (
        !preg_match(
            "#Content-Type\s*:\s*(text/html|text/php|image/png|image/webp|image/avif|image/jpg|image/jpeg)#i",
            $headers,
            $matches
        )
    ) {
        continue;
    }
    $contentType = $matches[1];

    // Insert into urlInfo
    if (!$conn->insert_data(["url" => $url], "urlInfo")) {
        continue;
    }
    $url_id = $conn->get_url_id($url, "urlInfo");
    $inserted++;

    // Update hash
    $hash = md5($body);
    $conn->update_data(["hash" => $hash], ["id" => $url_id], "urlInfo");

    // Handle non-text files (images)
    if (stripos($contentType, "text/html") === false && stripos($contentType, "text/php") === false) {
        $conn->update_data(["title" => basename($url)], ["id" => $url_id], "urlInfo");
        continue;
    }

    // Process HTML
    $process_dom = new ProcessDom($body);
    $title = $process_dom->extractTitle();
    $description = $process_dom->extractDescription();
    $doc_length = $process_dom->getDocumentLength();

    // Update urlInfo with metadata
    $conn->update_data(
        [
            "title" => $title ? substr($title, 0, 255) : "",
            "description" => $description,
            "doc_length" => $doc_length,
        ],
        ["id" => $url_id],
        "urlInfo"
    );

    // Extract and store keywords
    $keywords = $process_dom->extractKeywords();
    foreach ($keywords as $keyword) {
        if (!in_array($keyword, $stop_words)) {
            $frequency = $process_dom->getKeywordFrequency($keyword);
            if ($frequency == 0) {
                continue;
            }

            // Insert into keywordTable
            if ($conn->insert_data(["keyword" => substr($keyword, 0, 100)], "keywordTable")) {
                $keywords_inserted++;
            }

            // Get keyword ID
            $keyword_id = $conn->get_keyword_id($keyword, "keywordTable");

            // Insert into keyToUrl
            $conn->insert_data(
                [
                    "keywordId" => $keyword_id,
                    "urlId" => $url_id,
                    "frequency" => $frequency,
                ],
                "keyToUrl"
            );
        }
    }

    // Extract links (href)
    $pattern = "/href=\"(.+?)\"/";
    if (preg_match_all($pattern, $body, $matches)) {
        foreach ($matches[1] as $href) {
            $full_url = $href;
            if (!filter_var($full_url, FILTER_VALIDATE_URL)) {
                continue;
            }

            // Check content type
            curl_setopt($ch, CURLOPT_URL, $full_url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            $link_headers = curl_exec($ch);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            if (
                $link_headers &&
                preg_match(
                    "#Content-Type\s*:\s*(text/html|text/php|image/png|image/webp|image/avif|image/jpg|image/jpeg)#i",
                    $link_headers
                )
            ) {
                if ($conn->insert_data(["url" => $full_url], "urlInfo")) {
                    $to_url_id = $conn->get_url_id($full_url, "urlInfo");
                    $conn->insert_data(["sourceUrl" => $url_id, "destinationUrl" => $to_url_id], "outgoingUrl");
                    $links_inserted++;
                }
            }
        }
    }

    // Extract images (src, alt)
    $img_pattern = "/<img[^>]+src=\"(.+?)\"[^>]*alt=\"(.+?)\"/i";
    if (preg_match_all($img_pattern, $body, $img_matches, PREG_SET_ORDER)) {
        foreach ($img_matches as $match) {
            $src = $match[1];
            $alt = $match[2];
            $full_img_url = $src;
            if (!filter_var($full_img_url, FILTER_VALIDATE_URL)) {
                continue;
            }

            // Check content type
            curl_setopt($ch, CURLOPT_URL, $full_img_url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            $img_headers = curl_exec($ch);
            curl_setopt($ch, CURLOPT_NOBODY, false);
            if ($img_headers && preg_match("#Content-Type\s*:\s*image/(png|webp|avif|jpg|jpeg)#i", $img_headers)) {
                if ($conn->insert_data(["url" => $full_img_url, "description" => substr($alt, 0, 255)], "urlInfo")) {
                    $to_url_id = $conn->get_url_id($full_img_url, "urlInfo");
                    $conn->insert_data(["sourceUrl" => $url_id, "destinationUrl" => $to_url_id], "outgoingUrl");
                    $conn->update_data(["title" => basename($full_img_url)], ["id" => $url_id], "urlInfo");
                    $links_inserted++;
                }
            }
        }
    }
}

curl_close($ch);

// Calculate IDF
$conn->calculate_idf();

echo json_encode(["message" => "Sitemap processed. $inserted URLs added."]);
?>
