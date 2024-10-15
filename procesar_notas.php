<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Database.php';

$db = new Database();
$db->connect();

if (isset($_POST['id_estudiante']) && isset($_POST['materia']) && isset($_POST['nota'])) {
    $id_estudiante = $_POST['id_estudiante'];
    $id_materia = $_POST['materia'];
    $nota = $_POST['nota'];

    // Validar que la nota esté entre 1 y 100
    if ($nota < 1 || $nota > 100 || !is_numeric($nota) || intval($nota) != $nota) {
        echo "La nota debe ser un número entero entre 1 y 100.";
        exit;
    }

    // Llamar al procedimiento almacenado 'sp_agregar_notas'
    $stmt = $db->prepare("CALL sp_agregar_notas(?, ?, ?)");
    $stmt->bind_param("iii", $id_estudiante, $id_materia, $nota); // 'iii' indica tres enteros

    if ($stmt->execute()) {
        echo "Notas agregadas correctamente.";
    } else {
        echo "Error al agregar las notas: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Datos incompletos.";
}

$db->close();
?>
