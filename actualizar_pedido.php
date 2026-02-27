<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['ok'=>false,'error'=>'No autorizado']);
    exit;
}

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['ok'=>false,'error'=>'Datos inválidos']);
    exit;
}

$id       = intval($data['id']);
$productos = $data['productos'] ?? [];
$total    = floatval($data['total'] ?? 0);
$estado   = $data['estado'] ?? 'solicitado';

$estadosValidos = ['solicitado','pagado','no_pagado'];
if (!in_array($estado, $estadosValidos)) {
    echo json_encode(['ok'=>false,'error'=>'Estado inválido']);
    exit;
}

$productosJson = json_encode($productos, JSON_UNESCAPED_UNICODE);

$stmt = $pdo->prepare("
    UPDATE pedidos 
    SET productos = ?, total = ?, estado = ?, fecha_actualizacion = NOW()
    WHERE id = ?
");
$stmt->execute([$productosJson, $total, $estado, $id]);

echo json_encode(['ok'=>true,'rows'=>$stmt->rowCount()]);
?>
