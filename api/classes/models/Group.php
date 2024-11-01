<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'PdfHelper.php';

class Group {
    private $conn;
    private $table = 'groups';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to create a group entry in the database
    public function createGroup($groupName) {
        $query = "INSERT INTO " . $this->table . " (group_name) VALUES (:group_name)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':group_name', $groupName);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId(); // Return the created group ID
        }
        return false;
    }

    public function storeLetterHeadFilePath($letterHeadFilePath, $groupId) {
        // Update database with image path
        $query = "UPDATE " . $this->table . " SET letterhead_file = :letterhead_file WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':letterhead_file', $letterHeadFilePath);
        $stmt->bindParam(':id', $groupId);
        $stmt->execute();
    }
}
