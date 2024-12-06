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
    $filterType = $request['filterType'] ?? 'communities';
    $orderBy = $request['orderBy'] ?? 'community_name';
    $order = $request['order'] ?? 'ASC';
    // No filterType selected? default to 'community'
    if (!$filterType || $filterType === 'communities') {
        $filterType = 'communities';  
        // Within community, if no orderBy is selected or is set to firstname 
        if (!$orderBy || $orderBy === 'firstname') 
        {
            $orderBy = 'community_name';  // Default to 'community_name'
        } 
        else 
        {
            $orderBy = $request['orderBy'];  // Else use what is selected
        }

        $community = new Community($db);
        $data = $community->getFilteredData($orderBy, $order);
        if (count($data) === 0) 
        {
            // If data ($result) is an empty array with zero elements
            http_response_code(200);
            echo json_encode(['status' => 'noresult', 'message' => 'No results found!']);
            exit();
        } elseif (!$data) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error...']);
            exit();
        }
        http_response_code(200);
        echo json_encode(['status' => 'success', 'filterType' => $filterType, 'data' => $data]);
    } elseif ($filterType === 'members') {
        // Within members, if no orderBy is selected or is set to community_name or community_eze 
        if ($orderBy && ($orderBy === 'community_name' || $orderBy === 'community_eze')) {
                $orderBy = 'firstname';  // Default to 'firstname'
        } else {
            $orderBy = $request['orderBy'];  // Else use what is selected
        }

        $communityRep = new CommunityRep($db);
        $data = $communityRep->getFilteredData($orderBy, $order);
        if (count($data) === 0) 
        {
            // If data ($result) is an empty array with zero elements
            http_response_code(200);
            echo json_encode(['status' => 'noresult', 'message' => 'No results found!']);
            exit();
        } elseif (!$data) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server error...']);
            exit();
        }
        http_response_code(200);
        echo json_encode(['status' => 'success', 'filterType' => $filterType, 'data' => $data]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
