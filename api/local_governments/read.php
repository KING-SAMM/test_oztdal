<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'LocalGovernment.php';

// Create an instance of LocalGovernment
$localGovernment = new LocalGovernment();

// Fetch local governments
$localGovts = $localGovernment->readLocalGovts();

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($localGovts);
