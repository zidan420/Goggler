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

	/* Base URL */
	$url = "https://hianime.to";
	
	$de_url = new SplQueue();

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	
	$pattern = "/href=\"(.+?)\"/";
	if (preg_match_all($pattern, $response, $matches)){
		foreach ($matches[1] as $href) {
			$full_url = rel2abs($url, $href);
			echo $href."  -->  ".$full_url."<br>";

			/* Enqueue URL */
			$de_url[] = $full_url;
		}
	}

	while (!$de_url->isEmpty()) {
		$next_url = $de_url->dequeue();
		$db->insert_data($next_url);
	}
	$result = $db->query_all();
	if ($result->num_rows > 0){
		echo "$result->num_rows results<br>";
	}
	else echo "0 results<br>";

	if ($db->url_exists("https://hianime.to/community/board")) echo "link exists<br>";

	$db->delete_all();

?>