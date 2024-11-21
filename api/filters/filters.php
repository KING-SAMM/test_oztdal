<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Community.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'CommunityRep.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Constituency.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'LocalGovernment.php';

header('Content-Type: application/json');

// Handle incoming request
$request = json_decode(file_get_contents('php://input'), true);

if (!$request || !isset($request['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$action = $request['action'];

$db = (new Database())->connect();
if($db === null) 
{
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Connection error...']);
    exit();
}

if ($action === 'fetch_all') {
    $communityRep = new CommunityRep($db);
    $data = $communityRep->getAllMembersWithDetails();
    echo json_encode(['status' => 'success', 'data' => $data]);
} elseif ($action === 'filter') {
    $filterType = $request['filterType'] ?? 'community';
    $order = $request['order'] ?? 'ASC';
    $orderBy = $request['orderBy'] ?? 'firstname';

    $communityRep = new CommunityRep($db);
    // $data = $communityRep->getFilteredData($filterType, $order, $orderBy);
    $data = $communityRep->getCommunityDetails($filterType, $order, $orderBy);
    if (!$data) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error...']);
        exit();
    }
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
