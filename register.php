
// class RegisterFormHandler {
//     private $apiUrl = "https://community.oztdal.com.ng/api/communities/create.php";
//     private $maxImageSize = 1.95 * 1024 * 1024; // 1.95 MB
//     private $allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

//     public function processForm() {
//         if ($_SERVER["REQUEST_METHOD"] == "POST") {
//             $communityName = $_POST['community_name'];
//             $communityEze = $_POST['community_eze'];
//             $localGovtId = $_POST['local_govt_id'];
//             $constituencyId = $_POST['constituency_id'];
//             $lgCommHeadEmail = $_POST['lg_comm_head_email'];
//             $lgCommHeadPhone = $_POST['lg_comm_head_phone'];
//             $lgCommSecPhone = $_POST['lg_comm_sec_phone'];
//             $letterHead = $_FILES['letter_head_file'] ?? null;
//             $reps = $_POST['reps'];

//             // Validate the letterhead PDF
//             if (!$letterHead || !$this->validatePDF($letterHead)) {
//                 echo "Letterhead must be a PDF file.";
//                 exit;
//             }

//             // Prepare the data for API request
//             $data = [
//                 'community_name' => $communityName,
//                 'community_eze' => $communityEze,
//                 'local_govt_id' => $localGovtId,
//                 'constituency_id' => $constituencyId,
//                 'lg_comm_head_email' => $lgCommHeadEmail,
//                 'lg_comm_head_phone' => $lgCommHeadPhone,
//                 'lg_comm_sec_phone' => $lgCommSecPhone,
//             ];

//             $files = [
//                 'letter_head_file' => new CURLFile($letterHead['tmp_name'], 'application/pdf', $letterHead['name'])
//             ];

//             // Process each rep's data
//             foreach ($reps as $index => $rep) {
//                 $firstname = $rep['firstname'];
//                 $lastname = $rep['lastname'];
//                 $phone = $rep['phone'];
//                 $gender = $rep['gender'];
//                 $profilePicArray = [
//                     'name' => $_FILES['reps']['name'][$index]['profile_pic'],
//                     'type' => $_FILES['reps']['type'][$index]['profile_pic'],
//                     'tmp_name' => $_FILES['reps']['tmp_name'][$index]['profile_pic'],
//                     'error' => $_FILES['reps']['error'][$index]['profile_pic'],
//                     'size' => $_FILES['reps']['size'][$index]['profile_pic']
//                 ];
                

//                 // Validate and process each profile picture
//                 $profilePicPath = $this->validateAndProcessImage($profilePicArray, $index);
//                 if (!$profilePicPath) {
//                     echo "Invalid profile picture for member $index.";
//                     exit;
//                 }

//                 // Flatten rep data for curl
//                 $data["reps[$index][firstname]"] = $firstname;
//                 $data["reps[$index][lastname]"] = $lastname;
//                 $data["reps[$index][phone]"] = $phone;
//                 $data["reps[$index][gender]"] = $gender;
//                 $files["reps[$index][profile_pic]"] = new CURLFile($profilePicPath, mime_content_type($profilePicPath), basename($profilePicPath));
//             }

//             // Send data to API
//             $this->sendToApi($data, $files);
//         }
//     }

//     private function validateAndProcessImage($image, $index) {
//         if (!in_array($image['type'], $this->allowedImageTypes)) {
//             return false;
//         }
//         if ($image['size'] > $this->maxImageSize) {
//             return false;
//         }

//         list($width, $height) = getimagesize($image['tmp_name']);
//         if ($width > 160 || $height > 160) {
//             $newImage = imagecreatetruecolor(160, 160);
//             $srcImage = imagecreatefromstring(file_get_contents($image['tmp_name']));
//             imagecopyresampled($newImage, $srcImage, 0, 0, 0, 0, 160, 160, $width, $height);

//             $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '_' . $image['name'];
//             imagejpeg($newImage, $tempImagePath);
//             return $tempImagePath;
//         }

//         return $image['tmp_name'];
//     }

//     private function validatePDF($pdf) {
//         if ($pdf['error'] !== UPLOAD_ERR_OK) {
//             return false;
//         }
//         // Check using mime_content_type for better accuracy
//         $fileType = mime_content_type($pdf['tmp_name']);
//         return $fileType === 'application/pdf';
//     }

//     private function sendToApi($data, $files) {
//         $ch = curl_init($this->apiUrl);
//         curl_setopt($ch, CURLOPT_POST, 1);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data, $files));
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//         // Enable verbose output to debug
//         curl_setopt($ch, CURLOPT_VERBOSE, true);

//         // Execute the request and capture the response
//         $apiResponse = curl_exec($ch);
        
//         // Capture the HTTP status code
//         $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

//         // Check for errors
//         if (curl_errno($ch)) {
//             $errorMessage = curl_error($ch);
//             echo "cURL error: $errorMessage";
//         }

//         // $apiResponse = curl_exec($ch);
//         curl_close($ch);

//         // Decode the response
//         $response = json_decode($apiResponse, true);

//         // Display the API response and HTTP status code for debugging
//         if (!$response) {
//             echo "Error: Failed to decode JSON response. HTTP Status Code: $httpStatusCode";
//             echo "Response received: " . htmlspecialchars($apiResponse);
//         } elseif (isset($response['status']) && $response['status'] === 'success') {
//             echo "Form submitted successfully!";
//         } else {
//             $errorMessage = $response['message'] ?? 'Unknown error';
//             echo "Error submitting form: $errorMessage. HTTP Status Code: $httpStatusCode";
//         }

//         // if ($response && isset($response['status']) && $response['status'] === 'success') {
//         //     echo "Form submitted successfully!";
//         // } else {
//         //     echo "Error submitting form: " . ($response['message'] ?? 'Unknown error');
//         // }

//         // Clean up temporary profile picture files
//         foreach ($files as $file) {
//             if ($file instanceof CURLFile && file_exists($file->getFilename())) {
//                 @unlink($file->getFilename()); // Suppress errors if permission denied
//             }
//         }
//     }
// }

// // Instantiate and process the form
// $formHandler = new RegisterFormHandler();
// $formHandler->processForm();

