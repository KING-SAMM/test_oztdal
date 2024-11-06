<?php

class RegisterFormHandler {
    private $apiUrl = "http://testoztdal.local/api/communities/create.php";
    private $maxImageSize = 1.95 * 1024 * 1024; // 1.95 MB
    private $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

    public function processForm() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $communityName = $_POST['community_name'];
            $communityEze = $_POST['community_eze'];
            $localGovtId = $_POST['local_govt_id'];
            $constituencyId = $_POST['constituency_id'];
            $lgCommHeadEmail = $_POST['lg_comm_head_email'];
            $lgCommHeadPhone = $_POST['lg_comm_head_phone'];
            $lgCommSecPhone = $_POST['lg_comm_sec_phone'];
            $letterHead = $_FILES['letter_head_file'] ?? null;
            $reps = $_POST['reps'];

            // Validate the letterhead PDF
            if (!$letterHead || !$this->validatePDF($letterHead)) {
                echo "Letterhead must be a PDF file.";
                exit;
            }

            // Prepare the data for API request
            $data = [
                'community_name' => $communityName,
                'community_eze' => $communityEze,
                'local_govt_id' => $localGovtId,
                'constituency_id' => $constituencyId,
                'lg_comm_head_email' => $lgCommHeadEmail,
                'lg_comm_head_phone' => $lgCommHeadPhone,
                'lg_comm_sec_phone' => $lgCommSecPhone,
            ];

            $files = [
                'letter_head_file' => new CURLFile($letterHead['tmp_name'], 'application/pdf', $letterHead['name'])
            ];

