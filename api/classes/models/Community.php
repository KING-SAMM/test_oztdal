<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'PdfHelper.php';

class Community {
    private $conn;
    private $table = 'communities';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to create a community entry in the database
    public function createCommunity($communityName, $communityEze, $localGovtId, $constituencyId, $lgCommHeadEmail, $lgCommHeadPhone, $lgCommSecPhone, $letterHeadFil) {
        $query = "INSERT INTO " . $this->table . " (community_name, local_govt_id,	constituency_id, community_eze,	lg_comm_head_email,	lg_comm_head_phone,	lg_comm_sec_phone) VALUES (:community_name, :local_govt_id,	:constituency_id, :community_eze,	:lg_comm_head_email, :lg_comm_head_phone, :lg_comm_sec_phone)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':community_name', $communityName);
        $stmt->bindParam(':local_govt_id', $localGovtId);
        $stmt->bindParam(':constituency_id', $constituencyId);
        $stmt->bindParam(':community_eze', $communityEze);
        $stmt->bindParam(':lg_comm_head_email', $lgCommHeadEmail);
        $stmt->bindParam(':lg_comm_head_phone', $lgCommHeadPhone);
        $stmt->bindParam(':lg_comm_sec_phone', $lgCommSecPhone);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId(); // Return the created group ID
        } else {
            return false;
        }
    }

    public function storeLetterHeadFilePath($letterHeadFilePath, $communityId) {
        // Update database with image path
        $query = "UPDATE " . $this->table . " SET letterhead_file = :letterhead_file WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':letterhead_file', $letterHeadFilePath);
        $stmt->bindParam(':id', $communityId);
        $stmt->execute();
    }

    // Search Functionality
    public function searchCommunities($searchQuery) {
        $query = "
            SELECT * 
            FROM communities 
            WHERE community_name LIKE :searchQuery
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }    

    public function getById($id) {
        $query = "
            SELECT 
                * 
            FROM " 
                . $this->table . " 
            WHERE 
                id = :id LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false; // Handle errors as needed
    }

    public function getFilteredData($orderBy, $order) {
        // Whitelist of allowed column names and order directions
        $allowedColumns = ['community_name', 'community_eze', 'firstname', 'created_at'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'community_name'; // Default column
        }
        if (!in_array(strtoupper($order), $allowedOrder)) {
            $order = 'ASC'; // Default order
        }
        
        $query = "
            SELECT 
                c.id AS community_id,
                c.community_name AS name,
                c.community_eze AS eze,
                c.lg_comm_head_phone AS chair_phone,
                c.lg_comm_head_email AS chair_email,
                lg.id AS lga_id,
                lg.name AS local_govt_name
            FROM 
                communities c
            LEFT JOIN 
                local_govts lg 
            ON 
                lg.id = c.local_govt_id
            ORDER BY 
                c.{$orderBy} {$order}
                
        "; // Build the query based on filterType
        // Implement queries similar to previous examples based on $filterType
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if($stmt->execute()) 
        {
            // Process results
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
