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
    $stmt = $db->prepare("CALL sp_login_sesion(?)");
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
            <style>
                .logout-btn {
                    position: absolute;
                    bottom: 10px;
                    right: 10px;
                    padding: 10px 20px;
                    background-color: #f44336;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                }
        
                .logout-btn:hover {
                    background-color: #d32f2f;
                }

                /* Estilos simples para la barra de progreso */
                .progress-container {
                    width: 100%;
                    background-color: #f0f0f0;
                    border-radius: 10px;
                    overflow: hidden;
                    margin: 5px 0;
                }

                .progress-bar {
                    height: 25px;
                    background: linear-gradient(45deg, #4CAF50, #45a049);
                    color: white;
                    text-align: center;
                    line-height: 25px;
                    border-radius: 8px;
                    transition: width 0.5s ease-in-out;
                    min-width: 30px;
                    position: relative;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                }
            </style>
        </head>
        <body>
        
            <!-- Botón de cerrar sesión -->
            <form action='logout.php' method='POST'>
                <button type='submit' class='logout-btn'>Cerrar Sesión</button>
            </form>";

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
            try {
                $connection = $db->getConnection();
                
                while ($connection->more_results()) {
                    $connection->next_result();
                    $connection->store_result();
                }

                // Función auxiliar para mostrar una fila de notas
                function outputNotaRow($row) {
                    $nota_final = intval($row['nota_final']);
                    $color_class = '';
                    if ($nota_final >= 75) {
                        $color_class = 'background: linear-gradient(45deg, #4CAF50, #45a049);'; // Verde para aprobado
                    } else {
                        $color_class = 'background: linear-gradient(45deg, #f44336, #d32f2f);'; // Rojo para reprobado
                    }
                    
                    echo "<tr>
                            <td>" . htmlspecialchars($row['materia']) . "</td>
                            <td>" . htmlspecialchars($row['notas_individuales']) . "</td>
                            <td>
                                <div class='progress-container'>
                                    <div class='progress-bar' style='width: " . $nota_final . "%; " . $color_class . "'>
                                        " . $nota_final . "/100
                                    </div>
                                </div>
                            </td>
                          </tr>";
                }
                
                // Obtener ID del estudiante
                $query = "SELECT id FROM usuarios WHERE correo = ? AND tipo = 'Estudiante'";
                $stmt = $db->prepare($query);

                if (!$stmt) {
                    throw new Exception("Error preparando la consulta: " . $db->error());
                }

                $stmt->bind_param("s", $correo_usuario);
                if (!$stmt->execute()) {
                    throw new Exception("Error ejecutando la consulta: " . $stmt->error);
                }

                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $id_estudiante = $user['id'];

                    while ($connection->more_results()) {
                        $connection->next_result();
                        $connection->store_result();
                    }

                    $stmt_notas = $db->prepare("CALL sp_obtener_materias_estudiante(?, ?)");
                    if (!$stmt_notas) {
                        throw new Exception("Error preparando el procedimiento: " . $db->error());
                    }

                    $stmt_notas->bind_param("ss", $nombre, $correo_usuario);
                    if (!$stmt_notas->execute()) {
                        throw new Exception("Error ejecutando el procedimiento: " . $stmt_notas->error);
                    }

                    do {
                        $result = $stmt_notas->get_result();

                        if ($result === false) {
                            continue;
                        }

                        if ($result->num_rows > 0) {
                            $first_row = $result->fetch_assoc();
                            
                            if (isset($first_row['titulo'])) {
                                echo "<h3>" . htmlspecialchars($first_row['titulo']) . "</h3>";
                            } elseif (isset($first_row['materia'])) {
                                echo "<table>
                                        <tr>
                                            <th>Materia</th>
                                            <th>Notas Individuales</th>
                                            <th>Nota Final</th>
                                        </tr>";
                                outputNotaRow($first_row);
                                while ($row = $result->fetch_assoc()) {
                                    outputNotaRow($row);
                                }
                                echo "</table>";
                            }
                        }

                        $result->free();
                    } while ($stmt_notas->next_result());

                    $stmt_notas->close();
                } else {
                    echo "<p>No se encontró la información del estudiante.</p>";
                }
            } catch (Exception $e) {
                echo "<div class='alerta error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } elseif (strcasecmp($tipo_usuario, 'Profesor') == 0) {
            // Mostrar lista de estudiantes
            echo "<h3>Lista de Estudiantes</h3>";
            
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

            // Redirigir al mismo archivo para mostrar la información del usuario
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
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