            // Process each rep's data
            foreach ($reps as $index => $rep) {
                $firstname = $rep['firstname'];
                $lastname = $rep['lastname'];
                $phone = $rep['phone'];
                $gender = $rep['gender'];
                $profilePicArray = [
                    'name' => $_FILES['reps']['name'][$index]['profile_pic'],
                    'type' => $_FILES['reps']['type'][$index]['profile_pic'],
                    'tmp_name' => $_FILES['reps']['tmp_name'][$index]['profile_pic'],
                    'error' => $_FILES['reps']['error'][$index]['profile_pic'],
                    'size' => $_FILES['reps']['size'][$index]['profile_pic']
                ];
                

                // Validate and process each profile picture
                $profilePicPath = $this->validateAndProcessImage($profilePicArray, $index);
                if (!$profilePicPath) {
                    echo "Invalid profile picture for member $index.";
                    exit;
                }

                // Flatten rep data for curl
                $data["reps[$index][firstname]"] = $firstname;
                $data["reps[$index][lastname]"] = $lastname;
                $data["reps[$index][phone]"] = $phone;
                $data["reps[$index][gender]"] = $gender;
                $files["reps[$index][profile_pic]"] = new CURLFile($profilePicPath, mime_content_type($profilePicPath), basename($profilePicPath));
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

            $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '_' . $image['name'];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Community</title>
    <link rel="stylesheet" href="http://testoztdal.local/assets/css/main.css" />
    <script src="http://testoztdal.local/assets/js/retrieve.js" defer></script>
</head>
<body>
    <div class="overlay">
        <header>
            <h1>Register Your Community</h1>
        </header>
        <div class="main">
            <form id="registerForm" action="register.php" method="POST" enctype="multipart/form-data">
                <div class="form-title">OZTDAL Community Registration Form</div>
                <fieldset>
                    <legend>General Information</legend>
                    <label for="community_name">Name of Community</label>
                    <input type="text" id="community_name" name="community_name" class="main-fields" value="<?php echo htmlspecialchars($_POST['community_name'] ?? '', ENT_QUOTES); ?>" required><br>
                    <span class="error community_name_err"></span><br>
                    
                    <label for="community_eze">Name of Community's Government Recognized Eze:</label>
                    <input type="text" id="community_eze" name="community_eze" class="main-fields" value="<?php echo htmlspecialchars($_POST['community_eze'] ?? '', ENT_QUOTES); ?>" required><br>
                    <span class="error community_eze_err"></span><br>
                    
                    <label for="local_govt_id">Local Government Area (LGA):</label>
                    <!--<input type="text" id="local_govt_id" name="local_govt_id" class="main-fields" required><br>-->
                    <!--<span class="error local_govt_id_err"></span><br>-->
                    <select id="local_govt_id" name="local_govt_id" required>
                        <option value="" disabled selected>-- Select a Local Government --</option>
                    </select>
                    
                    <label for="constituency_id">Constituency:</label>
                    <!--<input type="text" id="constituency_id" name="constituency_id" class="main-fields" required><br>-->
                    <!--<span class="error constituency_id_err"></span><br>-->
                    <select id="constituency_id" name="constituency_id" required>
                        <option value="" disabled selected>-- Select a Constituency --</option>
                    </select>
                    
                    <label for="lg_comm_head_email">Email of Community Chairman/President in Lagos:</label>
                    <input type="email" id="lg_comm_head_email" name="lg_comm_head_email" class="main-fields" value="<?php echo htmlspecialchars($_POST['lg_comm_head_email'] ?? '', ENT_QUOTES); ?>" required><br>
                    <span class="error lg_comm_head_email_err"></span><br>
                    
                    <label for="lg_comm_head_phone">Phone No. of Community Chairman/President in Lagos:</label>
                    <input type="tel" id="lg_comm_head_phone" name="lg_comm_head_phone" placeholder="e.g: +2349044444444" value="<?php echo htmlspecialchars($_POST['lg_comm_head_phone'] ?? '', ENT_QUOTES); ?>" class="main-fields"  required><br>
                    <span class="error lg_comm_head_phone_err"></span><br>
                    
                    <label for="lg_comm_sec_phone">Phone No. of Community Secretary in Lagos:</label>
                    <input type="tel" id="lg_comm_sec_phone" name="lg_comm_sec_phone" placeholder="e.g: +2349044444444" class="main-fields" value="<?php echo htmlspecialchars($_POST['lg_comm_sec_phone'] ?? '', ENT_QUOTES); ?>" required><br>
                    <span class="error lg_comm_sec_phone_err"></span><br>
            
                    <label for="letter_head_file">Letter-headed PDF (A4 size):</label>
                    <input type="file" id="letter_head_file" name="letter_head_file" accept="application/pdf" required><br><br>
                </fieldset>
                
                <div class="reps-section">
                    <h3>
                        Please Provide 10 representatives of your community: 5 males, 5 females
                    </h3>
                </div>
        
                <!-- Loop to create 10 input fields for name, email, and profile picture -->
               <!-- original loop code was here, kept in loop.php -->

                <?php for ($i = 0; $i < 10; $i++): ?>
                    <?php
                    // Retrieve existing data from $_POST for each field
                    $firstname = isset($_POST['reps'][$i]['firstname']) ? htmlspecialchars($_POST['reps'][$i]['firstname']) : '';
                    $lastname = isset($_POST['reps'][$i]['lastname']) ? htmlspecialchars($_POST['reps'][$i]['lastname']) : '';
                    $phone = isset($_POST['reps'][$i]['phone']) ? htmlspecialchars($_POST['reps'][$i]['phone']) : '';
                    $gender = isset($_POST['reps'][$i]['gender']) ? $_POST['reps'][$i]['gender'] : '';

                    // Check if a file was uploaded previously and saved temporarily
                    $profilePicPath = isset($_SESSION['uploaded_files'][$i]) ? $_SESSION['uploaded_files'][$i] : '#';
                    ?>

                    <fieldset>
                        <legend>Representative <?php echo $i + 1; ?></legend>

                        <label>First Name:<br>
                            <input type="text" name="reps[<?php echo $i; ?>][firstname]" placeholder="First Name" id="firstname-<?php echo $i; ?>" value="<?php echo $firstname; ?>" required>
                        </label><br>
                        <span class="error firstname_err-<?php echo $i; ?>"></span><br>

                        <label>Last Name:<br>
                            <input type="text" name="reps[<?php echo $i; ?>][lastname]" placeholder="Last Name" id="lastname-<?php echo $i; ?>" value="<?php echo $lastname; ?>" required>
                        </label><br>
                        <span class="error lastname_err-<?php echo $i; ?>"></span><br>

                        <label>Phone Number:<br>
                            <input type="tel" name="reps[<?php echo $i; ?>][phone]" placeholder="Phone Number" id="phone-<?php echo $i; ?>" value="<?php echo $phone; ?>" required>
                        </label><br>
                        <span class="error phone_err-<?php echo $i; ?>"></span><br>

                        <div class="profile_pic">
                            <label>Profile Picture:
                                <input type="file" name="reps[<?php echo $i; ?>][profile_pic]" accept="image/jpeg, image/jpg, image/png, image/gif" onchange="previewImage(event, 'preview-<?php echo $i; ?>')" required>
                            </label>
                            <img id="preview-<?php echo $i; ?>" class="preview" src="<?php echo $profilePicPath; ?>" alt="Profile Picture Preview" style="<?php echo $profilePicPath !== '#' ? 'display: block;' : 'display: none;'; ?>">
                        </div>

                        <label>Gender:</label>
                        <input type="radio" name="reps[<?php echo $i; ?>][gender]" value="male" <?php echo $gender === 'male' ? 'checked' : ''; ?> required> Male
                        <input type="radio" name="reps[<?php echo $i; ?>][gender]" value="female" <?php echo $gender === 'female' ? 'checked' : ''; ?> required> Female
                    </fieldset>
                <?php endfor; ?>
        
                <button type="submit">Submit</button>
                <button type="button" onclick="resetForm()">Clear Form</button>
            </form>
        </div>
    </div>
    
    <script>
        // Function to preview image when a file is selected
        function previewImage(event, previewId) {
            const file = event.target.files[0];
            const preview = document.getElementById(previewId);
        
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
        
        // Function to reset the form and clear all image previews
        function resetForm() {
            // Reset form fields
            document.getElementById("registerForm").reset();
        
            // Hide and clear all image previews
            <?php for ($i = 0; $i < 10; $i++): ?>
                const preview<?php echo $i; ?> = document.getElementById("preview-<?php echo $i; ?>");
                preview<?php echo $i; ?>.src = "#";
                preview<?php echo $i; ?>.style.display = "none";
            <?php endfor; ?>
        }
    </script>
    
    <script src="http://testoztdal.local/assets/js/validate_registration.js" defer></script>
</body>
</html>
