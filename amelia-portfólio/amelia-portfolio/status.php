<?php
header('Content-Type: application/json');
require_once 'config.php';

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? 'pendente';

$conn = getConnection();
if ($conn) {
    $stmt = $conn->prepare("UPDATE solicitacoes_servico SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>