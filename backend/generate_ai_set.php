<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$text = $data['text'] ?? '';

if (empty($text)) {
    echo json_encode(['success' => false, 'error' => 'No text provided']);
    exit;
}

$lines = preg_split('/\r?\n/', $text);
$terms = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    // Detect common separators
    $separator = null;
    if (strpos($line, ' - ') !== false) {
        $separator = ' - ';
    } elseif (strpos($line, ': ') !== false) {
        $separator = ': ';
    } elseif (strpos($line, ' — ') !== false) {
        $separator = ' — ';
    } elseif (strpos($line, ', ') !== false) {
        $separator = ', ';
    }

    if ($separator) {
        $parts = explode($separator, $line, 2);
        if (count($parts) === 2) {
            // Definition first, Term second
            $def = trim($parts[0]);
            $term = trim($parts[1]);
            if (!empty($def) && !empty($term)) {
                $terms[] = [
                    'definition' => $def,
                    'term' => $term
                ];
            }
        }
    }
}

// Fallback sentence parsing if structured lines aren't found in quantity
if (count($terms) < 5) {
    $sentences = preg_split('/(?<=[.?!])\s+/', $text);
    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);
        if (strlen($sentence) < 20) continue;

        if (preg_match('/^(.+?)\s+(is|refers to|defined as|means|consists of)\s+(.+)$/i', $sentence, $matches)) {
            $def = trim($matches[1]);
            $term = trim($matches[2] . ' ' . $matches[3]);
            if (strlen($term) < 150) {
                $terms[] = [
                    'definition' => ucfirst($def),
                    'term' => ucfirst($term)
                ];
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'terms' => array_slice($terms, 0, 200) // Now supports up to 200 cards!
]);
?>