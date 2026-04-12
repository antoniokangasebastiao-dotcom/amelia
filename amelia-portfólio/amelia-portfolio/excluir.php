<?php
header('Content-Type: application/json');
require_once 'config.php';

$tipo = $_GET['tipo'] ?? '';
$id = $_GET['id'] ?? 0;

$conn = getConnection();
if ($conn) {
    if ($tipo == 'mensagem') {
        $stmt = $conn->prepare("DELETE FROM mensagens_contato WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } elseif ($tipo == 'solicitacao') {
        $stmt = $conn->prepare("DELETE FROM solicitacoes_servico WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>