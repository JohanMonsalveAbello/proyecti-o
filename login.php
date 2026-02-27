<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usuario && $password) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_id']     = $user['id'];
            $_SESSION['admin_nombre'] = $user['nombre'];
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar Sesión – SurtiPaez</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{--verde:#2d6a2d;--amarillo:#f9c22e;--naranja:#e8650a;--cafe:#5c3a1e;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{
      font-family:'Nunito',sans-serif;
      background:linear-gradient(135deg,#1a4a1a,#2d6a2d 50%,#5c3a1e);
      min-height:100vh;display:flex;align-items:center;justify-content:center;
      padding:20px;
    }
    .login-card{
      background:white;border-radius:28px;
      padding:52px 44px;max-width:420px;width:100%;
      box-shadow:0 28px 70px rgba(0,0,0,0.35);
      animation:fadeIn 0.5s ease;
    }
    @keyframes fadeIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    .logo{
      font-family:'Playfair Display',serif;
      color:var(--verde);font-size:2.4rem;font-weight:900;
      text-align:center;margin-bottom:6px;
    }
    .logo span{color:var(--naranja);}
    .subtitle{text-align:center;color:#888;margin-bottom:36px;font-size:0.9rem;}
    .field{margin-bottom:20px;}
    label{display:block;font-weight:700;color:var(--cafe);font-size:0.9rem;margin-bottom:6px;}
    input{
      width:100%;border:2px solid #e0e0e0;border-radius:12px;
      padding:13px 16px;font-family:'Nunito',sans-serif;font-size:1rem;
      outline:none;transition:border-color 0.2s,box-shadow 0.2s;
    }
    input:focus{border-color:#4caf50;box-shadow:0 0 0 4px rgba(76,175,80,0.12);}
    .error{
      background:#fdecea;border:1px solid #f44336;
      color:#c62828;border-radius:10px;
      padding:12px 16px;font-size:0.9rem;margin-bottom:20px;
      display:flex;align-items:center;gap:8px;
    }
    .btn{
      width:100%;background:linear-gradient(135deg,var(--amarillo),var(--naranja));
      color:var(--cafe);font-family:'Nunito',sans-serif;
      font-weight:800;font-size:1.1rem;
      padding:16px;border:none;border-radius:50px;cursor:pointer;
      box-shadow:0 6px 20px rgba(233,101,10,0.3);
      transition:transform 0.2s,box-shadow 0.2s;
    }
    .btn:hover{transform:translateY(-2px);box-shadow:0 10px 30px rgba(233,101,10,0.4);}
    .back{display:block;text-align:center;margin-top:20px;color:#888;font-size:0.9rem;text-decoration:none;}
    .back:hover{color:var(--verde);}
    .hint{background:#e8f5e9;border-radius:10px;padding:12px 16px;font-size:0.82rem;color:var(--verde);margin-top:20px;text-align:center;}
  </style>
</head>
<body>
  <div class="login-card">
    <div class="logo">🌿 Surti<span>Paez</span></div>
    <p class="subtitle">Panel de Administración</p>

    <?php if($error): ?>
      <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field">
        <label for="usuario">👤 Usuario</label>
        <input type="text" id="usuario" name="usuario" placeholder="admin" required
               value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>">
      </div>
      <div class="field">
        <label for="password">🔒 Contraseña</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn">Entrar al Panel →</button>
    </form>

    <a href="index.html" class="back">← Volver a la página principal</a>
    
  </div>
</body>
</html>
