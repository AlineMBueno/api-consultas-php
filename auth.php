<?php

// Simulação básica: token vem como "Authorization: Bearer <token>"
$headers = getallheaders();
$auth = $headers['Authorization'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token ausente ou inválido']);
    exit;
}

$token = base64_decode($matches[1]);
[$userId, $timestamp] = explode('|', $token);

// Exemplo de validade: 1 dia (86400s)
if ((time() - $timestamp) > 86400) {
    http_response_code(401);
    echo json_encode(['erro' => 'Token expirado']);
    exit;
}