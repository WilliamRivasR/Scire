-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         10.4.32-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para scire
CREATE DATABASE IF NOT EXISTS `scire` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `scire`;

-- Volcando estructura para función scire.hash_contraseña
DELIMITER //
CREATE FUNCTION `hash_contraseña`(p_contraseña VARCHAR(255)) RETURNS varchar(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
BEGIN
  RETURN SHA2(p_contraseña, 256);
END//
DELIMITER ;

-- Volcando estructura para tabla scire.materias
CREATE TABLE IF NOT EXISTS `materias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla scire.materias: ~4 rows (aproximadamente)
REPLACE INTO `materias` (`id`, `nombre`) VALUES
	(1, 'Matematicas'),
	(2, 'Fisica'),
	(3, 'Quimica'),
	(4, 'Ingles');

-- Volcando estructura para tabla scire.notas
CREATE TABLE IF NOT EXISTS `notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_estudiante` int(11) DEFAULT NULL,
  `id_materia` int(11) DEFAULT NULL,
  `nota` int(3) NOT NULL CHECK (`nota` between 1 and 100),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id_estudiante` (`id_estudiante`) USING BTREE,
  KEY `id_materia` (`id_materia`) USING BTREE,
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `notas_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla scire.notas: ~16 rows (aproximadamente)
REPLACE INTO `notas` (`id`, `id_estudiante`, `id_materia`, `nota`) VALUES
	(1, 1, 1, 4),
	(2, 1, 1, 40),
	(3, 3, 4, 50),
	(4, 3, 4, 30),
	(5, 3, 4, 5),
	(6, 3, 2, 90),
	(7, 3, 1, 100),
	(8, 1, 1, 90),
	(9, 1, 2, 100),
	(10, 1, 2, 25),
	(11, 1, 2, 25),
	(12, 1, 2, 50),
	(13, 1, 4, 1),
	(14, 1, 4, 1),
	(15, 1, 4, 2),
	(16, 1, 4, 4);

-- Volcando estructura para procedimiento scire.sp_agregar_notas
DELIMITER //
CREATE PROCEDURE `sp_agregar_notas`(
    IN p_id_estudiante INT,
    IN p_id_materia INT,
    IN p_nota INT -- Acepta solo números enteros
)
BEGIN
    -- Verificar si la nota está en el rango permitido (1 a 100)
    IF p_nota < 1 OR p_nota > 100 THEN
	         -- Si la nota está fuera de rango, lanzar un error
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: La nota debe estar entre 1 y 100';
    ELSE
        -- Si la nota está en el rango, insertar la nota en la tabla 'notas'
        INSERT INTO notas (id_estudiante, id_materia, nota)
        VALUES (p_id_estudiante, p_id_materia, p_nota);
    END IF;
END//
DELIMITER ;

-- Volcando estructura para procedimiento scire.sp_login
DELIMITER //
CREATE PROCEDURE `sp_login`(
  IN p_correo VARCHAR(100),
  IN p_contraseña VARCHAR(255),
  IN p_tipo ENUM('Estudiante', 'Profesor')
)
BEGIN
  DECLARE id_estudiante INT;
  DECLARE existe_estudiante BOOLEAN;

  SELECT id INTO id_estudiante
  FROM usuarios
  WHERE correo = p_correo AND contraseña = p_contraseña AND tipo = p_tipo;

  IF id_estudiante IS NOT NULL THEN
    SET existe_estudiante = TRUE;
  ELSE
    SET existe_estudiante = FALSE;
  END IF;

  IF existe_estudiante THEN
    IF p_tipo = 'Estudiante' THEN
      SELECT 'Estudiante' AS tipo, id, nombre, correo, usuario
      FROM usuarios
      WHERE id = id_estudiante;

      SELECT 'Notas del estudiante' AS titulo;

      SELECT 
        m.nombre AS materia,
        GROUP_CONCAT(n.nota ORDER BY n.periodo SEPARATOR ', ') AS notas_individuales,
        COALESCE(AVG(n.nota), 0) AS nota_final
      FROM 
        materias m
        LEFT JOIN notas n ON m.id = n.id_materia AND n.id_estudiante = id_estudiante
      GROUP BY 
        m.nombre
      ORDER BY 
        m.nombre;
    ELSE
      SELECT 'Profesor' AS tipo, id, nombre, correo, usuario
      FROM usuarios
      WHERE id = id_estudiante;
    END IF;
  ELSE
    SELECT 'No existe el estudiante' AS mensaje;
  END IF;
END//
DELIMITER ;

-- Volcando estructura para procedimiento scire.sp_login_sesion
DELIMITER //
CREATE PROCEDURE `sp_login_sesion`(
    IN p_correo VARCHAR(255)
)
BEGIN
    -- Selecciona el nombre, correo, usuario y tipo desde la tabla usuarios
    SELECT nombre, correo, usuario, tipo
    FROM usuarios
    WHERE correo = p_correo;
END//
DELIMITER ;

-- Volcando estructura para procedimiento scire.sp_obtener_materias_estudiante
DELIMITER //
CREATE PROCEDURE `sp_obtener_materias_estudiante`(
  IN p_nombre VARCHAR(100),
  IN p_correo VARCHAR(100)
)
BEGIN
  DECLARE id_estudiante INT;
  DECLARE existe_estudiante BOOLEAN;

  -- Obtener el ID del estudiante basado en el nombre y correo
  SELECT id INTO id_estudiante
  FROM usuarios
  WHERE nombre = p_nombre AND correo = p_correo AND tipo = 'Estudiante';

  -- Verificar si el estudiante existe
  IF id_estudiante IS NOT NULL THEN
    SET existe_estudiante = TRUE;
  ELSE
    SET existe_estudiante = FALSE;
  END IF;

  -- Si el estudiante existe, devolver las materias y notas
  IF existe_estudiante THEN
    -- Información del estudiante
    SELECT id, nombre, correo, usuario
    FROM usuarios
    WHERE id = id_estudiante;

    -- Título de la tabla de materias
    SELECT 'Materias del estudiante' AS titulo;

    -- Obtener las materias y notas del estudiante (sin `periodo`)
    SELECT 
      m.nombre AS materia,
      GROUP_CONCAT(n.nota ORDER BY n.id SEPARATOR ', ') AS notas_individuales,
      COALESCE(ROUND(AVG(n.nota)), 0) AS nota_final  -- Redondear la nota final a un número entero
    FROM 
      materias m
      LEFT JOIN notas n ON m.id = n.id_materia AND n.id_estudiante = id_estudiante
    GROUP BY 
      m.nombre
    ORDER BY 
      m.nombre;
  ELSE
    -- Si el estudiante no existe, mostrar un mensaje
    SELECT 'No existe el estudiante' AS mensaje;
  END IF;
END//
DELIMITER ;

-- Volcando estructura para procedimiento scire.sp_register
DELIMITER //
CREATE PROCEDURE `sp_register`(
  IN p_nombre VARCHAR(50),
  IN p_correo VARCHAR(100),
  IN p_usuario VARCHAR(50),
  IN p_contraseña VARCHAR(255),
  IN p_tipo ENUM('Estudiante', 'Profesor')
)
BEGIN
  INSERT INTO usuarios (nombre, correo, usuario, contraseña, tipo)
  VALUES (p_nombre, p_correo, p_usuario, p_contraseña, p_tipo);
END//
DELIMITER ;

-- Volcando estructura para tabla scire.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('Estudiante','Profesor') NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla scire.usuarios: ~3 rows (aproximadamente)
REPLACE INTO `usuarios` (`id`, `tipo`, `nombre`, `correo`, `usuario`, `contraseña`) VALUES
	(1, 'Estudiante', 'Juan Perez', 'juan.perez@example.com', 'juanperez', 'edf9cf90718610ee7de53c0dcc250739239044de9ba115bb0ca6026c3e4958a5'),
	(2, 'Profesor', 'William Rivas', 'williamjoserrs@gmail.com', 'Willy', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4'),
	(3, 'Estudiante', 'Alberto Valencia', 'albertico@gmail.com', 'Albertico', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
