<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

class PdfHelper {
    public static function createLetterHeadedFile($communityId, $communityName, $letterHeadFile, $reps, $profilePic) {
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        $letterHeadFilePath = $uploadDir . 'letterhead_community_' . $communityId . '.pdf';

        // Copy the uploaded letterhead template to the destination
        move_uploaded_file($letterHeadFile['tmp_name'], $letterHeadFilePath);  // JUST ADDED

        // Initialize TcpdfFpdi
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);

        // Import the existing letterhead PDF page
        $pageCount = $pdf->setSourceFile($letterHeadFilePath);  // was letterHeadPath
        $tplIdx = $pdf->importPage(1);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx, 0, 0, 210, 297); // A4 dimensions in mm

        // Set the cursor position to leave space for the letterhead
        $pdf->SetY(40); // Adjust as needed

        // Set font for the group and member details
        $pdf->SetFont('helvetica', '', 14);
        
        // Add the group name at the top
        $pdf->Cell(0, 10, "$communityName Community Representatives", 0, 1, 'C');

        // Loop through each represenatative and add their details on a new line
        foreach ($reps as $index => $rep) {
            $firstname = $rep['firstname'];
            $lastname = $rep['lastname'];
            $phone = $rep['phone'];
    
            // $profilePic = $rep['profile_pic'];
            // $profilePic = $profilePic;

            // Add representative's names and phone number
            $pdf->Cell(0, 10, ($index + 1) . ".  Name: $firstname $lastname, Phone: $phone", 0, 0);

            // Check if the profile picture exists and is valid, then add it next to the text
            if (file_exists($profilePic['tmp_name'])) {
                $pdf->Image($profilePic['tmp_name'], 160, $pdf->GetY() - 10, 20, 20, '', '', '', true, 300, '', false, false, 1, false, false, false);
            }

            // Move to the next line below the current representative's details
            $pdf->Ln(20);
        }

        // Save the updated PDF
        $pdf->Output($letterHeadFilePath, 'F'); 

        return $letterHeadFilePath; 
    }
}
