<?php



<?php

header('Content-Type: application/json');

// Example URL
$url = "https://www.punters.com.au/racing-results/2025-11-23";

// Create a context with headers
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
    echo json_encode(["error" => "Failed to fetch URL — possibly blocked by server"]);
    exit;
}

echo json_encode(["status" => "success", "length" => strlen($html)]);

