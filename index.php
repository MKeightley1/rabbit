<?php

header('Content-Type: application/json');

// Read POST body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$url = $data['url'] ?? null;

if (!$url) {
    echo json_encode(["error" => "Missing URL"]);
    exit;
}

// Create a stream context with User-Agent
$options = [
    "http" => [
        "method" => "GET",
        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) ".
                    "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0 Safari/537.36\r\n"
    ]
];
$context = stream_context_create($options);

// Fetch URL content
$html = @file_get_contents($url, false, $context);

if ($html === false) {
    echo json_encode(["error" => "Failed to fetch URL"]);
    exit;
}

// Optionally truncate output to avoid huge JSON
$preview = substr($html, 0, 5000); // first 5000 chars

echo json_encode([
    "url" => $url,
    "html_preview" => $html,
    "length" => strlen($html)
], JSON_PRETTY_PRINT);

?>
