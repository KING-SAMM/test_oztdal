<?php
session_start();

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'Validate.php';
class RegisterFormHandler {
    private $apiUrl = "http://testoztdal.local/api/communities/create.php";
    private $maxImageSize = 1.95 * 1024 * 1024; // 1.95 MB
    private $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    private $tempDir = 'uploads/tmp/';
    private $requiredNamePattern = '/^[a-zA-Z\s]+$/'; // '/^[a-zA-Z]+( [a-zA-Z_]+)*$/'; 
    private $requiredPhonePattern = "/^\+[1-9]\d{0,3}[1-9]\d{6,14}$/"; // '/^\+[0-9]{8,}$/';
    private $nameFieldErrorMsg = 'Only letters and white spaces are allowed';
    private $phoneFieldErrorMsg = 'Invalid phone number format';
    private $emailFieldErrorMsg = 'Invalid email format';
    // Reps


    public function __construct() {
        // Ensure the temp directory exists
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    public function processForm() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            $validate = new Validate();

            $_SESSION['invalid_fields'] = [];

            $communityName = $validate->field($_POST['community_name'], 'Community name', $this->requiredNamePattern, $this->nameFieldErrorMsg, $_SESSION['invalid_fields']);
            $communityEze = $validate->field($_POST['community_eze'], 'Community eze', $this->requiredNamePattern, $this->nameFieldErrorMsg, $_SESSION['invalid_fields']);
            $localGovtId = $_POST['local_govt_id'];
            $constituencyId = $_POST['constituency_id'];
            $lgCommHeadEmail = $validate->email($_POST['lg_comm_head_email'], 'Chairman/President email', $this->emailFieldErrorMsg);
            $lgCommHeadPhone = $validate->field($_POST['lg_comm_head_phone'], 'Chairman/President Phone', $this->requiredPhonePattern, $this->phoneFieldErrorMsg, $_SESSION['invalid_fields']);
            $lgCommSecPhone = $validate->field($_POST['lg_comm_sec_phone'], 'Secretary Phone', $this->requiredPhonePattern, $this->phoneFieldErrorMsg, $_SESSION['invalid_fields']);
            $letterHead = $_FILES['letter_head_file'] ?? null;
            $reps = $_POST['reps'];
       
            // Validate and save the letterhead PDF
            if (!$letterHead || !$this->validatePDF($letterHead)) 
            {
                $_SESSION['invalid_letterhead'] = "Letterhead must be a PDF file.";
                // exit;
            } 
            else 
            {
                $letterHeadTempPath = $this->saveTempFile($letterHead);
                $_SESSION['uploaded_letter_head'] = $letterHeadTempPath;
            }

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
                'letter_head_file' => new CURLFile($_SESSION['uploaded_letter_head'], 'application/pdf', $letterHead['name'])
            ];

            // $_SESSION['invalid_rep_field'] = [];
            // Process each rep's data
            foreach ($reps as $index => $rep) 
            {
                $firstname = $validate->repField($rep['firstname'], 'Representative ' .($index + 1).' firstname', $this->requiredNamePattern, $this->nameFieldErrorMsg, $_SESSION['invalid_fields']);
                $lastname = $validate->repField($rep['lastname'], 'Representative ' .($index + 1).' lastname', $this->requiredNamePattern, $this->nameFieldErrorMsg, $_SESSION['invalid_fields']);
                $phone = $validate->repField($rep['phone'], 'Representative ' .($index + 1).' phone', $this->requiredPhonePattern, $this->phoneFieldErrorMsg, $_SESSION['invalid_fields']);
                $gender = $rep['gender'];

                $profilePicArray = [
                    'name' => $_FILES['reps']['name'][$index]['profile_pic'],
                    'type' => $_FILES['reps']['type'][$index]['profile_pic'],
                    'tmp_name' => $_FILES['reps']['tmp_name'][$index]['profile_pic'],
                    'error' => $_FILES['reps']['error'][$index]['profile_pic'],
                    'size' => $_FILES['reps']['size'][$index]['profile_pic']
                ];

                $profilePicPath = $this->validateAndProcessImage($profilePicArray, $index);
                
                
                if (!$profilePicPath) 
                {
                    $_SESSION['invalid_image'] = "Invalid profile picture for representative " . ($index + 1);
                    exit;
                    // return false;
                }
                if ($profilePicPath == "Failed to upload passport photo for representative " . ($index + 1)) 
                {
                    $_SESSION['invalid_image'] = "Failed to upload passport photo for representative " . ($index + 1);
                    // exit;
                } 
                elseif ($profilePicPath == "Passport photo exceeds 1.95MB. Please upload a smaller file representative " . ($index + 1)) 
                {
                    $_SESSION['invalid_image'] = "Passport photo exceeds 1.95MB. Please upload a smaller file for representative " . ($index + 1);
                    // exit;
                    // return $_SESSION['invalid_image'];
                } 
                elseif ($profilePicPath == "Passport photo is not of the allowed formats for representative " . ($index + 1)) 
                {
                    $_SESSION['invalid_image'] = "Passport photo is not of the allowed formats for representative " . ($index + 1);
                    // return false;
                    // exit;
                } 
                else 
                {
                    $_SESSION['uploaded_files'][$index] = $profilePicPath;

    
                    $data["reps[$index][firstname]"] = $firstname;
                    $data["reps[$index][lastname]"] = $lastname;
                    $data["reps[$index][phone]"] = $phone;
                    $data["reps[$index][gender]"] = $gender;
                    $files["reps[$index][profile_pic]"] = new CURLFile($profilePicPath, mime_content_type($profilePicPath), basename($profilePicPath));
                }
            }

