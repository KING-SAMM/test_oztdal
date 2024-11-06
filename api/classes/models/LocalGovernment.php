<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Database.php';

class LocalGovernment {
    private $db;
    private $table = 'local_govts';

    public function __construct() {
        $this->db = new Database(); // Assumes Database is properly set up
    }

    public function readLocalGovts() {
        $query = "SELECT id, name FROM " . $this->table;
        $stmt = $this->db->connect()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns an associative array of results
    }
}
