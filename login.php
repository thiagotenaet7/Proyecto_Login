<?php
// login.php
session_start();
require_once 'db_connect.php';

$message = '';
$popup = ''; // 'nouser' | 'badpass' | 'sent'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // servidor: validaciones mínimas
        if (!preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', $email)) {
            $popup = 'nouser';
        } else {
            $stmt = $pdo->prepare("SELECT id, nombre, password_hash FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            if (!$user) {
                $popup = 'nouser';
            } else {
                if (password_verify($password, $user['password_hash'])) {
                    // login correcto
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nombre'];
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $popup = 'badpass';
                }
            }
        }
    } elseif ($action === 'recover') {
        // recuperación: generar clave temporal y enviar por mail
        $email = trim($_POST['email_recover'] ?? '');
        if (preg_match('/^[A-Za-z0-9._%+-]+@gmail\.com$/i', $email)) {
            $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            if ($user) {
                $temp = bin2hex(random_bytes(4)); // 8 hex chars
                $newPass = $temp . 'A!'; // añadir complejidad mínima
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $up = $pdo->prepare("UPDATE usuarios SET password_hash = :ph WHERE id = :id");
                $up->execute(['ph' => $hash, 'id' => $user['id']]);

                // enviar mail (requiere configuración de correo en servidor)
                $to = $email;
                $subject = "Recuperación de contraseña";
                $body = "Hola {$user['nombre']},\n\nSe ha solicitado recuperar tu contraseña. Tu contraseña temporal es: {$newPass}\nPor seguridad cambia la contraseña al ingresar.\n\nSaludos.";
                $headers = "From: no-reply@miapp.local\r\n";
                @mail($to, $subject, $body, $headers);

                $popup = 'sent';
            } else {
                $popup = 'nouser';
            }
        } else {
            $popup = 'nouser';
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Iniciar sesión</title>
<link rel="stylesheet" href="estilo.css">
<script src="auto.js" defer></script>
</head>
<body>
<div class="container-wrap">
  <div class="card">
    <h2>Iniciar sesión</h2>

    <?php if(isset($_GET['registered'])): ?>
      <div style="color:green; text-align:left; margin-bottom:8px;">Registro exitoso. Ya podés iniciar sesión.</div>
    <?php endif; ?>

    <form method="POST" action="login.php" onsubmit="return clientValidateLogin();">
      <input class="input-field" id="email" name="email" placeholder="Correo @gmail.com">
      <div id="err-email" class="error"></div>

      <input class="input-field" id="password" name="password" type="password" placeholder="Contraseña">
      <div id="err-password" class="error"></div>

      <input type="hidden" name="action" value="login">
      <button class="btn" type="submit">Ingresar</button>
    </form>

    <a class="small-link" href="register.php">¿No tenés cuenta? Crear una</a>
    <a class="small-link" href="#" onclick="openRecover()">¿Olvidaste la contraseña?</a>
  </div>
</div>

<!-- Modal recovery simple -->
<div id="recoverModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
  <div style="background:white; padding:20px; border-radius:8px; width:90%; max-width:420px;">
    <h3>Recuperar contraseña</h3>
    <p>Se enviará una contraseña temporal al correo @gmail registrado.</p>
    <input id="email_recover" class="input-field" placeholder="tu-email@gmail.com">
    <div id="err-recover" class="error"></div>
    <div style="display:flex; gap:8px;">
      <button class="btn" onclick="sendRecover()">Enviar</button>
      <button class="btn" style="background:#777;" onclick="closeRecover()">Cancelar</button>
    </div>
  </div>
</div>

<script>
function clientValidateLogin(){
    clearErrors(['err-email','err-password']);
    const email = document.getElementById('email').value.trim();
    const pw = document.getElementById('password').value;
    let ok = true;
    if(!isGmail(email)){ showError('err-email','Email @gmail.com obligatorio'); ok=false; }
    if(pw.length < 8){ showError('err-password','La contraseña es obligatoria (mín 8)'); ok=false; }
    return ok;
}

function openRecover(){
    document.getElementById('recoverModal').style.display = 'flex';
}
function closeRecover(){
    document.getElementById('recoverModal').style.display = 'none';
}

function sendRecover(){
    clearErrors(['err-recover']);
    const email = document.getElementById('email_recover').value.trim();
    if(!isGmail(email)){
        showError('err-recover','Email @gmail.com obligatorio');
        return;
    }

    // Crear formulario dinámico para enviar POST action=recover a login.php
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'login.php';
    const inpAction = document.createElement('input'); inpAction.type='hidden'; inpAction.name='action'; inpAction.value='recover';
    const inpEmail = document.createElement('input'); inpEmail.type='hidden'; inpEmail.name='email_recover'; inpEmail.value=email;
    form.appendChild(inpAction); form.appendChild(inpEmail);
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
// mostrar popups basados en el resultado servidor
if ($popup === 'nouser') {
    echo "<script>alert('Usuario incorrecto o no existe.');</script>";
} elseif ($popup === 'badpass') {
    echo "<script>alert('Password incorrecta.');</script>";
} elseif ($popup === 'sent') {
    echo "<script>alert('Se envió una contraseña temporal al correo.');</script>";
}
?>
</body>
</html>
