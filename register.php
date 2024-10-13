<?php
require_once 'Database.php';

$db = new Database();
$db->connect();

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$usuario = $_POST['usuario'];
$contraseña = $_POST['contraseña'];
$tipo = $_POST['tipo'];

if (empty($nombre) || empty($correo) || empty($usuario) || empty($contraseña) || empty($tipo)) {
  echo "<div class='alerta error'>Error: Debes llenar todos los campos para registrarte.</div>";
  header("Refresh: 2; url=index.php");
  exit;
}

$pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
if (!preg_match($pattern, $correo)) {
  echo "<div class='alerta error'>Error: El correo electrónico no es válido.</div>";
  header("Refresh: 2; url=index.php");
  exit;
}

$stmt = $db->prepare("CALL sp_register(?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nombre, $correo, $usuario, $contraseña, $tipo);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo "<div class='alerta exito'>Registro exitoso, ahora puedes iniciar sesión.</div>";
  header("Refresh: 2; url=index.php");
} else {
  echo "<div class='alerta error'>Error al registrar.</div>";
  header("Refresh: 2; url=index.php");
}

$stmt->close();
$db->close();
?>