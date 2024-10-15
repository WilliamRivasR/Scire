// Ejecutando funciones
document.getElementById("btn__iniciar-sesion").addEventListener("click", iniciarSesion);
document.getElementById("btn__registrarse").addEventListener("click", register);
window.addEventListener("resize", anchoPage);

// Declarando variables
var formulario_login = document.querySelector(".formulario__login");
var formulario_register = document.querySelector(".formulario__register");
var contenedor_login_register = document.querySelector(".contenedor__login-register");
var caja_trasera_login = document.querySelector(".caja__trasera-login");
var caja_trasera_register = document.querySelector(".caja__trasera-register");

// Variables para el modal
var modal = document.createElement("div");
modal.classList.add("modal");
modal.style.display = "none";
modal.style.position = "fixed";
modal.style.top = "50%";
modal.style.left = "50%";
modal.style.transform = "translate(-50%, -50%)";
modal.style.backgroundColor = "#fff";
modal.style.padding = "20px";
modal.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.1)";
document.body.appendChild(modal);

var modalContent = document.createElement("div");
modal.appendChild(modalContent);

// Función para cerrar el modal
modal.addEventListener("click", function() {
    modal.style.display = "none";
});

// Función para abrir el modal con las notas del estudiante
function mostrarNotasEstudiante(idEstudiante) {
    fetch('obtener_notas.php?id=' + idEstudiante)
        .then(response => response.json())
        .then(data => {
            modalContent.innerHTML = `
                <h3>Notas del estudiante</h3>
                <table>
                    <tr>
                        <th>Materia</th>
                        <th>Notas Individuales</th>
                        <th>Nota Final</th>
                    </tr>
                    ${data.map(nota => `
                        <tr>
                            <td>${nota.materia}</td>
                            <td>${nota.notas_individuales}</td>
                            <td>${nota.nota_final}</td>
                        </tr>
                    `).join('')}
                </table>
            `;
            modal.style.display = "block";
        });
}

document.querySelectorAll("tr.student-row").forEach(function(fila) {
    fila.addEventListener("click", function() {
        var idEstudiante = this.getAttribute("data-id");
        mostrarNotasEstudiante(idEstudiante);
    });
});

// Agregando eventos a las filas de la tabla de estudiantes
document.querySelectorAll("tr.estudiante").forEach(function(fila) {
    fila.addEventListener("click", function() {
        var idEstudiante = this.getAttribute("data-id");
        mostrarNotasEstudiante(idEstudiante);
    });
});

// FUNCIONES
function anchoPage() {
    if (window.innerWidth > 850) {
        caja_trasera_register.style.display = "block";
        caja_trasera_login.style.display = "block";
    } else {
        caja_trasera_register.style.display = "block";
        caja_trasera_register.style.opacity = "1";
        caja_trasera_login.style.display = "none";
        formulario_login.style.display = "block";
        contenedor_login_register.style.left = "0px";
        formulario_register.style.display = "none";
    }
}

anchoPage();

function iniciarSesion() {
    if (window.innerWidth > 850) {
        formulario_login.style.display = "block";
        contenedor_login_register.style.left = "10px";
        formulario_register.style.display = "none";
        caja_trasera_register.style.opacity = "1";
        caja_trasera_login.style.opacity = "0";
    } else {
        formulario_login.style.display = "block";
        contenedor_login_register.style.left = "0px";
        formulario_register.style.display = "none";
        caja_trasera_register.style.display = "block";
        caja_trasera_login.style.display = "none";
    }
}

function register() {
    if (window.innerWidth > 850) {
        formulario_register.style.display = "block";
        contenedor_login_register.style.left = "410px";
        formulario_login.style.display = "none";
        caja_trasera_register.style.opacity = "0";
        caja_trasera_login.style.opacity = "1";
    } else {
        formulario_register.style.display = "block";
        contenedor_login_register.style.left = "0px";
        formulario_login.style.display = "none";
        caja_trasera_register.style.display = "none";
        caja_trasera_login.style.display = "block";
        caja_trasera_login.style.opacity = "1";
    }
}
