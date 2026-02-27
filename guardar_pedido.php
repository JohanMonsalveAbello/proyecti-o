<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedido.html');
    exit;
}

$nombre   = trim($_POST['nombre']   ?? '');
$celular  = trim($_POST['celular']  ?? '');
$ciudad   = trim($_POST['ciudad']   ?? '');

if (!$nombre || !$celular) {
    die('Faltan datos obligatorios.');
}

// Preparar hasta 5 productos como JSON
$productos = [];
for ($i = 1; $i <= 5; $i++) {
    $prod = trim($_POST["producto$i"] ?? '');
    $cant = intval($_POST["cantidad$i"] ?? 0);
    if ($prod !== '' && $cant > 0) {
        $productos[] = ['nombre' => $prod, 'cantidad' => $cant, 'precio' => 0];
    }
}
$productosJson = json_encode($productos, JSON_UNESCAPED_UNICODE);

$stmt = $pdo->prepare("
    INSERT INTO pedidos (nombre, celular, ciudad, productos, estado, fecha_creacion)
    VALUES (?, ?, ?, ?, 'solicitado', NOW())
");
$stmt->execute([$nombre, $celular, $ciudad, $productosJson]);

// Redirigir con mensaje de éxito
header('Location: pedido_ok.html');
exit;
?>
