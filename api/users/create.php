<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Group.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'GroupMember.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PdfHelper.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MailHandler.php';

// Set headers
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = (new Database())->connect();
    $group = new Group($db);
    $groupMember = new GroupMember($db);

    // Retrieve the group name and letter-headed file
    $groupName = $_POST['group_name'];
    $letterHeadFile = $_FILES['letter_head_file'];

    // Validate letter head PDF
    if ($letterHeadFile['type'] !== 'application/pdf') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid letterhead file format. Only PDF is allowed.']);
        exit;
    }

    // Create a group entry in the database
    $groupId = $group->createGroup($groupName, $letterHeadFile);
    if (!$groupId) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create group.']);
        exit;
    }

    // Loop through each member and add their details
    foreach ($_POST['members'] as $index => $member) {
        $name = $member['name'];
        $email = $member['email'];
        // $profilePic = $_FILES['members']['tmp_name'][$index]['profile_pic'];

        // Pass the entire file array instead of only tmp_name
        $profilePic = [
            'name' => $_FILES['members']['name'][$index]['profile_pic'],
            'type' => $_FILES['members']['type'][$index]['profile_pic'],
            'tmp_name' => $_FILES['members']['tmp_name'][$index]['profile_pic'],
            'error' => $_FILES['members']['error'][$index]['profile_pic'],
            'size' => $_FILES['members']['size'][$index]['profile_pic']
        ];
        $profilePicType = $_FILES['members']['type'][$index]['profile_pic'];

        // Validate and process the profile picture
        if (!in_array($profilePicType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
            echo json_encode(['status' => 'error', 'message' => "Invalid profile picture format for member $name."]);
            exit;
        }

        // Add each member's data to the database
        $memberId = $groupMember->addMember($groupId, $name, $email, $profilePic);
        if (!$memberId) {
            echo json_encode(['status' => 'error', 'message' => "Failed to add member $name to the group."]);
            exit;
        }
    }

    $letterHeadFilePath = PdfHelper::createLetterHeadedFile($groupId, $groupName, $letterHeadFile, $_POST['members'], $profilePic);

    if($groupId && $letterHeadFilePath) {
        // Store the letter Headed File path
        $group->storeLetterHeadFilePath($letterHeadFilePath, $groupId);
        
        // Send an email to the first member in the list as a sample (or use a specific email for notifications)
        // MailHandler::sendEmail($_POST['members'][0]['email'], $letterHeadPath);
        MailHandler::sendEmail("kcsamm11@gmail.com", $letterHeadFilePath);
    }

    echo json_encode(['status' => 'success', 'message' => 'Group and members added successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
