<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::find(2);
$user->tokens()->delete();
$newToken = $user->createToken('fresh-token')->plainTextToken;
echo "Token: " . $newToken . "\n\n";

$baseUrl = 'http://127.0.0.1:8000';

function sendRequest($url, $method, $token = null) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$headers = ['Accept: application/json'];
if ($token) $headers[] = 'Authorization: Bearer ' . $token;
if ($method === 'POST') curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);
return $response;
}

echo "1. Testing /api/user:\n";
echo sendRequest($baseUrl . '/api/user', 'GET', $newToken) . "\n\n";

echo "2. Testing organizer dashboard:\n";
echo sendRequest($baseUrl . '/api/Organizer-dashboard', 'GET', $newToken) . "\n\n";

echo "3. Testing logout:\n";
echo sendRequest($baseUrl . '/api/logout', 'POST', $newToken) . "\n\n";

echo "4. Testing /api/user after logout (should fail):\n";
echo sendRequest($baseUrl . '/api/user', 'GET', $newToken) . "\n";
