<?php
header('Content-Type: application/json');
require_once 'config.php';

$id = $_GET['id'] ?? 0;

$conn = getConnection();
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM mensagens_contato WHERE id = ?");
    $stmt->execute([$id]);
    $mensagem = $stmt->fetch();
    
    // Marcar como lida
    $stmt = $conn->prepare("UPDATE mensagens_contato SET lida = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode($mensagem);
} else {
    echo json_encode(['error' => 'Erro na conexão']);
}
?>