<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/models/CommunityRep.php';

// require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/models/Community.php';
// require_once __DIR__ . '/../classes/models/CommunityRep.php';
// require_once __DIR__ . '/../classes/models/Constituency.php';
// require_once __DIR__ . '/../classes/models/LocalGovernment.php';

header('Content-Type: application/json');

// Get ID
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid member ID']);
    exit();
}

$db = (new Database())->connect();
$member = (new CommunityRep($db))->getById($id);
if (!$member) {
    echo json_encode(['status' => 'error', 'message' => 'Member not found']);
    exit();
}
$community = (new Community($db))->getById($member['community_id']);


if (!$community) {
    echo json_encode(['status' => 'error', 'message' => 'Community not found']);
    exit();
}

$responseData = [
    'status' => 'success', 
    'data' => [
        'firstname'     => $member['firstname'], 
        'lastname'      => $member['lastname'], 
        'phone'         => $member['phone'], 
        'gender'        => $member['gender'],
        'community_name'=> $community['community_name'],
        'community_eze' => $community['community_eze']
    ]
];

echo json_encode($responseData);
