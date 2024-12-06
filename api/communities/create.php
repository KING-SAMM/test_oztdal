<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Community.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'CommunityRep.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'PdfHelper.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'MailHandler.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = (new Database())->connect();
    if($db === null) 
    {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Connection error...']);
        exit();
    }
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
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid letterhead file format. Only PDF is allowed.']);
        exit;
    }

    // Create a community entry in the database
    $communityId = $community->createCommunity($communityName, $communityEze, $localGovtId, $constituencyId, $lgCommHeadEmail, $lgCommHeadPhone, $lgCommSecPhone, $letterHeadFile);
    if (!$communityId) {
        http_response_code(500);
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
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Invalid profile picture format for representative $firstname."]);
            exit;
        }

        // Add each rep's data to the database
        $repId = $communityRep->addRep($communityId, $gender, $firstname, $lastname, $phone, $profilePic);
        if (!$repId) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => "Failed to add representative $firstname to the community."]);
            exit;
        }
    }

    $letterHeadFilePath = PdfHelper::createLetterHeadedFile($communityId, $communityName, $communityEze, $lgCommHeadEmail, $lgCommHeadPhone, $lgCommSecPhone, $letterHeadFile, $_POST['reps'], $profilePic);

    if($communityId && $letterHeadFilePath) {
        try {
            // Store the letter Headed File path
            $community->storeLetterHeadFilePath($letterHeadFilePath, $communityId);
            
            // Send an email to the first representative in the list as a sample (or use a specific email for notifications)
            $result = MailHandler::sendEmail('kcsamm11@gmail.com', $communityName, $letterHeadFilePath);
            
            if(is_array($result))
            {
                if(array_key_exists('success', $result))
                {
                    http_response_code(201);
                    echo json_encode([
                        ["status" => "success", "message" => "Form details has been forwarded"],
                        ["status" => "success", "message" => "Community and representatives added successfully!"]
                    ]);
                }
                else
                {
                    http_response_code(500);
                    echo json_encode(['status' => 'error', 'message' => $result['error']]);
                }
            }
    
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

