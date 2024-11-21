<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Authentication.php';

session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$username = htmlspecialchars($data['username']);
$password = htmlspecialchars($data['password']);

if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

$auth = new Authentication();
$response = $auth->loginUser($username, $password);

if ($response['status'] === 'success') {
    $_SESSION['user'] = $username;
}

echo json_encode($response);
?>
