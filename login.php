<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Database.php';

session_start();  // Iniciar o retomar sesión
$db = new Database();
$db->connect();

// Verificar si la sesión ya está iniciada
if (isset($_SESSION['correo'])) {
    // Obtener el correo de la sesión
    $correo = $_SESSION['correo'];
    
    // Preparar consulta para obtener los datos del usuario con sesión activa
    $stmt = $db->prepare("CALL sp_login_sesion(?)"); // Suponemos que hay un procedimiento almacenado para obtener datos de sesión
    $stmt->bind_param("s", $correo);
    
    if (!$stmt->execute()) {
        echo "<div class='alerta error'>Error al ejecutar el procedimiento almacenado: " . $stmt->error . "</div>";
        exit;
    }
    
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // Mostrar información del usuario
        $tipo_usuario = $user_data['tipo'] ?? 'Desconocido';
        $nombre = $user_data['nombre'] ?? 'Desconocido';
        $correo_usuario = $user_data['correo'] ?? 'Desconocido';
        $usuario = $user_data['usuario'] ?? 'Desconocido';

        // Encabezado y hoja de estilos
        echo "<!DOCTYPE html>
              <html lang='es'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Datos del Usuario</title>
                  <link rel='stylesheet' href='assets/css/estilos.css'>
              </head>
              <body>";

        // Información del usuario
        echo "<h3>Datos del Usuario</h3>";
        echo "<table>
                <tr>
                    <th>Tipo</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Usuario</th>
                </tr>
                <tr>
                    <td>{$tipo_usuario}</td>
                    <td>{$nombre}</td>
                    <td>{$correo_usuario}</td>
                    <td>{$usuario}</td>
                </tr>
              </table>";

        // Mostrar más detalles según el tipo de usuario
        if (strcasecmp($tipo_usuario, 'Estudiante') == 0) {
            $stmt->next_result();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $title = $result->fetch_assoc();
                echo "<h3>" . ($title['titulo'] ?? 'Notas del estudiante') . "</h3>";
            }

            $stmt->next_result();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                echo "<table>
                        <tr>
                            <th>Materia</th>
                            <th>Notas Individuales</th>
                            <th>Nota Final</th>
                        </tr>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . ($row['materia'] ?? 'N/A') . "</td>
                            <td>" . ($row['notas_individuales'] ?? 'N/A') . "</td>
                            <td>" . ($row['nota_final'] ?? 'N/A') . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<h3>No se encontraron materias para el estudiante.</h3>";
            }
        } elseif (strcasecmp($tipo_usuario, 'Profesor') == 0) {
            // Mostrar lista de estudiantes
            echo "<h3>Lista de Estudiantes</h3>";
            $stmt->next_result();
            $stmt_estudiantes = $db->prepare("SELECT id, nombre, correo, usuario FROM usuarios WHERE tipo = 'Estudiante'");
            $stmt_estudiantes->execute();
            $result_estudiantes = $stmt_estudiantes->get_result();
        
            if ($result_estudiantes && $result_estudiantes->num_rows > 0) {
                echo "<table>
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Usuario</th>
                        </tr>";
                
                while ($row = $result_estudiantes->fetch_assoc()) {
                    echo "<tr class='student-row' data-id='" . $row['id'] . "'>
                            <td>" . ($row['nombre'] ?? 'N/A') . "</td>
                            <td>" . ($row['correo'] ?? 'N/A') . "</td>
                            <td>" . ($row['usuario'] ?? 'N/A') . "</td>
                          </tr>";
                }
                echo "</table>";
        
                echo "<script>
                    document.querySelectorAll('.student-row').forEach(function(row) {
                        row.addEventListener('click', function() {
                            var studentId = this.getAttribute('data-id');
                            window.location.href = 'ver_notas_estudiante.php?id=' + studentId;
                        });
                    });
                </script>";
            } else {
                echo "<h3>No se encontraron estudiantes registrados.</h3>";
            }
            $stmt_estudiantes->close();
        }

        // Cerrar body y html
        echo "</body></html>";
    } else {
        echo "<div class='alerta error'>No se encontraron datos para este usuario.</div>";
    }

    $stmt->close();
}
// Si es una solicitud POST, manejar el inicio de sesión
else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
    $contraseña = isset($_POST['contraseña']) ? $_POST['contraseña'] : '';
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

    if (empty($correo) || empty($contraseña) || empty($tipo)) {
        echo "<div class='alerta error'>Error: Debes llenar todos los campos para iniciar sesión.</div>";
        exit;
    }

    $hash_contraseña = hash('sha256', $contraseña);

    $stmt = $db->prepare("CALL sp_login(?, ?, ?)");
    $stmt->bind_param("sss", $correo, $hash_contraseña, $tipo);

    if (!$stmt->execute()) {
        echo "<div class='alerta error'>Error al ejecutar el procedimiento almacenado: " . $stmt->error . "</div>";
        exit;
    }

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        if (isset($user_data['mensaje']) && $user_data['mensaje'] == 'No existe el estudiante') {
            echo "<div class='alerta error'>Usuario no encontrado o contraseña incorrecta. Por favor, verifica tus credenciales.</div>";
        } else {
            // Iniciar sesión
            $_SESSION['correo'] = $correo;

            // Mostrar la información del usuario que inició sesión
            // (Igual que el bloque anterior para sesión activa)
        }
    } else {
        echo "<div class='alerta error'>Correo o contraseña incorrectos.</div>";
    }

    $stmt->close();
}
// Si es una solicitud GET y no hay sesión, redirigir al index
else {
    header("Location: index.php");
    exit;
}

$db->close();
?>
