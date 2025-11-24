<?php

header('Content-Type: application/json');

// Read POST body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Extract parameters
$date  = $data['date']  ?? null;
$url   = $data['url']   ?? null;          // full URL to fetch
$regex = $data['regex'] ?? [];            // array of regex strings

if (!$date || !$url || !is_array($regex)) {
    echo json_encode(["error" => "Missing required fields: date, url, regex[]"]);
    exit;
}

// Create a stream context with a browser-like User-Agent
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

// Wrap regex patterns in PHP delimiters
$tableRegex    = isset($regex[0]) ? '#' . $regex[0] . '#s' : null;   // 's' flag for dotall
$eventIdRegex  = isset($regex[1]) ? '#' . $regex[1] . '#s' : null;
$titleRegex    = isset($regex[2]) ? '#' . $regex[2] . '#s' : null;

if (!$tableRegex || !$eventIdRegex || !$titleRegex) {
    echo json_encode(["error" => "Three regex patterns required"]);
    exit;
}

$results = [];

// Match all tables
if (preg_match_all($tableRegex, $html, $tableMatches, PREG_SET_ORDER)) {
    foreach ($tableMatches as $tableMatch) {
        
        $tableContent = $tableMatch[1];

        // Extract title
        if (!preg_match($titleRegex, $tableContent, $titleMatch)) {
            continue;
        }

        $title = trim($titleMatch[1]);
        $parts = preg_split('/\s*-\s*/', $title);
        $trackName = $parts[0] ?? null;
        $raceName  = $parts[1] ?? null;

        if (!$trackName || !$raceName) continue;

        // Extract event ID
        if (!preg_match($eventIdRegex, $tableContent, $eventMatch)) {
            continue;
        }

        $eventId = $eventMatch[1];

        // Find track entry or create it
        $trackIndex = null;
        foreach ($results as $i => $track) {
            if ($track['trackName'] === $trackName) {
                $trackIndex = $i;
                break;
            }
        }

        if ($trackIndex === null) {
            $results[] = [
                "trackName" => $trackName,
                "races" => []
            ];
            $trackIndex = count($results) - 1;
        }

        // Add race
        $results[$trackIndex]['races'][] = [
            "raceName" => $raceName,
            "eventId" => $eventId
        ];
    }
}

echo json_encode([
    "date" => $date,
    "url" => $url,
    "results" => $results
], JSON_PRETTY_PRINT);

?>
