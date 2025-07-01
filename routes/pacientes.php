<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../auth.php';

$method = $_SERVER['REQUEST_METHOD'];

// Lida com GET /pacientes ou /pacientes/{id}
if ($method === 'GET') {
    if (isset($id)) {
        $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
        $stmt->execute([$id]);
        $paciente = $stmt->fetch();

        if ($paciente) {
            echo json_encode($paciente);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Paciente não encontrado']);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM pacientes ORDER BY nome");
        echo json_encode($stmt->fetchAll());
    }
}

// Lida com POST /pacientes
elseif ($method === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['nome'], $dados['cpf'], $dados['data_nascimento'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome, CPF e data de nascimento são obrigatórios.']);
        return;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO pacientes (nome, cpf, data_nascimento, telefone, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $dados['nome'],
            $dados['cpf'],
            $dados['data_nascimento'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null
        ]);

        $idNovo = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode(['mensagem' => 'Paciente cadastrado com sucesso', 'id' => $idNovo]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao cadastrar paciente', 'detalhes' => $e->getMessage()]);
    }
}

// Lida com PUT /pacientes/{id}
elseif ($method === 'PUT') {
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do paciente é obrigatório para atualização.']);
        return;
    }

    $dados = json_decode(file_get_contents("php://input"), true);

    try {
        $stmt = $pdo->prepare("UPDATE pacientes SET nome = ?, cpf = ?, data_nascimento = ?, telefone = ?, email = ? WHERE id = ?");
        $stmt->execute([
            $dados['nome'],
            $dados['cpf'],
            $dados['data_nascimento'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null,
            $id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensagem' => 'Paciente atualizado com sucesso']);
        } else {
            echo json_encode(['mensagem' => 'Nenhuma alteração feita ou paciente inexistente']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao atualizar paciente', 'detalhes' => $e->getMessage()]);
    }
}

// Lida com DELETE /pacientes/{id}
elseif ($method === 'DELETE') {
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do paciente é obrigatório para exclusão.']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['mensagem' => 'Paciente excluído com sucesso']);
    } else {
        http_response_code(404);
        echo json_encode(['erro' => 'Paciente não encontrado ou já excluído']);
    }
}

else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}