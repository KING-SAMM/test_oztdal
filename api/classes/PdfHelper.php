<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfHelper {
    public static function createLetterHeadedFile($communityId, $communityName, $communityEze, $lgCommHeadEmail, $lgCommHeadPhone, $lgCommSecPhone, $letterHeadFile, $reps, $profilePic) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        $letterHeadFilePath = $uploadDir . 'letterhead_community_' . $communityId . '.pdf';

        // Copy the uploaded letterhead template to the destination
        move_uploaded_file($letterHeadFile['tmp_name'], $letterHeadFilePath);  // JUST ADDED

        // Passport photos directory
        $image_dir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
        
        // Format the date
        $date = new DateTime();
        $formattedDate = $date->format('jS F, Y');

        // Initialize TcpdfFpdi
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        // Import the existing letterhead PDF page
        $pageCount = $pdf->setSourceFile($letterHeadFilePath);  // was letterHeadPath
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210, 297); // A4 dimensions in mm

        // Set the cursor position to leave space for the letterhead logo
        $pdf->SetY(28); // Adjust as needed

        // Set font for the community and reps details
        // Set font for date
        $pdf->SetFont('helvetica', 'B', 10);
        // Add the community name at the top
        $pdf->Cell(0, 8, "$formattedDate", 0, 1, 'R');
        // Font for the community name
        $pdf->SetFont('helvetica', 'B', 16);
        // Add the community name at the top
        $pdf->Cell(0, 8, "$communityName Community (Lagos)", 0, 1, 'C');

        // Set font for the community Eze
        $pdf->SetFont('helvetica', '', 13);
        // Add the community Eze
        $pdf->Cell(0, 6, "Eze: $communityEze", 0, 1, 'C');

        // Set font for the community details
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(0, 6, "Lagos Chairman Email: $lgCommHeadEmail   Tel: $lgCommHeadPhone, Secretary: $lgCommSecPhone", 0, 1, 'C');

        $pdf->Ln(5);

        // Set font for the community details
        $pdf->SetFont('helvetica', 'B', 10);
        // Add the community details
        $pdf->Cell(0, 6, "REPRESENTATIVES", 0, 1, '');

        // Set font for the community details table structure
        $pdf->SetFont('helvetica', 'UI', 11);
        // Add the community details
        $pdf->Cell(0, 6, "     Name                                                       Phone                            Sex                                 Picture       ", 0, 1, '');

        $pdf->Ln(5);

        // Set font for the community reps
        $pdf->SetFont('helvetica', '', 12);
        
        // Loop through each representative and add their details on a new line
        foreach ($reps as $index => $rep) {
            $firstname = $rep['firstname'];
            $lastname = $rep['lastname'];
            $phone = $rep['phone'];
            $gender = $rep['gender'];
            $profilePic = [
                'name' => $_FILES['reps']['name'][$index]['profile_pic'],
                'type' => $_FILES['reps']['type'][$index]['profile_pic'],
                'tmp_name' => $_FILES['reps']['tmp_name'][$index]['profile_pic'],
                'error' => $_FILES['reps']['error'][$index]['profile_pic'],
                'size' => $_FILES['reps']['size'][$index]['profile_pic']
            ];
            $imageDirPath = $image_dir . $profilePic['name'];
    
            // Add representative's names and phone number
            $pdf->Cell(0, 10, ($index + 1) . " $firstname $lastname        $phone      $gender", 0, 0);

            // Check if the profile picture exists and is valid, then add it next to the text
            if (file_exists($imageDirPath)) {
                $pdf->Image($imageDirPath, 170, $pdf->GetY() - 5, 19, 19, '', '', '', true, 300, '', false, false, 1, true, false, false);
            }

            // Move to the next line below the current representative's details
            $pdf->Ln(20);
        }

        // Save the updated PDF
        $pdf->Output($letterHeadFilePath, 'F'); 

        return $letterHeadFilePath; 
    }
}
