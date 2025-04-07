<?php
class MySql
{
    private $conn;
    private $table;

    function __construct($servername, $username, $password, $dbname, $table)
    {
        $this->conn = new mysqli($servername, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        $this->table = $table;
    }

    function insert_data($data, $table = "")
    {
        if ($table != "") {
            $this->table = $table;
        }

        $columns = array_keys($data);
        $columns_string = implode(",", $columns);

        $values = array_map([$this->conn, "real_escape_string"], array_values($data));
        $values_string = "'" . implode("', '", $values) . "'";

        $query = "INSERT IGNORE INTO $this->table ($columns_string) VALUES ($values_string)";
        $result = $this->conn->query($query);

        if ($result) {
            return true;
        }
    }

    function update_data($data, $where, $table = "")
    {
        if ($table != "") {
            $this->table = $table;
        }

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

    function delete_all()
    {
        $this->conn->query("DELETE FROM $this->table");
    }

    function get_next_url($offset, $table)
    {
        $query = "SELECT url FROM $table LIMIT 1 OFFSET $offset";
        if ($row = $this->conn->query($query)->fetch_assoc()) {
            return $row["url"];
        }
    }

    function get_url_id($url, $table)
    {
        $query = "SELECT id FROM $table WHERE url='$url'";
        if ($row = $this->conn->query($query)->fetch_assoc()) {
            return $row["id"];
        }
    }

    function get_url($id, $table)
    {
        $query = "SELECT url FROM $table WHERE id='$id'";
        if ($row = $this->conn->query($query)->fetch_assoc()) {
            return $row["url"];
        }
    }

    function get_keyword_id($keyword, $table)
    {
        $keyword = $this->conn->real_escape_string($keyword);
        $query = "SELECT id FROM $table WHERE keyword='$keyword'";
        if ($row = $this->conn->query($query)->fetch_assoc()) {
            return $row["id"];
        }
    }

    function query_all()
    {
        $query = "SELECT * FROM $this->table";
        $result = $this->conn->query($query);
        return $result;
    }

    function query($text, $offset, $results_per_page)
    {
        // Split the keyword string into individual words
        $keywords = explode(" ", $text);
        $escaped_keywords = array_map([$this->conn, "real_escape_string"], $keywords);
        $keyword_placeholders = "'" . implode("','", $escaped_keywords) . "'";

        $query = "
        SELECT ui.url, ui.title, ui.description
        FROM urlInfo ui
        JOIN keyToUrl ktu ON ui.id = ktu.urlId
        JOIN keywordTable kt ON ktu.keywordId = kt.id
        WHERE kt.keyword IN ($keyword_placeholders)
        GROUP BY ui.id
        ORDER BY COUNT(ktu.keywordId) DESC
        LIMIT $offset, $results_per_page
    ";

        $result = $this->conn->query($query);
        return $result;
    }

    function query_count($text)
    {
        // Split the keyword string into individual words
        $keywords = explode(" ", $text);
        $escaped_keywords = array_map([$this->conn, "real_escape_string"], $keywords);
        $keyword_placeholders = "'" . implode("','", $escaped_keywords) . "'";

        $query = "
        SELECT count(*)
        FROM urlInfo ui
        JOIN keyToUrl ktu ON ui.id = ktu.urlId
        JOIN keywordTable kt ON ktu.keywordId = kt.id
        WHERE kt.keyword IN ($keyword_placeholders)
    ";
        $result = $this->conn->query($query);
        return $result;
    }

    function query_images($keyword, $offset, $results_per_page)
    {
        $query = "SELECT url, title, description FROM urlInfo WHERE url LIKE '%$keyword%.png' 
     OR url LIKE '%$keyword%.jpg' 
     OR url LIKE '%$keyword%.jpeg' 
     OR url LIKE '%$keyword%.webp' 
     OR url LIKE '%$keyword%.avif' LIMIT $offset, $results_per_page";
        $result = $this->conn->query($query);
        return $result;
    }

    function query_images_count($keyword)
    {
        $query = "SELECT count(*) FROM urlInfo WHERE url LIKE '%$keyword%.png' 
     OR url LIKE '%$keyword%.jpg' 
     OR url LIKE '%$keyword%.jpeg' 
     OR url LIKE '%$keyword%.webp' 
     OR url LIKE '%$keyword%.avif'";
        $result = $this->conn->query($query);
        return $result;
    }

    function query_hash($hash)
    {
        $query = "SELECT url, title, description FROM urlInfo WHERE hash='$hash'";
        $result = $this->conn->query($query);
        return $result;
    }

    function user_exist($username, $email)
    {
        $query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $result = $this->conn->query($query);
        return $result;
    }

    function email_exist($email)
    {
        $query = "SELECT id FROM users WHERE email = '$email'";
        $result = $this->conn->query($query);
        return $result;
    }

    function get_password_hash($username)
    {
        $query = "SELECT id, password_hash FROM users WHERE username = '$username'";
        $result = $this->conn->query($query);
        return $result;
    }

    function update_password_hash($password_hash, $email)
    {
        $query = "UPDATE users SET password_hash = '$password_hash' WHERE email = '$email'";
        $result = $this->conn->query($query);
        return $result;
    }

    function get_profile_icon($id)
    {
        $query = "SELECT profile_icon FROM users WHERE id = $id";
        $result = $this->conn->query($query);
        return $result;
    }

    function set_profile_icon($file, $id)
    {
        $query = "UPDATE users SET profile_icon = '$file' WHERE id = $id";
        $result = $this->conn->query($query);
        return $result;
    }

    function set_token($token, $expiry, $id)
    {
        $query = "UPDATE users SET reset_token = '$token', reset_expiry = $expiry WHERE id = $id";
        $result = $this->conn->query($query);
        return $result;
    }

    function get_reset_expiry($email, $token)
    {
        $query = "SELECT reset_expiry FROM users WHERE email = '$email' AND reset_token = '$token'";
        $result = $this->conn->query($query);
        return $result;
    }

    function get_search_history($user_id)
    {
        $query = "SELECT query, search_time FROM search_history WHERE user_id = $user_id ORDER BY search_time DESC";
        $result = $this->conn->query($query);
        return $result;
    }

    function is_web_master($user_id)
    {
        $query = "SELECT site_url FROM user_sites WHERE user_id = $user_id AND is_web_master = 1";
        $result = $this->conn->query($query);
        return $result;
    }

    function check_url_count($link, $table = "")
    {
        if ($table != "") {
            $this->table = $table;
        }
        $query = "SELECT * FROM $this->table WHERE URL LIKE '$link'";
        return $this->conn->query($query)->num_rows;
    }
}
?>
