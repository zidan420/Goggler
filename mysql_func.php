<?php
	class MySql{
		private $conn;
		private $table;
	

		function __construct($servername, $username, $password, $dbname, $table){
			$this->conn = new mysqli($servername, $username, $password, $dbname);
			if ($this->conn->connect_error)  die("Connection failed: " . $this->conn->connect_error);
			$this->table = $table;
		}

		function insert_data($link, $content="", $tag=""){
			$query = "INSERT INTO $this->table (URL, CONTENT, TAG) VALUES ('$link', '$content', '$tag')";
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

		function query_all(){
			$query = "SELECT * FROM $this->table";
			$result = $this->conn->query($query);
			if (!$result){
				echo "Error: ".$query."<br>".$this->conn->error;
				return false;
			}
			return $result;
		}

		function check_url_count($link){
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