            // print_r($_SESSION['invalid_rep_field']);
            // var_dump($_SESSION['invalid_rep_field']);
            //     // exit;

            // Send to API only if no errors
            if (!isset($_SESSION['invalid_image']) && 
                !isset($_SESSION['invalid_letterhead']) &&
                !isset($_SESSION['invalid_email']) &&
                ($_SESSION['invalid_fields'] == null || !$_SESSION['invalid_fields'])) 
            {
                $this->sendToApi($data, $files);
                $this->clearFormAndTemporaryFiles();
                $_SESSION['form_submitted'] = true;

                // Check if the form was successfully submitted
                if (isset($_SESSION['form_submitted']) && $_SESSION['form_submitted'] === true) {
                    // Clear the form submission flag
                    unset($_SESSION['form_submitted']);

                    // Clear any retained POST data
                    $_POST = [];
                }
            }
        }
    }

    private function validateAndProcessImage($image, $index) 
    {
        if (!in_array($image['type'], $this->allowedImageTypes)) 
        {
            // $_SESSION['invalid_image'] = "Passport photo is not of the allowed formats for representative " . $index;
            return "Passport photo is not of the allowed formats for representative " . ($index + 1);
        }
        if ($image['size'] > $this->maxImageSize) 
        { 
            // $_SESSION['invalid_image'] = "Passport photo exceeds 1.95MB. Please upload a smaller file representative " . $index;
            return "Passport photo exceeds 1.95MB. Please upload a smaller file representative " . ($index + 1);
        }
        if (isset($image['error']) && $image['error'] !== UPLOAD_ERR_OK) 
        {
            // $_SESSION['invalid_image'] = "Failed to upload passport photo for representative " . $index;
            return "Failed to upload passport photo for representative " . ($index + 1);
        }       
         

        list($width, $height) = getimagesize($image['tmp_name']);
        if ($width > 160 || $height > 160) 
        {
            $newImage = imagecreatetruecolor(160, 160);
            $srcImage = imagecreatefromstring(file_get_contents($image['tmp_name']));
            imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, 160, 160, $width, $height);

            $tempImagePath = $this->tempDir . uniqid() . '_' . $image['name'];
            imagejpeg($newImage, $tempImagePath);
            return $tempImagePath;
        }

        return $this->saveTempFile($image);
    }

    private function saveTempFile($file) 
    {
        $tempFilePath = $this->tempDir . uniqid() . '_' . basename($file['name']);
        move_uploaded_file($file['tmp_name'], $tempFilePath);
        return $tempFilePath;
    }

    private function validatePDF($pdf) 
    {
        return $pdf['error'] === UPLOAD_ERR_OK && mime_content_type($pdf['tmp_name']) === 'application/pdf';
    }
    
    private function sendToApi($data, $files) 
    {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data, $files));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);        // Ensure headers aren’t included in $apiResponse
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    
        $apiResponse = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $requestInfo = curl_getinfo($ch);
        
        $decodedResponses = json_decode($apiResponse, true);

        $_SESSION['decodedResponses'] = $decodedResponses;
        $_SESSION['httpStatusCode'] = $httpStatusCode;
        
        
        if (curl_errno($ch)) 
        {
            echo "cURL error: " . curl_error($ch);
        }
    
        curl_close($ch);
    }

    private function clearFormAndTemporaryFiles() {
        // Remove uploaded temp files
        array_map('unlink', glob($this->tempDir . '*'));

        // Clear session data
        unset($_SESSION['form_data'], $_SESSION['uploaded_letter_head'], $_SESSION['uploaded_files'], $_SESSION['invalid_fields'], $_SESSION['invalid_letterhead'], $_SESSION['invalid_image']);
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
    <script src="http://testoztdal.local/assets/js/notification.js" defer></script>
</head>
<body>
      <?php
         
        if (isset($_SESSION['invalid_image']) && $_SESSION['invalid_image'] != null): ?>
            <div id="warningNotification" class="warning-notification validate">
                <?= htmlspecialchars($_SESSION['invalid_image'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (isset($_SESSION['invalid_letterhead']) && $_SESSION['invalid_letterhead'] != null): ?>
            <div id="warningNotification" class="warning-notification validate">
                <?= htmlspecialchars($_SESSION['invalid_letterhead'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (isset($_SESSION['invalid_email']) && $_SESSION['invalid_email'] != null): ?>
            <div id="warningNotification" class="warning-notification validate">
                <?= htmlspecialchars($_SESSION['invalid_letterhead'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        
        <?php endif; ?>
        <?php 
          $invalidFields = $_SESSION['invalid_fields'] ?? []; // Ensure variable exists and is an array
            foreach ($invalidFields as $invalidFieldMsg): ?>
                <div id="warningNotification" class="warning-notification validate">
                    <?= htmlspecialchars($invalidFieldMsg, ENT_QUOTES, 'UTF-8'); ?>
                </div>
        <?php endforeach;?>
       
        <?php
         $decodedResponses = $_SESSION['decodedResponses'] ?? []; // Ensure variable exists and is an array
         foreach ($decodedResponses as $response): ?>
             <?php if ($response['status'] == 'success'): ?>
                 <div id="flashNotification" class="flash-notification success" style="color: white;">
                     <?php echo htmlspecialchars($response['message'], ENT_QUOTES, 'UTF-8'); ?>
                 </div>
             <?php elseif ($response['status'] == 'error'): ?>
                 <div id="flashNotification" class="flash-notification error" style="color: white;">
                     <?php echo htmlspecialchars($response['message'], ENT_QUOTES, 'UTF-8'); ?>
                 </div>
             <?php endif; ?>
         <?php endforeach; ?>

        <?php
            // Clear the session warning data after rendering
            unset($_SESSION['invalid_image'], $_SESSION['invalid_letterhead'], $_SESSION['invalid_fields'], $_SESSION['invalid_email'], $_SESSION['decodedResponses']);  
        ?>
    
    <div id="loader" class="loader"></div>
    <!--<div id="warningNotification" class="warning-notification">
       
        // htmlspecialchars($message) 
        
    </div>-->
    
   
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
                    <select id="local_govt_id" name="local_govt_id" required>
                        <option value="" disabled selected>-- Select a Local Government --</option>
                    </select>
                    
                    <label for="constituency_id">Constituency:</label>
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
                    <h3>Include a passport photo of each representative</h3>
                    <h3>Accepted Formats: JPEG, JPG, PNG, GIF</h3>
                    <h3>Maximum File Size: 1.95 MB</h3>
                </div>
        
                <!-- Loop to create 10 input fields for name, email, and profile picture -->
                
                
                <?php for ($i = 0; $i < 2; $i++): ?>
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
                                <label>Passport Photo:
                                    <input type="file" name="reps[<?php echo $i; ?>][profile_pic]" accept="image/jpeg, image/jpg, image/png, image/gif" onchange="previewImage(event, 'preview-<?php echo $i; ?>')" required>
                                </label>
                                <!-- <img id="preview-<?php echo $i; ?>" class="preview" src="<?php echo $profilePicPath; ?>" alt="Profile Picture Preview" style="<?php echo $profilePicPath !== '#' ? 'display: block;' : 'display: none;'; ?>"> -->
                                 
                                 <img id="preview-<?php echo $i; ?>" class="preview" src="<?php echo $_SESSION['uploaded_files'][$i] ?? '#'; ?>" alt="Profile Picture Preview" style="<?php echo $profilePicPath !== '#' ? 'display: block;' : 'display: none;'; ?>"> 
                               
    
    
                                <!--<img id="preview-<?php echo $i; ?>" class="preview" src="<?php echo $profilePicPath; ?>" alt="Profile Picture Preview" style="<?php echo $profilePicPath !== '#' ? 'display: block;' : 'display: none;'; ?>">-->
                            </div>
    
                            <label>Gender:</label>
                            <input type="radio" name="reps[<?php echo $i; ?>][gender]" value="male" <?php echo $gender === 'male' ? 'checked' : ''; ?> required> Male
                            <input type="radio" name="reps[<?php echo $i; ?>][gender]" value="female" <?php echo $gender === 'female' ? 'checked' : ''; ?> required> Female
                        </fieldset>
                    <?php endfor; ?>
        
        
                <button type="submit" id="submit">Submit</button>
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
            <?php for ($i = 0; $i < 2; $i++): ?>
                const preview<?php echo $i; ?> = document.getElementById("preview-<?php echo $i; ?>");
                preview<?php echo $i; ?>.src = "#";
                preview<?php echo $i; ?>.style.display = "none";
            <?php endfor; ?>
        }
    </script>
    
    <script src="http://testoztdal.local/assets/js/validate_registration.js" defer></script>
    
    <script src="http://testoztdal.local/assets/js/helper.js" defer></script>    
   

</body>
</html>