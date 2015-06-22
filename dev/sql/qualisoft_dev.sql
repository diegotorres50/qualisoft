-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-06-2015 a las 03:09:31
-- Versión del servidor: 5.6.24
-- Versión de PHP: 5.5.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `qualisoft_dev`
--
CREATE DATABASE IF NOT EXISTS `qualisoft_dev` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `qualisoft_dev`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logins`
--
-- Creación: 22-06-2015 a las 00:25:25
--

DROP TABLE IF EXISTS `logins`;
CREATE TABLE IF NOT EXISTS `logins` (
  `login_user_id` varchar(20) NOT NULL DEFAULT 'UNKNOWN' COMMENT 'Identificador del usuario quien crea la sesion, no la relacionamos como clave foranea para que el mantenimiento de esta tabla no tenga conflistos de relacionaes',
  `login_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha y hora en que se crea la sesion, es el complemento con el user:id para generar una llave primaria.',
  `login_id` bigint(20) unsigned zerofill NOT NULL COMMENT 'Un indice de secuencia que ayuda a indexar la tabla y a las busquedas de una sesion en particular',
  `login_status` set('CLOSED','OPENED') NOT NULL DEFAULT 'OPENED' COMMENT 'Estado de la sesion actual, que puede representar que la sesion esta viva o ya fue cerrada por el usuario',
  `login_useragent` text COMMENT 'userAgent del navegador cliente',
  `login_language` varchar(20) DEFAULT 'UNKNOWN' COMMENT 'Idioma local del usuario detectado en el cliente',
  `login_platform` varchar(45) DEFAULT 'UNKNOWN' COMMENT 'Plataforma local del usuario, por ejemplo linux o windows',
  `login_origin` varchar(100) DEFAULT 'UNKNOWN' COMMENT 'Dominio detectado con protocolo, por ejemplo http://www.qualisoft.com',
  `login_closed` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'Fecha hora del cierre de la sesion',
  `login_notes` varchar(20) DEFAULT NULL COMMENT 'Nota general de la sesion'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Registro de logins de usuario, conocido igual como registro de sesiones';

--
-- RELACIONES PARA LA TABLA `logins`:
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system`
--
-- Creación: 20-06-2015 a las 03:25:58
--

DROP TABLE IF EXISTS `system`;
CREATE TABLE IF NOT EXISTS `system` (
  `system_status` set('ACTIVE','INACTIVE') NOT NULL DEFAULT 'INACTIVE' COMMENT 'Define si el sistema esta activo o no para usarlo',
  `system_maintenance_msg` text NOT NULL COMMENT 'El texto por defecto que se muestra',
  `system_version` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT 'Versión del sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Información general de la aplicación';

--
-- RELACIONES PARA LA TABLA `system`:
--

--
-- Volcado de datos para la tabla `system`
--

INSERT INTO `system` (`system_status`, `system_maintenance_msg`, `system_version`) VALUES
('', 'El sistema no esta disponible en el momento por tareas de mantenimiento. \r\n\r\nPor favor intente mas tarde con contacte al administrador del sistema.', 1),
('ACTIVE', 'El sistema no esta disponible en el momento por tareas de mantenimiento. \r\n\r\nPor favor intente mas tarde con contacte al administrador del sistema.', 1),
('ACTIVE', 'El sistema no esta disponible en el momento por tareas de mantenimiento. \r\n\r\nPor favor intente mas tarde con contacte al administrador del sistema.', 1),
('ACTIVE', 'El sistema no esta disponible en el momento por tareas de mantenimiento. \r\n\r\nPor favor intente mas tarde con contacte al administrador del sistema.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--
-- Creación: 20-06-2015 a las 03:25:59
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` varchar(20) NOT NULL COMMENT 'Identificador unico del usuario, por ejemplo: diegotorres50',
  `user_document` varchar(15) DEFAULT NULL COMMENT 'Documento unico opcional para identificar al usuario, por ejemplo el numero de cedula o pasaporte',
  `user_status` set('ACTIVE','INACTIVE') NOT NULL DEFAULT 'INACTIVE' COMMENT 'Debe ser active o inactive',
  `user_name` varchar(200) NOT NULL COMMENT 'Nombre y apellido del usuario',
  `user_mail` varchar(200) NOT NULL COMMENT 'Correo electronico del usuario, deberia ser unico entre todos los usuarios',
  `user_pass` varchar(512) NOT NULL COMMENT 'Clave del usuario',
  `user_language` char(3) NOT NULL DEFAULT 'es' COMMENT 'Idioma opcional, se podria usar en un futuro para las traducciones del sistema.',
  `user_debugger` tinyint(1) DEFAULT '0' COMMENT '1 para determinar que el usuario puede ver datos ocultos en la interface de qualisofti como variables de prueba, esto ayudaria a depurar el crm en tiempo de ejecucion',
  `user_secretquestion` varchar(200) DEFAULT NULL COMMENT 'Pregunta secreta para validar la recuperacion de la clave',
  `user_secretanswer` varchar(200) DEFAULT NULL COMMENT 'Respuesta secreta para validar la recuperacion de la clave',
  `user_birthday` date DEFAULT NULL COMMENT 'Fecha de cumpleanios',
  `user_lastactivation` date DEFAULT NULL COMMENT 'Muestra la fecha desde que el usuario fue activado en el sistema',
  `user_alloweddays` int(3) DEFAULT NULL COMMENT 'Dias permitidos, si se quiere restringir el tiempo de activacion del usuario.',
  `user_photo` blob COMMENT 'Guarda en binario la imagen de perfil de usuario',
  `user_role` set('NONE','ADMIN') DEFAULT 'NONE' COMMENT 'Determina el role de usuario para la logica de accesos a los diferentes modulos del sistema.',
  `user_notes` text COMMENT 'Observaciones generales del usuario',
  `user_lastmovementdate` datetime DEFAULT NULL COMMENT 'Fecha y hora en que se toco el registro en la base de datos',
  `user_lastmovementip` varchar(15) DEFAULT NULL COMMENT 'Direccion ip para monitorear la ubicacion de quien toca el registro',
  `user_lastmovementwho` varchar(10) DEFAULT NULL COMMENT 'User Id del usuario que toca el registro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='App Users';

