<?php
require_once 'Database.php';

$db = new Database();
$db->connect();

$correo = $_POST['correo'];
$contraseña = $_POST['contraseña'];
$tipo = $_POST['tipo'];

if (empty($correo) || empty($contraseña) || empty($tipo)) {
  echo "<div class='alerta error'>Error: Debes llenar todos los campos para iniciar sesión.</div>";
  header("Refresh: 2; url=index.php");
  exit;
}

$stmt = $db->prepare("CALL sp_login(?, ?, ?)");
$stmt->bind_param("sss", $correo, $contraseña, $tipo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  // Iniciar sesión
  session_start();
  $_SESSION['correo'] = $correo;
  echo "<div class='alerta exito'>Inicio de sesión exitoso, bienvenido!</div>";
  header("Refresh: 2; url=index.php");
} else {
  echo "<div class='alerta error'>Correo o contraseña incorrectos.</div>";
  header("Refresh: 2; url=index.php");
}

$stmt->close();
$db->close();
?>