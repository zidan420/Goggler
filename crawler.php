<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once "stop_words.php";
require_once "config.php";
require_once "process_dom.php";

/*
echo "\e[31mThis is red text\e[0m\n"; // Red text
echo "\e[32mThis is green text\e[0m\n"; // Green text
echo "\e[34mThis is blue text\e[0m\n"; // Blue text
*/

/* Relative tp Absolute URL */
function rel2abs($base, $relative)
{
    /* URL is already absolute URL */
    if (parse_url($relative, PHP_URL_SCHEME) != "") {
        return $relative;
    }

    /* queries and anchors */
    if ($relative[0] == "#" || $relative[0] == "?") {
        return $base . $relative;
    }

    /* extract scheme, host and path */
    extract(parse_url($base));

    /* remove non-directory elements from path */
    $path = preg_replace('#/[^/]*$#', "", $path);

    /* destroy path if relative url points to root */
    if ($relative[0] == "/") {
        $path = "";
    }

    /* construct absolute url */
    $abs_url = "$host$path/$relative";

    /* replace '//' or '/./' or '/../' with '/' */
    $re = ["#//#", "#/\./#", "#/\.\./#"];

    for ($count = 1; $count > 0; $abs_url = preg_replace($re, "/", $abs_url, -1, $count));
    return "$scheme://$abs_url";
}

/* Extracts domain only */
function url2host($link)
{
    extract(parse_url($link));
    return "$scheme://$host";
}

/* Follow all redirects to get the final URL */
function get_final_url($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    curl_exec($ch);
    /* gets the final url after all redirects */
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return $final_url;
}

/* does NOT follow redirect. Uses HEAD to get headers */
function get_headers_head($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $headers = curl_exec($ch);
    curl_close($ch);

    return $headers;
}

/*
 * Uses HEAD method to check for whitelisted URL. Returns final redirected url.
 * Allowed types: html, php, png, webp, jpeg, avif, jpg
 */
function isWhitelisted($url)
{
    $url = get_final_url($url);
    $headers = get_headers_head($url);
    if (!$headers) {
        return false;
    }

    /* extract Content-Type */
    if (!preg_match('/^content-type:\s*([^\r\n]+)/im', $headers, $matches)) {
        return false;
    }

    $contentType = trim($matches[1]);

    $allowed_types = ["text/html", "text/php", "image/png", "image/webp", "image/jpeg", "image/avif", "image/jpg"];

    if (!preg_match("#(" . implode("|", $allowed_types) . ")#i", $contentType)) {
        return false;
    }

    return $url;
}

/* Base URL */
$base_url = "https://suninme.org/best-websites";
//$base_url = "https://www.google.com/preferences?hl=bn";
$max_pages = 5;
$max_url = 10;
$url_crawled = 1; /* up to max_url */
$max_url_reached = false;
$offset = 0;

$ch = curl_init();
/* return response as string instead of directly printing to browser */
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
curl_setopt($ch, CURLOPT_TIMEOUT, 3);

