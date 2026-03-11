<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedido.php');
    exit;
}

$nombre   = trim($_POST['nombre']   ?? '');
$celular  = trim($_POST['celular']  ?? '');
$ciudad   = trim($_POST['ciudad']   ?? '');
$destino  = $_POST['destino'] ?? 'Hogar';

if (!$nombre || !$celular) {
    die('Faltan datos obligatorios.');
}
// validar destino
$validDest = ['Restaurante','Negocio','Hogar'];
if (!in_array($destino, $validDest)) {
    $destino = 'Hogar';
}

// Preparar productos enviados dinámicamente
$productos = [];
// buscamos campos producto1, producto2, ...
foreach ($_POST as $key => $value) {
    if (preg_match('/^producto(\d+)$/', $key, $m)) {
        $i = $m[1];
        $prod = trim($value);
        $cant = intval($_POST["cantidad$i"] ?? 0);
        $unidad = trim($_POST["unidad$i"] ?? '');
        if ($prod !== '' && $cant > 0) {
            $productos[] = ['nombre' => $prod, 'cantidad' => $cant, 'unidad' => $unidad, 'precio' => 0];
        }
    }
}
$productosJson = json_encode($productos, JSON_UNESCAPED_UNICODE);

$stmt = $pdo->prepare("
    INSERT INTO pedidos (nombre, celular, ciudad, destino, productos, estado, fecha_creacion)
    VALUES (?, ?, ?, ?, ?, 'solicitado', NOW())
");
$stmt->execute([$nombre, $celular, $ciudad, $destino, $productosJson]);

// Redirigir con mensaje de éxito
header('Location: pedido_ok.html');
exit;
?>
