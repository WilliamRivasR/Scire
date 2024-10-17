<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'Database.php';

$db = new Database();
$db->connect();

if (isset($_GET['id'])) {
    $id_estudiante = $_GET['id'];

    // Obtener información del estudiante
    $stmt = $db->prepare("SELECT nombre, correo FROM usuarios WHERE id = ? AND tipo = 'Estudiante'");
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $estudiante = $result->fetch_assoc();

        echo "<!DOCTYPE html>
              <html lang='es'>
              <head>
                  <meta charset='UTF-8'>
                  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                  <title>Notas del Estudiante</title>
                  <link rel='stylesheet' href='assets/css/estilos.css'>
                  <script>
                      function abrirVentana() {
                          window.open('subir_notas.php?id=$id_estudiante', 'Subir Notas', 'width=600,height=500');
                      }
                  </script>
                  <style>
                      .progress-container {
                          width: 100%;
                          background-color: #f0f0f0;
                          border-radius: 10px;
                          overflow: hidden;
                          margin: 5px 0;
                      }

                      .progress-bar {
                          height: 25px;
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
              <body>";

        echo "<h2>Notas de " . htmlspecialchars($estudiante['nombre']) . "</h2>";

        // Llamar al procedimiento almacenado para obtener las notas
        $stmt = $db->prepare("CALL sp_obtener_materias_estudiante(?, ?)");
        $stmt->bind_param("ss", $estudiante['nombre'], $estudiante['correo']);

        if (!$stmt->execute()) {
            die("Error al ejecutar el procedimiento almacenado: " . $stmt->error);
        }

        // Procesar el primer resultado (información del estudiante)
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $info_estudiante = $result->fetch_assoc();
        } else {
            die("Error al obtener la información del estudiante.");
        }

        // Mover al siguiente conjunto de resultados
        $stmt->next_result();

        // Procesar el segundo resultado (título)
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $title = $result->fetch_assoc();
            echo "<h3>" . htmlspecialchars($title['titulo']) . "</h3>";
        } else {
            echo "<h3>No se encontró un título.</h3>";
        }

        // Mover al siguiente conjunto de resultados
        $stmt->next_result();

        // Procesar el tercer resultado (notas)
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>Materia</th>
                        <th>Notas Individuales</th>
                        <th>Nota Final</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
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
            echo "</table>";
        } else {
            echo "<p>No se encontraron notas para este estudiante.</p>";
        }

        // Botón "Subir Notas"
        echo "<button style='padding: 10px 40px; border: 2px solid #fff; font-size: 14px; background: #fff; font-weight: 600; cursor: pointer; color: #46A2FD; outline: none; transition: all 300ms; margin-top: 20px;' onclick='abrirVentana()'>Subir Notas</button>";

        // Botón "Volver" con margen a la izquierda
        echo "<button style='padding: 10px 40px; border: 2px solid #fff; font-size: 14px; background: #fff; font-weight: 600; cursor: pointer; color: #46A2FD; outline: none; transition: all 300ms; margin-top: 20px; margin-left: 20px;' onclick='window.location.href=\"login.php\"'>Volver</button>";
    } else {
        echo "Estudiante no encontrado.";
    }

    $stmt->close();
} else {
    echo "ID de estudiante no proporcionado.";
}

$db->close();
?>