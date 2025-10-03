<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php'; // ajusta según tu ruta
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

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');

if (!$query) {
    echo json_encode(['error' => 'No se recibió consulta']);
    exit;
}

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

$_SESSION['chat_history'][] = ['role' => 'user', 'message' => $query];

$payload = [
    "query" => $query,
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

$assistantReply = "No se encontraron resultados.";

if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data['results']) && count($data['results']) > 0) {
        $answers = [];
        foreach ($data['results'] as $res) {
            if (isset($res['document']['derivedStructData']['extractive_answers'][0]['content'])) {
                $answers[] = $res['document']['derivedStructData']['extractive_answers'][0]['content'];
            } elseif (isset($res['document']['title'])) {
                $answers[] = $res['document']['title'];
            }
        }
        $assistantReply = implode("\n\n", $answers);
    }
}

$_SESSION['chat_history'][] = ['role' => 'assistant', 'message' => $assistantReply];

echo json_encode(['reply' => $assistantReply, 'history' => $_SESSION['chat_history']]);
