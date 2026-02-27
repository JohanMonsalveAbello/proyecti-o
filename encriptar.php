<?php
require_once 'db.php'; 

$usuario = "admin";
$nuevaPassword = "surtipaez2026";

// Generar hash seguro
$hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE usuario = ?");
    $stmt->execute([$hash, $usuario]);

    if ($stmt->rowCount() > 0) {
        echo " Contraseña actualizada correctamente para el usuario: $usuario";
    } else {
        echo " No se encontró el usuario.";
    }

} catch (PDOException $e) {
    echo " Error: " . $e->getMessage();
}