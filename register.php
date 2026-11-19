<?php
// register.php
session_start();
require_once 'db_connect.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recibir y sanitizar
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // validaciones servidor
    if ($nombre === '') $errors['nombre'] = 'El nombre es obligatorio';
    if ($apellido === '') $errors['apellido'] = 'El apellido es obligatorio';
    if ($email === '' || !preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', $email)) $errors['email'] = 'Email @gmail.com válido requerido';
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) $errors['password'] = 'Contraseña insegura';
    if ($password !== $password2) $errors['password2'] = 'Las contraseñas no coinciden';

    // comprobar si ya existe el usuario
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'El usuario ya existe';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash) VALUES (:nombre, :apellido, :email, :phash)");
        $stmt->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'phash' => $hash
        ]);
        $success = true;
        // opcional: iniciar sesión automáticamente o redirigir al login
        header("Location: login.php?registered=1");
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Crear cuenta</title>
<link rel="stylesheet" href="estilo.css">
<script src="auto.js" defer></script>
</head>
<body>
<div class="container-wrap">
  <div class="card">
    <h2>Crear Cuenta</h2>

    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $k=>$v): ?>
            <div class="error" style="text-align:left; margin-bottom:6px;"><?php echo htmlspecialchars($v); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" action="register.php" onsubmit="return clientValidateRegister();">
      <input class="input-field" id="nombre" name="nombre" placeholder="Nombre" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : '' ?>">
      <div id="err-nombre" class="error"></div>

      <input class="input-field" id="apellido" name="apellido" placeholder="Apellido" value="<?php echo isset($apellido) ? htmlspecialchars($apellido) : '' ?>">
      <div id="err-apellido" class="error"></div>

      <input class="input-field" id="email" name="email" placeholder="Correo @gmail.com" value="<?php echo isset($email) ? htmlspecialchars($email) : '' ?>">
      <div id="err-email" class="error"></div>

      <input class="input-field" id="password" type="password" name="password" placeholder="Contraseña">
      <div id="err-password" class="error"></div>

      <input class="input-field" id="password2" type="password" name="password2" placeholder="Repetir contraseña">
      <div id="err-password2" class="error"></div>

      <button class="btn" type="submit">Crear Cuenta</button>
    </form>

    <a class="small-link" href="login.php">¿Ya tenés cuenta? Iniciar sesión</a>
  </div>
</div>

<script>
// validación cliente usando funciones de auto.js (ya cargado)
function clientValidateRegister(){
    clearErrors(['err-nombre','err-apellido','err-email','err-password','err-password2']);
    const nombre = sanitizeInput(document.getElementById('nombre').value.trim());
    const apellido = sanitizeInput(document.getElementById('apellido').value.trim());
    const email = document.getElementById('email').value.trim();
    const pw = document.getElementById('password').value;
    const pw2 = document.getElementById('password2').value;
    let ok = true;

    if(nombre === '') { showError('err-nombre','El nombre es obligatorio'); ok = false; }
    if(apellido === '') { showError('err-apellido','El apellido es obligatorio'); ok = false; }
    if(!isGmail(email)) { showError('err-email','Email @gmail.com obligatorio'); ok = false; }
    if(!strongPassword(pw)) { showError('err-password','Mín 8 chars, mayúscula, minúscula, número y símbolo'); ok = false; }
    if(pw !== pw2) { showError('err-password2','Las contraseñas no coinciden'); ok = false; }

    return ok;
}
</script>
</body>
</html>
