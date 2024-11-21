<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Database.php';

class Constituency {
    private $db;
    private $table = 'constituencies';

    public function __construct() {
        $this->db = new Database(); // Assumes Database is properly set up
    }

    public function readConstituencies() {
        $query = "SELECT id, name FROM " . $this->table;
        $stmt = $this->db->connect()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns an associative array of results
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->db->connect()->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false; // Handle errors as needed
    }
}
