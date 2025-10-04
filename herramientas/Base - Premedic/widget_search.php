<?php
// widget_search.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require __DIR__ . '/../../vendor/autoload.php'; // Ajusta según tu ruta
use Google\Client;

// ==== CONFIG ====
$projectId = "725425147709";
$engineId  = "premedic_1759348507869";
$location  = "global";
$keyFile   = __DIR__ . "/service-account.json";

// ==== INPUT ====
$input = json_decode(file_get_contents("php://input"), true);
$query = trim($input['query'] ?? '');
if (!$query) {
    echo json_encode(['error' => 'No se envió consulta']);
    exit;
}

// ==== FUNCIONES ====
function getAccessToken($keyFile) {
    $client = new Client();
    $client->setAuthConfig($keyFile);
    $client->addScope('https://www.googleapis.com/auth/cloud-platform');
    $token = $client->fetchAccessTokenWithAssertion();
    return $token['access_token'] ?? null;
}

$accessToken = getAccessToken($keyFile);
if (!$accessToken) {
    echo json_encode(['error' => 'No se pudo obtener token de acceso']);
    exit;
}

// ==== 1️⃣ POST a search ====
$searchUrl = "https://discoveryengine.googleapis.com/v1alpha/projects/$projectId/locations/$location/collections/default_collection/engines/$engineId/servingConfigs/default_search:search";

$searchPayload = [
    "query" => $query,
    "pageSize" => 10,
    "queryExpansionSpec" => ["condition"=>"AUTO"],
    "spellCorrectionSpec" => ["mode"=>"AUTO"],
    "languageCode" => "es-419",
    "contentSearchSpec" => ["extractiveContentSpec"=>["maxExtractiveAnswerCount"=>1]],
    "userInfo" => ["timeZone"=>"America/Buenos_Aires"]
];

$ch = curl_init($searchUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($searchPayload));
$searchResp = curl_exec($ch);
if ($searchResp === false) {
    echo json_encode(['error'=>'Error al ejecutar búsqueda']);
    exit;
}
$searchData = json_decode($searchResp, true);
if (!$searchData || !isset($searchData['queryId']) || !isset($searchData['session'])) {
    echo json_encode(['error'=>'No se pudo obtener queryId o session de la búsqueda']);
    exit;
}

// ==== 2️⃣ POST a answer ====
$answerUrl = "https://discoveryengine.googleapis.com/v1alpha/projects/$projectId/locations/$location/collections/default_collection/engines/$engineId/servingConfigs/default_search:answer";

$answerPayload = [
    "query" => ["text"=>$query, "queryId"=>$searchData['queryId']],
    "session" => $searchData['session'],
    "relatedQuestionsSpec" => ["enable"=>true],
    "answerGenerationSpec" => [
        "ignoreAdversarialQuery"=>false,
        "ignoreNonAnswerSeekingQuery"=>false,
        "ignoreLowRelevantContent"=>false,
        "multimodalSpec"=> new stdClass(),
        "includeCitations"=>true,
        "promptSpec"=>[
            "preamble"=>"Dado el usuario y los resultados de búsqueda, genera una respuesta final clara y concisa en español, sin introducir información adicional."
        ]
    ],
    "answerLanguageCode"=>"es"
];

curl_setopt($ch, CURLOPT_URL, $answerUrl);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($answerPayload));
$answerResp = curl_exec($ch);
curl_close($ch);

$answerData = json_decode($answerResp, true);
if (!$answerData || !isset($answerData['answer']['answerText'])) {
    echo json_encode(['error'=>'No se pudo generar la respuesta', 'raw'=>$answerResp]);
    exit;
}

// ==== FORMATO FINAL ====
$result = [
    'title' => 'Asistente de Premedic',
    'answer' => $answerData['answer']['answerText'],
    'citations' => $answerData['answer']['citations'] ?? []
];

echo json_encode($result);
