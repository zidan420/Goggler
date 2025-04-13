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
        return false;
    }

    function delete_data($where, $table = "")
    {
        if ($table != "") {
            $this->table = $table;
        }

        $where_clause = [];
        foreach ($where as $col => $val) {
            $where_clause[] = "$col = '" . $this->conn->real_escape_string($val) . "'";
        }
        $where_string = implode(" AND ", $where_clause);

        $query = "DELETE FROM $this->table WHERE $where_string";
        $result = $this->conn->query($query);

        if ($result) {
            return true;
        }
        return false;
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

        if ($result) {
            return true;
        }
        return false;
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

    function calculate_idf()
    {
        // Total number of documents
        $total_docs = $this->conn->query("SELECT COUNT(*) FROM urlInfo")->fetch_row()[0];

        // Update IDF for each keyword
        $result = $this->conn->query("SELECT id, keyword FROM keywordTable");
        while ($row = $result->fetch_assoc()) {
            $keyword_id = $row["id"];
            // Number of documents containing this keyword
            $doc_count = $this->conn
                ->query("SELECT COUNT(*) FROM keyToUrl WHERE keywordId = $keyword_id")
                ->fetch_row()[0];
            // IDF calculation
            $idf = log(($total_docs - $doc_count + 0.5) / ($doc_count + 0.5) + 1);
            $this->conn->query("UPDATE keywordTable SET idf = $idf WHERE id = $keyword_id");
        }
    }

    /* returns Array( [doc_id] => score,  .. )*/
    function calculate_bm25($query_terms, $k1 = 1.5, $b = 0.75)
    {
        // Get average document length
        $avgdl_result = $this->conn->query("SELECT AVG(doc_length) FROM urlInfo")->fetch_row();
        $avgdl = $avgdl_result[0] ?? 1; // Avoid division by zero

        // Escape query terms
        $terms = array_map([$this->conn, "real_escape_string"], $query_terms);

        // Build query to get relevant documents
        $term_ids = [];
        foreach ($terms as $term) {
            $result = $this->conn->query("SELECT id, idf FROM keywordTable WHERE keyword = '$term'");
            if ($row = $result->fetch_assoc()) {
                $term_ids[$term] = ["id" => $row["id"], "idf" => $row["idf"]];
            }
        }

        // Calculate BM25 for each document
        $scores = [];
        $doc_result = $this->conn->query("SELECT id, doc_length FROM urlInfo");
        while ($doc = $doc_result->fetch_assoc()) {
            $doc_id = $doc["id"];
            $doc_length = $doc["doc_length"] ?? 1;
            $score = 0;

            foreach ($terms as $term) {
                if (!isset($term_ids[$term])) {
                    continue;
                } // Term not in index
                $keyword_id = $term_ids[$term]["id"];
                $idf = $term_ids[$term]["idf"];

                $freq_result = $this->conn->query(
                    "SELECT frequency FROM keyToUrl WHERE keywordId = $keyword_id AND urlId = $doc_id"
                );
                $tf = $freq_result->num_rows ? $freq_result->fetch_assoc()["frequency"] : 0;

                $numerator = $tf * ($k1 + 1);
                $denominator = $tf + $k1 * (1 - $b + $b * ($doc_length / $avgdl));
                $score += $idf * ($numerator / $denominator);
            }
            if ($score > 0) {
                $scores[$doc_id] = $score;
            }
        }
        return $scores;
    }

    function search($text, $offset, $results_per_page)
    {
        $terms = preg_split("/\s+/", trim($text));
        $scores = $this->calculate_bm25($terms);

        // Sort by score descending
        arsort($scores);

        // Total count of results (before pagination)
        $total_count = count($scores);

        $results = [];
        $doc_ids = array_keys($scores);

        // Apply pagination
        $paginated_ids = array_slice($doc_ids, $offset, $results_per_page);

        // Fetch details for paginated document IDs
        foreach ($paginated_ids as $doc_id) {
            $doc_id = $this->conn->real_escape_string($doc_id);
            $result = $this->conn->query("SELECT url, title, description FROM urlInfo WHERE id = '$doc_id'");
            /* if the result is not empty, insert result */
            if ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        }

        return [$results, $total_count];
    }

    function advanced_search($and_query, $or_query, $nor_query, $offset, $results_per_page)
    {
        // Initialize scores arrays
        $and_scores = [];
        $or_scores = [];
        $nor_scores = [];
        $final_scores = [];

        /* Process AND query (all terms must appear) */
        if (!empty($and_query)) {
            $and_terms = preg_split("/\s+/", trim($and_query));
            // Calculate BM25 for each term individually to ensure all are present
            $term_scores = [];
            foreach ($and_terms as $term) {
                $term_scores[$term] = $this->calculate_bm25([$term]);
            }

            // Intersect results: keep documents with all AND terms
            $doc_ids = null;
            foreach ($term_scores as $term => $scores) {
                /* current doc ids */
                $current_ids = array_keys($scores);
                if ($doc_ids === null) {
                    $doc_ids = $current_ids;
                } else {
                    $doc_ids = array_intersect($doc_ids, $current_ids);
                }
            }

            // Compute combined BM25 score for AND documents
            foreach ($doc_ids as $doc_id) {
                $score = 0;
                foreach ($term_scores as $scores) {
                    if (isset($scores[$doc_id])) {
                        $score += $scores[$doc_id];
                    }
                }
                if ($score > 0) {
                    $and_scores[$doc_id] = $score;
                }
            }
            $final_scores = $and_scores;
        }

        // Process OR query (at least one term must appear)
        if (!empty($or_query)) {
            $or_terms = preg_split("/\s+/", trim($or_query));
            $or_scores = $this->calculate_bm25($or_terms);
            if (empty($final_scores)) {
                // If no AND query, use OR scores directly
                $final_scores = $or_scores;
            } else {
                // Boost AND-matching documents with OR scores
                foreach ($or_scores as $doc_id => $score) {
                    if (isset($final_scores[$doc_id])) {
                        $final_scores[$doc_id] += $score * 0.3; // Lower weight for OR
                    }
                    // Don't include OR-only documents if AND is specified
                }
            }
        }

        // Process NOR query (exclude documents with these terms)
        if (!empty($nor_query)) {
            $nor_terms = preg_split("/\s+/", trim($nor_query));
            $nor_scores = $this->calculate_bm25($nor_terms);
            // Exclude documents matching NOR terms
            foreach ($nor_scores as $doc_id => $score) {
                unset($final_scores[$doc_id]);
            }
        }

        // Handle empty results
        if (empty($final_scores) && (!empty($and_query) || !empty($or_query))) {
            return [[], 0];
        }

        // Sort by score descending
        arsort($final_scores);

        // Total count of results
        $total_count = count($final_scores);

        $results = [];
        $doc_ids = array_keys($final_scores);

        // Apply pagination
        $paginated_ids = array_slice($doc_ids, $offset, $results_per_page);

        // Fetch details for paginated document IDs
        foreach ($paginated_ids as $doc_id) {
            $doc_id = $this->conn->real_escape_string($doc_id);
            $result = $this->conn->query("SELECT url, title, description FROM urlInfo WHERE id = '$doc_id'");
            if ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
        }

        return [$results, $total_count];
    }
}
?>
