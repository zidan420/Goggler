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

			$where_clause = [];
			foreach ($data as $col => $val){
				$where_clause[] = "$col = '" . $this->conn->real_escape_string($val) . "'";
			}
			$where_string = implode(" AND ", $where_clause);

			$query = "INSERT INTO $this->table ($columns_string) SELECT $values_string WHERE NOT EXISTS (SELECT 1 FROM $this->table WHERE $where_string)";
			/*$link = mysqli_real_escape_string($this->conn, $link);
			$word = mysqli_real_escape_string($this->conn, $word);
			$query = "INSERT INTO $this->table (WORD, URL) SELECT '$word', '$link'  WHERE NOT EXISTS (SELECT 1 FROM $this->table WHERE WORD='$word' AND URL='$link')";*/
			if (!$this->conn->query($query)){
				echo "Error: ".$query."<br>".$this->conn->error;
				return false;
			}
			return true;
		}

		function delete_all(){
			$query = "DELETE FROM $this->table";
			if (!$this->conn->query($query)){
				echo "Error: ".$query."<br>".$this->conn->error;
				return false;
			}
			return true;
		}

		function getID($link, $table){
			$link = $this->conn->real_escape_string($link);
			$query = "SELECT ID FROM $table WHERE URL='$link'";
			$result = $this->conn->query($query);
			if (!$result){
				echo "Error: ".$query."<br>".$this->conn->error;
				return false;
			}
			if ($row = $result->fetch_assoc()) return $row["ID"];

			return false;
		}

		function query_all(){
			$query = "SELECT * FROM $this->table";
			$result = $this->conn->query($query);
			if (!$result){
				echo "Error: ".$query."<br>".$this->conn->error;
				return false;
			}
			return $result;
		}

		function check_url_count($link, $table=""){
			if ($table != "") $this->table = $table;
			$query = "SELECT * FROM $this->table WHERE URL LIKE '$link'";
			$result = $this->conn->query($query);
			if (!$result){
				echo "Error: ".$query."<br>".$this->conn->error;
				return false;
			}
			return $result->num_rows;
		}
	}
?>