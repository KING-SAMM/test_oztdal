<?php

class GroupMember {
    private $conn;
    private $table = 'group_members';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to add a member to the database
    public function addMember($groupId, $name, $email, $profilePic) {
        $query = "INSERT INTO " . $this->table . " (group_id, name, email, profile_pic) VALUES (:group_id, :name, :email, :profile_pic)";
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

        $stmt->bindParam(':group_id', $groupId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':profile_pic', $profilePicPath);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId(); // Return the created member ID
        }
        return false;
    }
}