$conn->insert_data(["url" => $base_url], "urlInfo");
while ($base_url) {
    curl_setopt($ch, CURLOPT_URL, $base_url);
    $response = curl_exec($ch);

    /* Check for curl errors */
    if ($response === false) {
        $error_code = curl_errno($ch);
        /* Timeout Error */
        if ($error_code === CURLE_OPERATION_TIMEDOUT) {
            echo "\e[31mTimed out... Removing entry: $base_url\e[0m\n";
            /* Remove the entry */
            $conn->delete_data(["url" => $base_url], "urlInfo");
            /* Move to the next pending url */
            $base_url = $conn->get_next_url($offset, "urlInfo");
            continue;
        } else {
            echo "\e[31mError: UNKNOWN\e[0m\n";
        }
    }

    /* Extract Response Headers */
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);

    /* Extract Response Body */
    $body = substr($response, $header_size);

    /* Skip if not White-listed files (html, php, png, jpg, jpeg, webp, avif) */
    if (
        !preg_match(
            "#Content-Type\s*:\s*(text/html|text/php|image/png|image/webp|image/avif|image/jpg|image/jpeg)#i",
            $headers,
            $matches
        )
    ) {
        /* Remove the entry */
        $conn->delete_data(["url" => $base_url], "urlInfo");
        /* Move to the next pending url */
        $base_url = $conn->get_next_url($offset, "urlInfo");
        continue;
    }

    /* Get url id */
    $url_id = $conn->get_url_id($base_url, "urlInfo");
    /* Store the URL, hash and ID (auto-generated) to database */
    echo "\e[32mUpdate $base_url\e[0m\n";
    $conn->update_data(["hash" => md5($body)], ["id" => $url_id], "urlInfo");

    /* Skip non-text files */
    if (stripos($matches[1], "text/html") === false && stripos($matches[1], "text/php") === false) {
        $conn->update_data(["title" => basename($base_url)], ["id" => $url_id], "urlInfo");
        /* Change base_url to next pending url */
        $offset++;
        $base_url = $conn->get_next_url($offset, "urlInfo");
        continue;
    }

    $process_dom = new ProcessDom($body);

    /* Store the title to database */
    $conn->update_data(
        [
            "title" => $process_dom->extractTitle(),
            "description" => $process_dom->extractDescription(),
            "doc_length" => $process_dom->getDocumentLength(),
        ],
        ["id" => $url_id],
        "urlInfo"
    );

    /* Map all relevant words to this (base) URL */
    $keywords = $process_dom->extractKeywords();
    foreach ($keywords as $keyword) {
        if (!in_array($keyword, $stop_words)) {
            /* If the keyword is not used in the body at all, skip */
            $frequency = $process_dom->getKeywordFrequency($keyword);
            if ($frequency == 0) {
                continue;
            }

            /* Insert keyword to keywordTable. No duplicates */
            $conn->insert_data(["keyword" => $keyword], "keywordTable");

            /* Get keyword's id */
            $keyword_id = $conn->get_keyword_id($keyword, "keywordTable");

            /* Insert keyword ID and url ID to keyToUrl Table */
            $conn->insert_data(
                [
                    "keywordId" => "$keyword_id",
                    "urlId" => "$url_id",
                    "frequency" => $frequency,
                ],
                "keyToUrl"
            );
        }
    }

    /* Search for href attributes inside base_url */
    $pattern = "/href=\"(.+?)\"/";
    if (preg_match_all($pattern, $body, $matches)) {
        foreach ($matches[1] as $href) {
            $full_url = rel2abs($base_url, $href);
            echo $href . "  -->  " . $full_url . "\n";

            /* Count No. of times a URL (Domain) occurs in database */
            $count_url = $conn->check_url_count(url2host($full_url) . "%", "urlInfo");
            if ($count_url != $max_pages && !$max_url_reached) {
                /* Skip any URLs that are not whitelisted */
                $full_url = isWhitelisted($full_url); /* gets the final url after redirect */
                if (!$full_url) {
                    continue;
                }

                /* Store URL in database for crawling later */
                if ($conn->insert_data(["URL" => $full_url], "urlInfo")) {
                    echo "\e[34mInserting $full_url\e[0m\n";
                }

                /* Store Source & destination URL */
                $to_url_id = $conn->get_url_id($full_url, "urlInfo");
                $conn->insert_data(["sourceUrl" => $url_id, "destinationUrl" => $to_url_id], "outgoingUrl");

                /* Increase the count for new host only */
                if ($count_url == 0) {
                    $url_crawled += 1;
                    if ($url_crawled == $max_url) {
                        $max_url_reached = true;
                    }
                }
            }
        }
    }
    /* Change base_url to next pending url */
    $offset++;
    $base_url = $conn->get_next_url($offset, "urlInfo");
}
curl_close($ch);
$conn->calculate_idf();

?>
