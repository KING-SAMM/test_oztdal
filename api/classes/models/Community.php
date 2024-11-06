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
        }
        return false;
    }

    public function storeLetterHeadFilePath($letterHeadFilePath, $communityId) {
        // Update database with image path
        $query = "UPDATE " . $this->table . " SET letterhead_file = :letterhead_file WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':letterhead_file', $letterHeadFilePath);
        $stmt->bindParam(':id', $communityId);
        $stmt->execute();
    }
}
