<?php
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/models/Community.php';
require_once __DIR__ . '/../classes/models/CommunityRep.php';
require_once __DIR__ . '/../classes/models/Constituency.php';
require_once __DIR__ . '/../classes/models/LocalGovernment.php';

header('Content-Type: application/json');

// Get ID
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid community ID']);
    exit();
}

$db = (new Database())->connect();
$community = (new Community($db))->getById($id);
$members = (new CommunityRep($db))->getByCommunityId($id);
$constituency = (new Constituency($db))->getById($community['constituency_id']);
$localGovt = (new LocalGovernment($db))->getById($community['local_govt_id']);

$response = [
    'status' => 'success',
    'data' => [
        'community_name' => $community['community_name'],
        'eze_name' => $community['community_eze'],
        'chair_email' => $community['lg_comm_head_email'],
        'chair_phone' => $community['lg_comm_head_phone'],
        'secretary_phone' => $community['lg_comm_sec_phone'],
        'members' => $members,
        'local_govt' => $localGovt['name'],
        'constituency' => $constituency['name'],
        'created_at' => $community['created_at']
    ]
];

echo json_encode($response);
