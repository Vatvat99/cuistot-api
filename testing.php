<?php

require __DIR__.'/vendor/autoload.php';

// Configuration du client Http
$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://api.cuistot.dev'
]);

// Préparation des données que l'on va passer au client
$email = 'plop' . rand(0, 999) . '@plop.fr';

$data = array(
    'email' => $email,
    'password' => 'plop',
);

// On interroge le client Http
// 1) Crée un utilisateur
$response = $client->post('/app_dev.php/users', [
    'body' => json_encode($data)
]);

$userUrl = $response->getHeader('Location');

// 2) Récupère un utilisateur
$response = $client->get($userUrl[0]);

// 3) Récupère les utilisateurs
$response = $client->get('/app_dev.php/users');

// Affiche la réponse
echo 'Status: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . "\r\n";
foreach ($response->getHeaders() as $name => $values) {
    echo $name . ': ' . implode(', ', $values) . "\r\n";
}
echo "\r\n";
echo $response->getBody();
echo "\r\n";
die;