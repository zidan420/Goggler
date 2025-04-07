<?php
require_once 'mysql_func.php';

$config = parse_ini_file("config.ini");

define("DB_SERVER", $config["servername"]);
define("DB_USER", $config["username"]);
define("DB_PASS", $config["password"]);
define("DB_NAME", $config["dbname"]);
define("DB_TABLE", $config["tablename"]);

$conn = new MySql(DB_SERVER, DB_USER, DB_PASS, DB_NAME, DB_TABLE);

?>
