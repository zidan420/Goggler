<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

	require 'mysql_func.php';

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
	$max_pages = 10;
	$max_url = 3;
	$url_crawled = 0;
	
	$de_url = new SplQueue();
	$ch = curl_init();
	/* return response as string instead of directly printing to browser */
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	while ($url_crawled != $max_url){
		curl_setopt($ch, CURLOPT_URL, $base_url);
		$response = curl_exec($ch);

		/* Search for links inside href */
		$pattern = "/href=\"(.+?)\"/";
		if (preg_match_all($pattern, $response, $matches)){
			foreach ($matches[1] as $href) {
				$full_url = rel2abs($base_url, $href);
				echo $href."  -->  ".$full_url."\n";

				$count_url = $db->check_url_count(url2host($full_url)."%");
				if ($count_url != $max_pages){
					/* Store the URL to database */
					echo "Inserting $full_url\n";
					$db->insert_data($full_url);
					/* Enqueue URL */
					$de_url[] = $full_url;

					/* Increase the count for new host only */
					if ($count_url == 0){
						$url_crawled += 1;
						if ($url_crawled == $max_url) break;
					}
				}
			}
		}

		if (!$de_url->isEmpty()) {
			$base_url = $de_url->dequeue();
		}
	}

	$result = $db->query_all();
	echo "$result->num_rows results\n";

	// $db->delete_all();

?>
