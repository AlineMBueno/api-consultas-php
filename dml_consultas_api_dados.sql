
USE consultas_api;

-- Inserir médicos de exemplo
INSERT INTO medicos (nome, crm, especialidade, email, telefone) VALUES
('Dr. João Andrade', '123456-SP', 'Cardiologia', 'joao@clinica.com', '11998887777'),
('Dra. Maria Costa', '654321-RJ', 'Dermatologia', 'maria@clinica.com', '21997776666');

-- Inserir pacientes de exemplo
INSERT INTO pacientes (nome, cpf, data_nascimento, telefone, email) VALUES
('Carlos Silva', '123.456.789-00', '1985-04-12', '11999998888', 'carlos@exemplo.com'),
('Fernanda Lima', '987.654.321-00', '1992-09-25', '11988887777', 'fernanda@exemplo.com');

-- Inserir consultas de exemplo
INSERT INTO consultas (id_paciente, id_medico, data_hora, motivo) VALUES
(1, 1, '2025-07-01 10:00:00', 'Consulta de rotina'),
(2, 2, '2025-07-02 14:30:00', 'Revisão de pele');

-- Inserir usuário adicional para login
INSERT INTO usuarios (nome, email, senha) VALUES
('Usuário Teste', 'teste@api.com', SHA2('teste123', 256));
