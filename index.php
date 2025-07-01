<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Inclui conexão com o banco
require 'database.php';

// Obtém a URL da requisição
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

// Espera-se que a rota seja algo como: /pacientes, /medicos, /consultas
$recurso = $uri[1] ?? null;
$id = $uri[2] ?? null;

// Roteamento básico
switch ($recurso) {
    case 'pacientes':
        require 'routes/pacientes.php';
        break;
    case 'medicos':
        require 'routes/medicos.php';
        break;
    case 'consultas':
        require 'routes/consultas.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['erro' => 'Rota não encontrada']);
}