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
}
