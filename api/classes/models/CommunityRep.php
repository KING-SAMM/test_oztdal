<?php
class CommunityRep {
    private $conn;
    private $table = 'community_reps';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to add a representative to the database
    public function addRep($communityId, $gender, $firstname, $lastname, $phone, $profilePic) {
        $query = "INSERT INTO " . $this->table . " (community_id, gender, firstname, lastname, phone, profile_pic) VALUES (:community_id, :gender, :firstname, :lastname, :phone, :profile_pic)";
        $stmt = $this->conn->prepare($query);

        // Validate that $profilePic is an array with the required keys
        if (!is_array($profilePic) || !isset($profilePic['tmp_name']) || !isset($profilePic['name'])) {
            throw new InvalidArgumentException("Invalid profile picture format.");
        }

        // Handle profile picture upload
        $profilePicPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . basename($profilePic['name']);

        if (!move_uploaded_file($profilePic['tmp_name'], $profilePicPath)) {
            throw new RuntimeException("Failed to move profile picture.");
        }

        $stmt->bindParam(':community_id', $communityId);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':firstname', $firstname);
        $stmt->bindParam(':lastname', $lastname);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':profile_pic', $profilePicPath);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId(); // Return the created member ID
        }
        return false;
    }

    public function getAllMembersWithDetails() {
        $query = "SELECT cr.firstname, cr.lastname, c.community_name, lg.name AS local_govt, con.name AS constituency 
                  FROM community_reps cr 
                  JOIN communities c ON cr.community_id = c.id 
                  JOIN local_govts lg ON c.local_govt_id = lg.id 
                  JOIN constituencies con ON c.constituency_id = con.id";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredData($filterType, $order, $orderBy) {
        $query = ""; // Build the query based on filterType
        // Implement queries similar to previous examples based on $filterType
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommunityDetails($filterType, $order, $orderBy) {
        // Validate input for order and orderBy to prevent SQL injection
        $validOrder = ['ASC', 'DESC'];
        $validOrderBy = ['firstname', 'lastname', 'phone', 'gender'];
    
        // Default to ASC if $order is invalid
        if (!in_array(strtoupper($order), $validOrder)) {
            $order = 'ASC';
        }
    
        // Default to 'firstname' if $orderBy is invalid
        if (!in_array($orderBy, $validOrderBy)) {
            $orderBy = 'firstname';
        }
    
        // Construct the query
        $query = "
            SELECT 
                c.id AS community_id,
                c.community_name AS name,
                c.community_eze AS eze,
                c.lg_comm_head_phone AS chair_phone,
                c.lg_comm_sec_phone AS secretary_phone,
                c.lg_comm_head_email AS chair_email,
                m.id AS member_id,
                m.firstname,
                m.lastname,
                m.phone,
                m.gender
            FROM 
                communities c
            LEFT JOIN 
                community_reps m 
            ON 
                m.community_id = c.id
            ORDER BY 
                c.id, m.{$orderBy} {$order}
        ";
    
        // Prepare and execute the query
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute()) {
            return "Query error...";
        }
    
        // Process results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Group members by community
        $groupedResults = [];
        foreach ($results as $row) {
            $communityId = $row['community_id'];
    
            if (!isset($groupedResults[$communityId])) {
                $groupedResults[$communityId] = [
                    'community_name' => $row['name'],
                    'eze_name' => $row['eze'],
                    'chair_phone' => $row['chair_phone'],
                    'secretary_phone' => $row['secretary_phone'],
                    'chair_email' => $row['chair_email'],
                    'members' => []
                ];
            }
    
            if (!empty($row['member_id'])) {
                $groupedResults[$communityId]['members'][] = [
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname'],
                    'phone' => $row['phone'],
                    'gender' => $row['gender']
                ];
            }
        }
    
        return array_values($groupedResults);
    }
    
    // Search Functionality
    public function searchMembers($searchQuery) {
        $query = "
            SELECT * 
            FROM community_reps 
            WHERE firstname LIKE :searchQuery 
            OR lastname LIKE :searchQuery
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByCommunityId($communityId) {
        $query = "SELECT firstname, lastname, phone, gender 
                  FROM " . $this->table . " 
                  WHERE community_id = :community_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':community_id', $communityId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return false; // Handle errors as needed
    }
}

