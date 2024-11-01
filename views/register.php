<?php

class RegisterFormHandler {
    private $apiUrl = "http://testoztdal.local/api/users/create.php";
    private $maxImageSize = 1.95 * 1024 * 1024; // 1.95 MB
    private $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    public function processForm() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $groupName = $_POST['group_name'];
            $letterHead = $_FILES['letter_head_file'] ?? null;
            $members = $_POST['members'];

            // Validate the letterhead PDF
            if (!$letterHead || !$this->validatePDF($letterHead)) {
                echo "Letterhead must be a PDF file.";
                exit;
            }

            // Prepare the data for API request
            $data = [
                'group_name' => $groupName
            ];

            $files = [
                'letter_head_file' => new CURLFile($letterHead['tmp_name'], 'application/pdf', $letterHead['name'])
            ];

            // Process each member's data
            foreach ($members as $index => $member) {
                $name = $member['name'];
                $email = $member['email'];
                $profilePicArray = [
                    'name' => $_FILES['members']['name'][$index]['profile_pic'],
                    'type' => $_FILES['members']['type'][$index]['profile_pic'],
                    'tmp_name' => $_FILES['members']['tmp_name'][$index]['profile_pic'],
                    'error' => $_FILES['members']['error'][$index]['profile_pic'],
                    'size' => $_FILES['members']['size'][$index]['profile_pic']
                ];

                // Validate and process each profile picture
                $profilePicPath = $this->validateAndProcessImage($profilePicArray, $index);
                if (!$profilePicPath) {
                    echo "Invalid profile picture for member $index.";
                    exit;
                }

                // Flatten member data for curl
                $data["members[$index][name]"] = $name;
                $data["members[$index][email]"] = $email;
                $files["members[$index][profile_pic]"] = new CURLFile($profilePicPath, mime_content_type($profilePicPath), basename($profilePicPath));
            }

            // Send data to API
            $this->sendToApi($data, $files);
        }
    }

    private function validateAndProcessImage($image, $index) {
        if (!in_array($image['type'], $this->allowedImageTypes)) {
            return false;
        }
        if ($image['size'] > $this->maxImageSize) {
            return false;
        }

        list($width, $height) = getimagesize($image['tmp_name']);
        if ($width > 160 || $height > 160) {
            $newImage = imagecreatetruecolor(160, 160);
            $srcImage = imagecreatefromstring(file_get_contents($image['tmp_name']));
            imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, 160, 160, $width, $height);

            $tempImagePath = sys_get_temp_dir() . '/' . uniqid() . '_' . $image['name'];
            imagejpeg($newImage, $tempImagePath);
            return $tempImagePath;
        }

        return $image['tmp_name'];
    }

    private function validatePDF($pdf) {
        if ($pdf['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        // Check using mime_content_type for better accuracy
        $fileType = mime_content_type($pdf['tmp_name']);
        return $fileType === 'application/pdf';
    }

    private function sendToApi($data, $files) {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data, $files));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Enable verbose output to debug
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        // Execute the request and capture the response
        $apiResponse = curl_exec($ch);
        
        // Capture the HTTP status code
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for errors
        if (curl_errno($ch)) {
            $errorMessage = curl_error($ch);
            echo "cURL error: $errorMessage";
        }

        // $apiResponse = curl_exec($ch);
        curl_close($ch);

        // Decode the response
        $response = json_decode($apiResponse, true);

        // Display the API response and HTTP status code for debugging
        if (!$response) {
            echo "Error: Failed to decode JSON response. HTTP Status Code: $httpStatusCode";
            echo "Response received: " . htmlspecialchars($apiResponse);
        } elseif (isset($response['status']) && $response['status'] === 'success') {
            echo "Form submitted successfully!";
        } else {
            $errorMessage = $response['message'] ?? 'Unknown error';
            echo "Error submitting form: $errorMessage. HTTP Status Code: $httpStatusCode";
        }

        // if ($response && isset($response['status']) && $response['status'] === 'success') {
        //     echo "Form submitted successfully!";
        // } else {
        //     echo "Error submitting form: " . ($response['message'] ?? 'Unknown error');
        // }

        // Clean up temporary profile picture files
        foreach ($files as $file) {
            if ($file instanceof CURLFile && file_exists($file->getFilename())) {
                @unlink($file->getFilename()); // Suppress errors if permission denied
            }
        }
    }
}

// Instantiate and process the form
$formHandler = new RegisterFormHandler();
$formHandler->processForm();
?>


<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Group</title>
</head>
<body>
    <h2>Group Registration Form</h2>
    <form action="register.php" method="POST" enctype="multipart/form-data">
        <label for="group_name">Group Name:</label>
        <input type="text" id="group_name" name="group_name" required><br>

        <label for="letter_head_file">Letter-headed PDF (A4 size):</label>
        <input type="file" id="letter_head_file" name="letter_head_file" accept="application/pdf" required><br><br>

        <!-- Loop to create 10 input fields for name, email, and profile picture -->
        <?php for ($i = 0; $i < 10; $i++): ?>
            <fieldset>
                <legend>Member <?php echo $i + 1; ?></legend>
                <label>Name: <input type="text" name="members[<?php echo $i; ?>][name]" required></label><br>
                <label>Email: <input type="email" name="members[<?php echo $i; ?>][email]" required></label><br>
                <label>Profile Picture: <input type="file" name="members[<?php echo $i; ?>][profile_pic]" accept="image/jpeg, image/jpg, image/png, image/gif" required></label>
            </fieldset>
        <?php endfor; ?>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
