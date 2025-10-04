<?php
require __DIR__ . '../../vendor/autoload.php';

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/service-account.json');

// Crear el middleware para autenticación
$middleware = ApplicationDefaultCredentials::getMiddleware([
    'https://www.googleapis.com/auth/cloud-platform'
]);

$stack = HandlerStack::create();
$stack->push($middleware);

// Cliente HTTP con token automático
$client = new Client([
    'handler' => $stack,
    'auth' => 'google_auth'
]);

// Leer query del frontend
$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

// Endpoint de Vertex
$url = "https://discoveryengine.googleapis.com/v1alpha/projects/725425147709/locations/global/collections/default_collection/engines/premedic_1759348507869/servingConfigs/default_search:answer";

// Payload
$payload = [
    "query" => ["text" => $query],
    "relatedQuestionsSpec" => ["enable" => true],
    "answerGenerationSpec" => [
        "includeCitations" => true,
        "answerLanguageCode" => "es"
    ],
    "modelSpec" => [
        "modelVersion" => "gemini-2.5-flash/answer_gen/v1"
    ]
];

// Llamar a Vertex
$response = $client->post($url, [
    'json' => $payload
]);

header('Content-Type: application/json');
echo $response->getBody();
