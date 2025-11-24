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

// Fetch URL content
$html = @file_get_contents($url);
if ($html === false) {
    echo json_encode(["error" => "Failed to fetch URL"]);
    exit;
}

/*
    Your JavaScript regex equivalents
    We assume POST body includes:
    regex[0] = table regex
    regex[1] = event ID regex
    regex[2] = title regex
*/

$tableRegex    = $regex[0] ?? null;
$eventIdRegex  = $regex[1] ?? null;
$titleRegex    = $regex[2] ?? null;

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
