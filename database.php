<?php
// Configurações do banco de dados
$host = 'localhost:3307';
$dbname = 'consultas_api';
$user = 'apiuser';
$pass = 'senhaSegura123';

try {
    // Conexão com PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);

    // Configurações recomendadas para PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);      // Erros como exceções
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Resultados como array associativo
} catch (PDOException $e) {
    // Em produção, evite mostrar detalhes do erro!
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao conectar com o banco de dados.']);
    exit;
}