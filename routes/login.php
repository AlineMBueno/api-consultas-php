<?php

require_once __DIR__ . '/../database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);

if (!isset($dados['email'], $dados['senha'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'E-mail e senha são obrigatórios']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$dados['email']]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($dados['senha'], $usuario['senha'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Usuário ou senha inválidos']);
    exit;
}

// Gera token simples (não JWT neste exemplo)
$token = base64_encode($usuario['id'] . '|' . time());

echo json_encode([
    'mensagem' => 'Login bem-sucedido',
    'token' => $token
]);