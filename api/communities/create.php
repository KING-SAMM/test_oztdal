<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Community.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'CommunityRep.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PdfHelper.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MailHandler.php';

// Set headers
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = (new Database())->connect();
    $community = new Community($db);
    $communityRep = new CommunityRep($db);

    // Retrieve the community details and letter-headed file
    $communityName = $_POST['community_name'];
    $communityEze = $_POST['community_eze'];
    $localGovtId = $_POST['local_govt_id'];
    $constituencyId = $_POST['constituency_id'];
    $lgCommHeadEmail = $_POST['lg_comm_head_email'];
    $lgCommHeadPhone = $_POST['lg_comm_head_phone'];
    $lgCommSecPhone = $_POST['lg_comm_sec_phone'];
    $letterHeadFile = $_FILES['letter_head_file'];

    // Validate letter head PDF
    if ($letterHeadFile['type'] !== 'application/pdf') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid letterhead file format. Only PDF is allowed.']);
        exit;
    }

    // Create a community entry in the database
    $communityId = $community->createCommunity($communityName, $communityEze, $localGovtId, $constituencyId, $lgCommHeadEmail, $lgCommHeadPhone, $lgCommSecPhone, $letterHeadFile);
    if (!$communityId) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create community.']);
        exit;
    }

    // Loop through each representative and add their details
    foreach ($_POST['reps'] as $index => $rep) {
        $firstname = $rep['firstname'];
        $lastname = $rep['lastname'];
        $phone = $rep['phone'];
        $gender = $rep['gender'];
        // $profilePic = $_FILES['members']['tmp_name'][$index]['profile_pic'];

        // Pass the entire file array instead of only tmp_name
        $profilePic = [
            'name' => $_FILES['reps']['name'][$index]['profile_pic'],
            'type' => $_FILES['reps']['type'][$index]['profile_pic'],
            'tmp_name' => $_FILES['reps']['tmp_name'][$index]['profile_pic'],
            'error' => $_FILES['reps']['error'][$index]['profile_pic'],
            'size' => $_FILES['reps']['size'][$index]['profile_pic']
        ];
        $profilePicType = $_FILES['reps']['type'][$index]['profile_pic'];

        // Validate and process the profile picture
        if (!in_array($profilePicType, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
            echo json_encode(['status' => 'error', 'message' => "Invalid profile picture format for representative $firstname."]);
            exit;
        }

        // Add each rep's data to the database
        $repId = $communityRep->addRep($communityId, $gender, $firstname, $lastname, $phone, $profilePic);
        if (!$repId) {
            echo json_encode(['status' => 'error', 'message' => "Failed to add representative $firstname to the community."]);
            exit;
        }
    }

    $letterHeadFilePath = PdfHelper::createLetterHeadedFile($communityId, $communityName, $letterHeadFile, $_POST['reps'], $profilePic);

    if($communityId && $letterHeadFilePath) {
        try {
            // Store the letter Headed File path
            $community->storeLetterHeadFilePath($letterHeadFilePath, $communityId);
            
            // Send an email to the first representative in the list as a sample (or use a specific email for notifications)
            // MailHandler::sendEmail($_POST['reps'][0]['email'], $letterHeadPath);
            // MailHandler::sendEmail("kcsamm11@gmail.com", $letterHeadFilePath);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        
    }

    echo json_encode(['status' => 'success', 'message' => 'Community and representatives added successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
