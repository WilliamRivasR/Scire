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

-- Volcando estructura para procedimiento scire.sp_login
DELIMITER //
CREATE PROCEDURE `sp_login`(
  IN p_correo VARCHAR(255),
  IN p_contraseña VARCHAR(255)
)
BEGIN
  SELECT * FROM usuarios WHERE correo = p_correo AND contraseña = hash_contraseña(p_contraseña);
END//
DELIMITER ;

-- Volcando estructura para procedimiento scire.sp_register
DELIMITER //
CREATE PROCEDURE `sp_register`(
  IN p_nombre VARCHAR(255),
  IN p_correo VARCHAR(255),
  IN p_usuario VARCHAR(255),
  IN p_contraseña VARCHAR(255)
)
BEGIN
  INSERT INTO usuarios (nombre, correo, usuario, contraseña)
  VALUES (p_nombre, p_correo, p_usuario, hash_contraseña(p_contraseña));
END//
DELIMITER ;

-- Volcando estructura para tabla scire.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla scire.usuarios: ~2 rows (aproximadamente)
REPLACE INTO `usuarios` (`id`, `nombre`, `correo`, `usuario`, `contraseña`) VALUES
	(2, 'William Rivas', 'williamjoserrs@gmail.com', 'Willy', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4'),
	(3, 'Alberto Valencia', 'abertico@gmail.com', 'Albertico', '03ac674216f3e15c761ee1a5e255f067953623c8b388b4459e13f978d7c846f4');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
