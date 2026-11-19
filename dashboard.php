<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$nombre = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard</title>
<link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="container-wrap">
  <div class="card">
    <h2>Bienvenido <?php echo $nombre; ?> a tu dashboard</h2>
    <p>Esta es una vista protegida. Aquí podés agregar contenido del sistema.</p>

    <form method="POST" action="logout.php" style="margin-top:20px;">
      <button class="btn" type="submit">Cerrar sesión</button>
    </form>
  </div>
</div>
</body>
</html>
