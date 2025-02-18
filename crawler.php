<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

	require_once 'stop_words.php';
	require_once 'mysql_func.php';
	require_once 'process_dom.php';

	/* Connect Database */
	$config_file = parse_ini_file("config.ini");
	$servername = $config_file['servername'];
	$username = $config_file['username'];
	$password = $config_file['password'];
	$dbname = $config_file['dbname'];
	$tablename = $config_file['tablename'];
	$db = new MySql($servername, $username, $password, $dbname, $tablename);

	function rel2abs($base, $relative){
		/* URL is already absolute URL */
		if (parse_url($relative, PHP_URL_SCHEME) != "") return $relative;

		/* queries and anchors */
		if ($relative[0] == "#" || $relative[0] == "?") return $base.$relative;

		/* extract scheme, host and path */
		extract(parse_url($base));

		/* remove non-directory elements from path */
		$path = preg_replace('#/[^/]*$#', '', $path);

		/* destroy path if relative url points to root */
		if ($relative[0] == "/") $path = "";

		/* construct absolute url */
		$abs_url = "$host$path/$relative";

		/* replace '//' or '/./' or '/../' with '/' */
		$re = array("#//#", "#/\./#", "#/\.\./#");

		for ($count = 1; $count > 0; $abs_url = preg_replace($re, '/', $abs_url, -1, $count));
		return "$scheme://$abs_url";
	}

	function url2host($link){
		extract(parse_url($link));
		return "$scheme://$host";
	}

	/* Base URL */
	$base_url = "https://suninme.org/best-websites";
	$max_pages = 2;
	$max_url = 10;
	$url_crawled = 1;
	$de_url = new SplQueue();
	$max_url_reached = false;

	$ch = curl_init();
	/* return response as string instead of directly printing to browser */
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	
	$de_url[] = $base_url;
	while (!$de_url->isEmpty()){
		$base_url = $de_url->dequeue();

		curl_setopt($ch, CURLOPT_URL, $base_url);
		$response = curl_exec($ch);

		/* Extract Headers */
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($response, 0, $header_size);

		/* Extract Body */
		$body = substr($response, $header_size);

		/* Store the URL, hash and ID (auto-generated) to database */
		echo "Inserting $base_url<br>";
		$db->insert_data(["URL" => "$base_url", "hash" => md5($body)], "CrawlerData");

		/* Skip non-text files */
		if (!preg_match("#Content-Type\s*:\s*text/html#i", $headers)) continue;

		/* Map all relevant words to this URL */
		$process_dom = new ProcessDom($body);
		$keywords = $process_dom->extractKeywords();
		foreach ($keywords as $keyword){
			if (!in_array($keyword, $stop_words)){
				$ID = $db->getID($base_url, 'CrawlerData');
				$db->insert_data(["WORD" => "$keyword", "ID" => "$ID"], "Crawler");
			}
		}

		/* Search for href attributes inside base_url */
		$pattern = "/href=\"(.+?)\"/";
		if (preg_match_all($pattern, $body, $matches)){
			foreach ($matches[1] as $href) {
				$full_url = rel2abs($base_url, $href);
				echo $href."  -->  ".$full_url."<br>";

				/* Count No. of times a URL (Domain) occurs in database */
				$count_url = $db->check_url_count(url2host($full_url)."%", "CrawlerData");
				if ($count_url != $max_pages && !$max_url_reached){
					/* Enqueue URL */
					$de_url[] = $full_url;

					/* Increase the count for new host only */
					if ($count_url == 0){
						$url_crawled += 1;
						if ($url_crawled == $max_url) $max_url_reached = true;
					}
				}
			}
		}
	}

	$result = $db->query_all();
	echo "$result->num_rows results\n";

	#$db->delete_all();

?>