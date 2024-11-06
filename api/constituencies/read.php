<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Constituency.php';

// Create an instance of Constituency
$constituency = new Constituency();

// Fetch constituencies
$constituencies = $constituency->readConstituencies();

// Send the response as JSON
header('Content-Type: application/json');
echo json_encode($constituencies);
