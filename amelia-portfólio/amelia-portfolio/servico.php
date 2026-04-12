<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Permitir apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados do POST
$dados = sanitizar([
    'nome' => $_POST['nome'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telefone' => $_POST['telefone'] ?? '',
    'servico' => $_POST['servico'] ?? '',
    'orcamento' => $_POST['orcamento'] ?? '',
    'descricao' => $_POST['descricao'] ?? '',
    'prazo' => $_POST['prazo'] ?? null
]);

// Validar dados
$erros = validarDados($dados, [
    'nome' => ['required', 'min3'],
    'email' => ['required', 'email'],
    'servico' => ['required'],
    'descricao' => ['required', 'min10']
]);

if (!empty($erros)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $erros]);
    exit;
}

// Salvar no banco de dados
$conn = getConnection();
$salvoNoBanco = false;

if ($conn) {
    try {
        $sql = "INSERT INTO solicitacoes_servico (nome, email, telefone, servico, orcamento, descricao, prazo, ip_address, user_agent) 
                VALUES (:nome, :email, :telefone, :servico, :orcamento, :descricao, :prazo, :ip, :user_agent)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':email' => $dados['email'],
            ':telefone' => $dados['telefone'],
            ':servico' => $dados['servico'],
            ':orcamento' => $dados['orcamento'],
            ':descricao' => $dados['descricao'],
            ':prazo' => $dados['prazo'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        $salvoNoBanco = true;
        $solicitacaoId = $conn->lastInsertId();
        
    } catch(PDOException $e) {
        registrarLog('ERRO', 'Erro ao salvar solicitação', ['erro' => $e->getMessage()]);
    }
}

// Preparar email para o cliente
$emailClienteHTML = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0a1929; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f5f5f5; }
        .info { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .label { font-weight: bold; color: #f57c00; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Kumosi Impact Lab</h2>
            <p>Confirmação de Solicitação de Serviço</p>
        </div>
        <div class='content'>
            <p>Olá <strong>{$dados['nome']}</strong>,</p>
            <p>Recebemos sua solicitação para o serviço de <strong>{$dados['servico']}</strong>.</p>
            <div class='info'>
                <p><span class='label'>📝 Descrição:</span> {$dados['descricao']}</p>
            </div>
            <p>Entraremos em contacto em até 24h úteis para alinhar os próximos passos.</p>
            <p style='text-align: center; margin-top: 30px;'>
                <a href='" . SITE_URL . "' class='btn'>Visitar Site</a>
            </p>
        </div>
        <div class='footer'>
            <p>Kumosi Impact Lab - Construindo Impacto Social com Tecnologia</p>
        </div>
    </div>
</body>
</html>
";

// Enviar emails
$emailClienteEnviado = enviarEmailSimples($dados['email'], 'Solicitação Recebida - Kumosi Impact Lab', $emailClienteHTML);
$emailAdminEnviado = enviarEmailSimples(ADMIN_EMAIL, 'Nova Solicitação de Serviço - ' . $dados['nome'], $emailAdminHTML);

// Resposta de sucesso
echo json_encode([
    'success' => true,
    'message' => 'Solicitação enviada com sucesso! Entraremos em contacto em breve.',
    'saved' => $salvoNoBanco,
    'id' => $solicitacaoId ?? null
]);
?>