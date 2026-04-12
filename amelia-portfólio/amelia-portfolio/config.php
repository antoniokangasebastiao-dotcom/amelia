<?php
// Configuração do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'kumosi_db');
define('DB_USER', 'kumosi_user');
define('DB_PASS', 'Kumosi2026!');  // ALTERE PARA SUA SENHA

// Configuração do Site
define('SITE_NAME', 'Kumosi Impact Lab');
define('SITE_URL', 'https://seusite.com');  // ALTERE PARA SEU DOMÍNIO
define('ADMIN_EMAIL', 'ameliaevaristo@kumosi.com');  // SEU EMAIL

// Configuração de Email (GMAIL - Recomendado)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seuemail@gmail.com');  // SEU EMAIL GMAIL
define('SMTP_PASS', 'sua_senha_app');       // SENHA DE APP DO GMAIL
define('SMTP_FROM', 'naoresponda@kumosi.com');
define('SMTP_FROM_NAME', 'Kumosi Impact Lab');

// Timezone
date_default_timezone_set('Africa/Luanda');

// Função de conexão com o banco de dados
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch(PDOException $e) {
        error_log("Erro de conexão: " . $e->getMessage());
        return null;
    }
}

// Função para enviar email usando PHPMailer
function enviarEmail($destinatario, $assunto, $corpoHTML, $corpoTexto = '') {
    require_once __DIR__ . '/vendor/autoload.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    try {
        // Configurar SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Remetente e Destinatário
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($destinatario);
        $mail->addReplyTo(ADMIN_EMAIL, SITE_NAME);
        
        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpoHTML;
        $mail->AltBody = $corpoTexto ?: strip_tags($corpoHTML);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar email: {$mail->ErrorInfo}");
        return false;
    }
}

// Função para enviar email simples (sem PHPMailer)
function enviarEmailSimples($destinatario, $assunto, $mensagem) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">" . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    
    return mail($destinatario, $assunto, $mensagem, $headers);
}

// Função para validar dados
function validarDados($dados, $campos) {
    $erros = [];
    
    foreach ($campos as $campo => $regras) {
        $valor = trim($dados[$campo] ?? '');
        
        if (in_array('required', $regras) && empty($valor)) {
            $erros[$campo] = 'Campo obrigatório';
        }
        
        if (in_array('email', $regras) && !empty($valor) && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $erros[$campo] = 'Email inválido';
        }
        
        if (in_array('min3', $regras) && strlen($valor) < 3) {
            $erros[$campo] = 'Mínimo 3 caracteres';
        }
        
        if (in_array('min10', $regras) && strlen($valor) < 10) {
            $erros[$campo] = 'Mínimo 10 caracteres';
        }
    }
    
    return $erros;
}

// Função para sanitizar dados
function sanitizar($dados) {
    $sanitizado = [];
    foreach ($dados as $key => $value) {
        $sanitizado[$key] = htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }
    return $sanitizado;
}

// Função para registrar log
function registrarLog($tipo, $mensagem, $dados = []) {
    $log = date('Y-m-d H:i:s') . " | {$tipo} | {$mensagem} | " . json_encode($dados) . PHP_EOL;
    file_put_contents(__DIR__ . '/logs/error.log', $log, FILE_APPEND);
}
?>