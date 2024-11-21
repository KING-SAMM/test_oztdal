<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Database.php';

class Authentication {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function registerUser($username, $hashedPassword) {
        $stmt = $this->conn->prepare("SELECT id FROM auth_user WHERE username = :username");
        $stmt->execute([':username' => $username]);

        if ($stmt->rowCount() > 0) {
            return ['status' => 'error', 'message' => 'User already exists'];
        }

        $stmt = $this->conn->prepare("INSERT INTO auth_user (username, password) VALUES (:username, :password)");
        $stmt->execute([':username' => $username, ':password' => $hashedPassword]);

        return ['status' => 'success', 'message' => 'User registered successfully'];
    }

    public function loginUser($username, $password) {
        $stmt = $this->conn->prepare("SELECT password FROM auth_user WHERE username = :username");
        $stmt->execute([':username' => $username]);

        if ($stmt->rowCount() == 0) {
            return ['status' => 'error', 'message' => 'Invalid username or password'];
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            return ['status' => 'success', 'message' => 'Login successful'];
        }

        return ['status' => 'error', 'message' => 'Invalid username or password'];
    }
}
?>
