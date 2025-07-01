<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../database.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($id)) {
        $stmt = $pdo->prepare("
            SELECT c.*, p.nome AS paciente, m.nome AS medico 
            FROM consultas c
            JOIN pacientes p ON c.id_paciente = p.id
            JOIN medicos m ON c.id_medico = m.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $consulta = $stmt->fetch();

        if ($consulta) {
            echo json_encode($consulta);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Consulta não encontrada']);
        }
    } else {
        // Monta base da query
$sql = "
    SELECT c.id, c.data_hora, c.motivo, 
           p.nome AS paciente, m.nome AS medico
    FROM consultas c
    JOIN pacientes p ON c.id_paciente = p.id
    JOIN medicos m ON c.id_medico = m.id
    WHERE 1 = 1
";

// Parâmetros para filtros
$params = [];

if (isset($_GET['data'])) {
    $sql .= " AND DATE(c.data_hora) = ?";
    $params[] = $_GET['data'];
}

if (isset($_GET['id_medico'])) {
    $sql .= " AND c.id_medico = ?";
    $params[] = $_GET['id_medico'];
}

if (isset($_GET['id_paciente'])) {
    $sql .= " AND c.id_paciente = ?";
    $params[] = $_GET['id_paciente'];
}

$sql .= " ORDER BY c.data_hora DESC";

// Executa com ou sem filtros
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll());
    }
}

// Agendar nova consulta: POST /consultas
elseif ($method === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['id_paciente'], $dados['id_medico'], $dados['data_hora'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Paciente, médico e data/hora são obrigatórios.']);
        return;
    }

    // Verifica se o paciente existe
    $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE id = ?");
    $stmt->execute([$dados['id_paciente']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['erro' => 'Paciente inválido']);
        return;
    }

    // Verifica se o médico existe
    $stmt = $pdo->prepare("SELECT id FROM medicos WHERE id = ?");
    $stmt->execute([$dados['id_medico']]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['erro' => 'Médico inválido']);
        return;
    }

    // Verifica se o horário já está ocupado para o médico
    $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_medico = ? AND data_hora = ?");
    $stmt->execute([$dados['id_medico'], $dados['data_hora']]);
    if ($stmt->fetch()) {
        http_response_code(409); // conflito
        echo json_encode(['erro' => 'Este horário já está ocupado para o médico selecionado.']);
        return;
    }

    // Agenda a consulta
    $stmt = $pdo->prepare("
        INSERT INTO consultas (id_paciente, id_medico, data_hora, motivo)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $dados['id_paciente'],
        $dados['id_medico'],
        $dados['data_hora'],
        $dados['motivo'] ?? null
    ]);

    http_response_code(201);
    echo json_encode(['mensagem' => 'Consulta agendada com sucesso']);
}
// Cancelar consulta: DELETE /consultas/{id}
elseif ($method === 'DELETE') {
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da consulta é obrigatório para exclusão.']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['mensagem' => 'Consulta cancelada com sucesso']);
    } else {
        http_response_code(404);
        echo json_encode(['erro' => 'Consulta não encontrada ou já cancelada']);
    }
}
// Reagendar ou atualizar consulta: PUT /consultas/{id}
elseif ($method === 'PUT') {
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da consulta é obrigatório para atualização.']);
        return;
    }

    $dados = json_decode(file_get_contents("php://input"), true);

    // Verifica se a consulta existe
    $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id = ?");
    $stmt->execute([$id]);
    $consulta = $stmt->fetch();

    if (!$consulta) {
        http_response_code(404);
        echo json_encode(['erro' => 'Consulta não encontrada']);
        return;
    }

    // Validação opcional: evitar mesmo médico no mesmo horário
    if (isset($dados['data_hora']) && $dados['data_hora'] !== $consulta['data_hora']) {
        $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_medico = ? AND data_hora = ? AND id != ?");
        $stmt->execute([$consulta['id_medico'], $dados['data_hora'], $id]);
        if ($stmt->fetch()) {
            http_response_code(409); // conflito
            echo json_encode(['erro' => 'Este horário já está ocupado para este médico.']);
            return;
        }
    }

    // Atualiza a consulta
    $stmt = $pdo->prepare("
        UPDATE consultas SET data_hora = ?, motivo = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $dados['data_hora'] ?? $consulta['data_hora'],
        $dados['motivo'] ?? $consulta['motivo'],
        $id
    ]);

    echo json_encode(['mensagem' => 'Consulta atualizada com sucesso']);
}

else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}