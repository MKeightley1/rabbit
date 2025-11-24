<?php
// $url = "https://example.com/";

// Read raw POST body
$raw = file_get_contents("php://input");

// Decode JSON
$data = json_decode($raw, true);

// Extract fields safely
$date  = $data['date']  ?? null;
$url   = $data['url']   ?? null;
$regex = $data['regex'] ?? [];

// Build response
$response = [
    "date"  => $date,
    "url"   => $url,
    "regex" => $regex,
    "status" => "received"
];

// Output JSON
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);


echo "test- maurice - 24 Nov - 10:56am";
?>
