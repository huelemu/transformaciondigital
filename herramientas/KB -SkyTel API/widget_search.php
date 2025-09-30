<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php'; // revisá la ruta
use Google\Client;

header('Content-Type: application/json');

$endpoint = "https://us-discoveryengine.googleapis.com/v1alpha/projects/725425147709/locations/us/collections/default_collection/engines/skytel-kb-test_1758718086421/servingConfigs/default_search:search";
$keyFile = __DIR__ . '/service-account.json';

function getAccessToken($keyFile) {
    $client = new Client();
    $client->setAuthConfig($keyFile);
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');
    $token = $client->fetchAccessTokenWithAssertion();
    return $token['access_token'];
}

$input = $_GET['q'] ?? '';
$q = trim($input);

if ($q === '') {
    echo json_encode(['error' => 'Consulta vacía', 'results' => []]);
    exit;
}

$payload = [
    "query" => $q,
    "pageSize" => 5,
    "queryExpansionSpec" => ["condition" => "AUTO"],
    "spellCorrectionSpec" => ["mode" => "AUTO"],
    "languageCode" => "es-419",
    "contentSearchSpec" => ["extractiveContentSpec" => ["maxExtractiveAnswerCount" => 1]],
    "userInfo" => ["timeZone" => "America/Buenos_Aires"],
    "session" => "projects/725425147709/locations/us/collections/default_collection/engines/skytel-kb-test_1758718086421/sessions/-"
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . getAccessToken($keyFile),
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['error' => 'Error al llamar a la API', 'results' => []]);
    exit;
}

$data = json_decode($response, true);
$results = [];

if (isset($data['results'])) {
    foreach ($data['results'] as $res) {
        $title = $res['document']['title'] ?? ($res['document']['id'] ?? '');
        $snippet = '';
        if (isset($res['document']['derivedStructData']['extractive_answers'][0]['content'])) {
            $snippet = $res['document']['derivedStructData']['extractive_answers'][0]['content'];
        }
        $results[] = [
            'title' => $title,
            'snippet' => $snippet
        ];
    }
}

echo json_encode(['results' => $results]);
