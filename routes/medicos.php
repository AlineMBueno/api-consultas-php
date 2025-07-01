<?php

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../database.php';

$method = $_SERVER['REQUEST_METHOD'];

// Lida com GET /medicos ou /medicos/{id}
if ($method === 'GET') {
    if (isset($id)) {
        $stmt = $pdo->prepare("SELECT * FROM medicos WHERE id = ?");
        $stmt->execute([$id]);
        $medico = $stmt->fetch();

        if ($medico) {
            echo json_encode($medico);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Médico não encontrado']);
        }
    } else {
        $stmt = $pdo->query("SELECT * FROM medicos ORDER BY nome");
        echo json_encode($stmt->fetchAll());
    }
}

// Lida com POST /medicos
elseif ($method === 'POST') {
    $dados = json_decode(file_get_contents("php://input"), true);

    if (!isset($dados['nome'], $dados['crm'], $dados['especialidade'])) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nome, CRM e especialidade são obrigatórios.']);
        return;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO medicos (nome, crm, especialidade, telefone, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $dados['nome'],
            $dados['crm'],
            $dados['especialidade'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null
        ]);

        $idNovo = $pdo->lastInsertId();
        http_response_code(201);
        echo json_encode(['mensagem' => 'Médico cadastrado com sucesso', 'id' => $idNovo]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao cadastrar médico', 'detalhes' => $e->getMessage()]);
    }
}

// Lida com PUT /medicos/{id}
elseif ($method === 'PUT') {
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do médico é obrigatório para atualização.']);
        return;
    }

    $dados = json_decode(file_get_contents("php://input"), true);

    try {
        $stmt = $pdo->prepare("UPDATE medicos SET nome = ?, crm = ?, especialidade = ?, telefone = ?, email = ? WHERE id = ?");
        $stmt->execute([
            $dados['nome'],
            $dados['crm'],
            $dados['especialidade'],
            $dados['telefone'] ?? null,
            $dados['email'] ?? null,
            $id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['mensagem' => 'Médico atualizado com sucesso']);
        } else {
            echo json_encode(['mensagem' => 'Nenhuma alteração feita ou médico inexistente']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao atualizar médico', 'detalhes' => $e->getMessage()]);
    }
}

// Lida com DELETE /medicos/{id}
elseif ($method === 'DELETE') {
    if (!isset($id)) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID do médico é obrigatório para exclusão.']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM medicos WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['mensagem' => 'Médico excluído com sucesso']);
    } else {
        http_response_code(404);
        echo json_encode(['erro' => 'Médico não encontrado ou já excluído']);
    }
}

else {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
}