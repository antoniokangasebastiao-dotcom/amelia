<?php
header('Content-Type: application/json');
require_once 'config.php';

$id = $_GET['id'] ?? 0;

$conn = getConnection();
if ($conn) {
    $stmt = $conn->prepare("SELECT * FROM solicitacoes_servico WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode($stmt->fetch());
} else {
    echo json_encode(['error' => 'Erro na conexão']);
}
?>