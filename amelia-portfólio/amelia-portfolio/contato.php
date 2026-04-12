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
    'assunto' => $_POST['assunto'] ?? '',
    'mensagem' => $_POST['mensagem'] ?? ''
]);

// Validar dados
$erros = validarDados($dados, [
    'nome' => ['required', 'min3'],
    'email' => ['required', 'email'],
    'assunto' => ['required'],
    'mensagem' => ['required', 'min10']
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
        $sql = "INSERT INTO mensagens_contato (nome, email, telefone, assunto, mensagem, ip_address) 
                VALUES (:nome, :email, :telefone, :assunto, :mensagem, :ip)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nome' => $dados['nome'],
            ':email' => $dados['email'],
            ':telefone' => $dados['telefone'],
            ':assunto' => $dados['assunto'],
            ':mensagem' => $dados['mensagem'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        $salvoNoBanco = true;
        
    } catch(PDOException $e) {
        registrarLog('ERRO', 'Erro ao salvar contato', ['erro' => $e->getMessage()]);
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
        .footer { background: #0a1929; color: white; padding: 10px; text-align: center; font-size: 12px; }
        .btn { display: inline-block; background: #f57c00; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Kumosi Impact Lab</h2>
            <p>Confirmação de Contacto</p>
        </div>
        <div class='content'>
            <p>Olá <strong>{$dados['nome']}</strong>,</p>
            <p>Recebemos a sua mensagem sobre <strong>{$dados['assunto']}</strong> e responderemos em breve.</p>
            <p>Agradecemos o seu contacto!</p>
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

// Preparar email para o administrador
$emailAdminHTML = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
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
            <h2>Nova Mensagem de Contacto</h2>
        </div>
        <div class='content'>
            <div class='info'>
                <p><span class='label'>👤 Nome:</span> {$dados['nome']}</p>
                <p><span class='label'>📧 Email:</span> {$dados['email']}</p>
                <p><span class='label'>📞 Telefone:</span> " . ($dados['telefone'] ?: 'Não informado') . "</p>
                <p><span class='label'>📋 Assunto:</span> {$dados['assunto']}</p>
                <p><span class='label'>💬 Mensagem:</span></p>
                <p>{$dados['mensagem']}</p>
                <p><span class='label'>🌐 IP:</span> {$_SERVER['REMOTE_ADDR']}</p>
            </div>
            <p style='text-align: center; margin-top: 30px;'>
                <a href='" . SITE_URL . "/backend/admin/' class='btn'>Ver no Admin</a>
            </p>
        </div>
    </div>
</body>
</html>
";

// Enviar emails
$emailClienteEnviado = enviarEmailSimples($dados['email'], 'Recebemos a sua mensagem - Kumosi Impact Lab', $emailClienteHTML);
$emailAdminEnviado = enviarEmailSimples(ADMIN_EMAIL, 'Nova mensagem de ' . $dados['nome'], $emailAdminHTML);

// Resposta de sucesso
echo json_encode([
    'success' => true,
    'message' => 'Mensagem enviada com sucesso! Responderemos em breve.',
    'saved' => $salvoNoBanco
]);
?>