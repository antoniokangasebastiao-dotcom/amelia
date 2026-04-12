<?php
session_start();

// Se já estiver logado, redirecionar
if (isset($_SESSION['admin_logado']) && $_SESSION['admin_logado'] === true) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ALTERE AQUI SEU USUÁRIO E SENHA!
    $usuario_correto = 'admin';
    $senha_correta = 'Kumosi2026!';  // Mude para uma senha forte!
    
    if ($username === $usuario_correto && $password === $senha_correta) {
        $_SESSION['admin_logado'] = true;
        $_SESSION['admin_usuario'] = $username;
        $_SESSION['admin_login_time'] = time();
        
        header('Location: index.php');
        exit;
    } else {
        $erro = 'Usuário ou senha incorretos!';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Kumosi Impact Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0a1929 0%, #1e3a5f 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(30, 58, 95, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 2.5rem;
            border: 1px solid rgba(245, 124, 0, 0.3);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h2 {
            color: white;
            font-size: 1.8rem;
        }
        
        .logo span {
            color: #f57c00;
        }
        
        .logo p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            color: white;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-label i {
            color: #f57c00;
        }
        
        .form-control {
            background: rgba(10, 25, 41, 0.8);
            border: 1px solid rgba(245, 124, 0, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(10, 25, 41, 0.9);
            border-color: #f57c00;
            box-shadow: 0 0 0 3px rgba(245, 124, 0, 0.2);
            color: white;
            outline: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #f57c00, #ff9800);
            color: #0a1929;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 124, 0, 0.3);
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #dc3545;
            border-radius: 10px;
            padding: 10px;
            font-size: 0.9rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h2><span>Kumosi</span> Impact Lab</h2>
                <p>Área Administrativa</p>
            </div>
            
            <?php if ($erro): ?>
                <div class="alert-danger text-center mb-4">
                    <i class="fas fa-exclamation-triangle"></i> <?= $erro ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Usuário
                    </label>
                    <input type="text" name="username" class="form-control" 
                           placeholder="Digite seu usuário" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Senha
                    </label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Digite sua senha" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>
            
            <div class="footer">
                <p><i class="fas fa-shield-alt"></i> Área segura - Acesso restrito</p>
            </div>
        </div>
    </div>
</body>
</html>