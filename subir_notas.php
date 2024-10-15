<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Notas</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script>
        function enviarNotas() {
            const materiaSelect = document.getElementById('materia');
            const notaInput = document.getElementById('nota');
            const idEstudiante = document.getElementById('id_estudiante').value;

            const materia = materiaSelect.value;
            const nota = notaInput.value;

            if (materia === '' || nota === '') {
                alert('Por favor, complete todos los campos.');
                return;
            }

            // Validar que la nota esté entre 1 y 100
            if (nota < 1 || nota > 100 || !Number.isInteger(Number(nota))) {
                alert('La nota debe ser un número entero entre 1 y 100.');
                return;
            }

            // Crear objeto para enviar mediante POST
            const formData = new FormData();
            formData.append('materia', materia);
            formData.append('nota', nota);
            formData.append('id_estudiante', idEstudiante);

            // Enviar la información al servidor usando fetch
            fetch('procesar_notas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data); // Mostrar el resultado del servidor
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</head>
<body>
    <main>
        <div class="contenedor__todo">
            <div class="caja__trasera">
                <div>
                    <h2>Subir Notas</h2>

                    <?php
                    if (isset($_GET['id'])) {
                        $id_estudiante = htmlspecialchars($_GET['id']);
                        echo "<input type='hidden' id='id_estudiante' value='$id_estudiante'>";
                    } else {
                        echo "<p>No se ha proporcionado un ID de estudiante.</p>";
                        exit;
                    }
                    ?>

                    <form>
                        <label for="materia">Seleccione la materia:</label>
                        <select id="materia" class="input-custom">
                            <option value="">Seleccione una materia</option>
                            <option value="1">Matemáticas</option>
                            <option value="2">Física</option>
                            <option value="3">Química</option>
                            <option value="4">Inglés</option>
                        </select>

                        <label for="nota">Escriba la nota (1 - 100):</label>
                        <input type="number" id="nota" class="input-custom" min="1" max="100" step="1" placeholder="Ejemplo: 85" required>

                        <button type="button" class="boton-volver" onclick="enviarNotas()">Enviar</button>
                    </form>

                    <button class="boton-volver" onclick="window.close()">Volver</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>