--
-- RELACIONES PARA LA TABLA `users`:
--

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`user_id`, `user_document`, `user_status`, `user_name`, `user_mail`, `user_pass`, `user_language`, `user_debugger`, `user_secretquestion`, `user_secretanswer`, `user_birthday`, `user_lastactivation`, `user_alloweddays`, `user_photo`, `user_role`, `user_notes`, `user_lastmovementdate`, `user_lastmovementip`, `user_lastmovementwho`) VALUES
('clayanine', '52234223', 'ACTIVE', 'Claudia Neira', 'clayanine@hotmail.com', '14e1b600b1fd579f47433b88e8d85291', 'es', 0, 'nombre de bebe', 'Mariana', NULL, NULL, 30, NULL, 'ADMIN', 'Otro usuario', '2015-06-15 00:00:28', NULL, NULL),
('diegotorres50', '80123856', 'ACTIVE', 'Diego Torres', 'diegotorres50@hotmail.com', '14e1b600b1fd579f47433b88e8d85291', 'es', 0, 'nombre de mascota de padres', 'falkor', '0000-00-00', NULL, 30, NULL, 'ADMIN', 'Usuario de prueba', '2015-06-15 00:00:28', NULL, NULL);

--
-- Disparadores `users`
--
DROP TRIGGER IF EXISTS `users_before_ins_tr`;
DELIMITER $$
CREATE TRIGGER `users_before_ins_tr` BEFORE INSERT ON `users`
 FOR EACH ROW BEGIN

#El campo clave lo interceptamos para cifrarlo en el campo.
SET NEW.user_pass = MD5(MD5(NEW.user_pass));

#Actualizamos la fecha y hora en que el registro fue creado
set NEW.user_lastmovementdate=now();
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `users_before_upd_tr`;
DELIMITER $$
CREATE TRIGGER `users_before_upd_tr` BEFORE UPDATE ON `users`
 FOR EACH ROW BEGIN

#Si el campo clave del usuario ha cambiado en el registro
if NEW.user_pass <> old.user_pass then
	SET NEW.user_pass = MD5(MD5(NEW.user_pass)); #Actualizamos la clave del usuario
end if;  

#Actualizamos la fecha y hora en que el registro fue actualizado 
set NEW.user_lastmovementdate=now();
END
$$
DELIMITER ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`login_user_id`,`login_time`), ADD UNIQUE KEY `login_id_UNIQUE` (`login_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `user_mail_UNIQUE` (`user_mail`), ADD UNIQUE KEY `user_documen_UNIQUE` (`user_document`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `logins`
--
ALTER TABLE `logins`
  MODIFY `login_id` bigint(20) unsigned zerofill NOT NULL AUTO_INCREMENT COMMENT 'Un indice de secuencia que ayuda a indexar la tabla y a las busquedas de una sesion en particular';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
