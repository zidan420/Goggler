<?php
	class MySql{
		private $conn;
		private $table;

		function __construct($servername, $username, $password, $dbname, $table){
			$this->conn = new mysqli($servername, $username, $password, $dbname);
			if ($this->conn->connect_error)  die("Connection failed: " . $this->conn->connect_error);
			$this->table = $table;
		}

		function insert_data($data, $table=""){
			if ($table != "") $this->table = $table;

			$columns = array_keys($data);
			$columns_string = implode(",", $columns);

			$values = array_map([$this->conn, "real_escape_string"], array_values($data));
			$values_string = "'" . implode("', '", $values) . "'";

			$query = "INSERT IGNORE INTO $this->table ($columns_string) VALUES ($values_string)";
			$result = $this->conn->query($query);
			
			if ($result) return true;
		}

		function update_data($data, $where, $table=""){
		    if ($table != "") $this->table = $table;

		    $set_clause = [];
		    foreach ($data as $col => $val) {
		        $set_clause[] = "$col = '" . $this->conn->real_escape_string($val) . "'";
		    }
		    $set_string = implode(", ", $set_clause);

		    $where_clause = [];
		    foreach ($where as $col => $val) {
		        $where_clause[] = "$col = '" . $this->conn->real_escape_string($val) . "'";
		    }
		    $where_string = implode(" AND ", $where_clause);

		    $query = "UPDATE $this->table SET $set_string WHERE $where_string";
		    $this->conn->query($query);
		}

		function delete_all(){
			$this->conn->query("DELETE FROM $this->table");
		}

		function get_next_url($offset, $table){
			$query = "SELECT url FROM $table LIMIT 1 OFFSET $offset";
			if ($row = $this->conn->query($query)->fetch_assoc()) return $row["url"];
		}

		function get_url_id($url, $table){
			$query = "SELECT id FROM $table WHERE url='$url'";
			if ($row = $this->conn->query($query)->fetch_assoc()) return $row["id"];
		}

		function get_url($id, $table){
			$query = "SELECT url FROM $table WHERE id='$id'";
			if ($row = $this->conn->query($query)->fetch_assoc()) return $row["url"];
		}

		function get_keyword_id($keyword, $table){
			$keyword = $this->conn->real_escape_string($keyword);
			$query = "SELECT id FROM $table WHERE keyword='$keyword'";
			if ($row = $this->conn->query($query)->fetch_assoc()) return $row["id"];
		}

		function query_all(){
			$query = "SELECT * FROM $this->table";
			$result = $this->conn->query($query);
			return $result;
		}

		function check_url_count($link, $table=""){
			if ($table != "") $this->table = $table;
			$query = "SELECT * FROM $this->table WHERE URL LIKE '$link'";
			return $this->conn->query($query)->num_rows;
		}
	}
?>