<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$csvFile = 'score.csv';

// GET - Leggi scores
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {

    if (!file_exists($csvFile)) {
        echo json_encode(['success' => false, 'error' => 'File non trovato']);
        exit;
    }

    $scores = [];
    $file = fopen($csvFile, 'r');

    // Salta header
    fgetcsv($file);

    while (($data = fgetcsv($file)) !== FALSE) {
        if (count($data) >= 2) {
            $scores[] = [
                'nome' => trim($data[0]),
                'score' => (int)trim($data[1])
            ];
        }
    }

    fclose($file);

    // Ordina per score decrescente
    usort($scores, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Prendi top 8
    $scores = array_slice($scores, 0, 8);

    echo json_encode(['success' => true, 'scores' => $scores]);
}

// POST - Salva scores
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save') {

    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['scores']) || !is_array($input['scores'])) {
        echo json_encode(['success' => false, 'error' => 'Dati non validi']);
        exit;
    }

    $scores = $input['scores'];

    // Ordina per score decrescente
    usort($scores, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Prendi top 8
    $scores = array_slice($scores, 0, 8);

    // Scrivi nel CSV
    $file = fopen($csvFile, 'w');

    // Header
    fputcsv($file, ['nome', 'score']);

    // Dati
    foreach ($scores as $entry) {
        fputcsv($file, [$entry['nome'], $entry['score']]);
    }

    fclose($file);

    echo json_encode(['success' => true]);
}

else {
    echo json_encode(['success' => false, 'error' => 'Azione non valida']);
}
?>