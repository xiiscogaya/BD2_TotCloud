-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-12-2024 a las 14:04:06
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `totclouddb`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `realizar_backup_incremental` ()   BEGIN
    -- Tabla almacenamiento
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'almacenamiento',
        'UPDATE',
        JSON_OBJECT(
            'idAlmacenamiento', idAlmacenamiento,
            'Nombre', Nombre,
            'Tipo', Tipo,
            'VelocidadLectura', VelocidadLectura,
            'VelocidadEscritura', VelocidadEscritura,
            'Capacidad', Capacidad,
            'PrecioH', PrecioH,
            'Cantidad', Cantidad
        )
    FROM almacenamiento
    WHERE idAlmacenamiento NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idAlmacenamiento') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'almacenamiento'
    );

    -- Tabla cpu
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'cpu',
        'UPDATE',
        JSON_OBJECT(
            'idCPU', idCPU,
            'Nombre', Nombre,
            'Fabricante', Fabricante,
            'Arquitectura', Arquitectura,
            'Nucleos', Nucleos,
            'Frecuencia', Frecuencia,
            'PrecioH', PrecioH,
            'Cantidad', Cantidad
        )
    FROM cpu
    WHERE idCPU NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idCPU') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'cpu'
    );

    -- Tabla direccionip
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'direccionip',
        'UPDATE',
        JSON_OBJECT(
            'idIp', idIp,
            'Direccion', Direccion,
            'PrecioH', PrecioH,
            'idPaaS', idPaaS
        )
    FROM direccionip
    WHERE idIp NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idIp') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'direccionip'
    );

    -- Tabla paas
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'paas',
        'UPDATE',
        JSON_OBJECT(
            'idPaaS', idPaaS,
            'Nombre', Nombre,
            'Estado', Estado,
            'idSO', idSO
        )
    FROM paas
    WHERE idPaaS NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idPaaS') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'paas'
    );

    -- Tabla ram
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'ram',
        'UPDATE',
        JSON_OBJECT(
            'idRAM', idRAM,
            'Nombre', Nombre,
            'Fabricante', Fabricante,
            'Frecuencia', Frecuencia,
            'Capacidad', Capacidad,
            'Tipo', Tipo,
            'PrecioH', PrecioH,
            'Cantidad', Cantidad
        )
    FROM ram
    WHERE idRAM NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idRAM') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'ram'
    );

    -- Tabla motor
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'motor',
        'UPDATE',
        JSON_OBJECT(
            'idMotor', idMotor,
            'Nombre', Nombre,
            'Version', Version,
            'PrecioH', PrecioH
        )
    FROM motor
    WHERE idMotor NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idMotor') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'motor'
    );

    -- Tabla sistemaoperativo
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'sistemaoperativo',
        'UPDATE',
        JSON_OBJECT(
            'idSO', idSO,
            'Nombre', Nombre,
            'Arquitectura', Arquitectura,
            'Version', Version,
            'Tipo', Tipo,
            'MinAlmacenamiento', MinAlmacenamiento,
            'MinRAM', MinRAM,
            'PrecioH', PrecioH
        )
    FROM sistemaoperativo
    WHERE idSO NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idSO') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'sistemaoperativo'
    );

    -- Tabla usuario
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'usuario',
        'UPDATE',
        JSON_OBJECT(
            'idUsuario', idUsuario,
            'Nombre', Nombre,
            'Usuario', Usuario,
            'Email', Email,
            'Telefono', Telefono,
            'Direccion', Direccion,
            'FechaRegistro', FechaRegistro
        )
    FROM usuario
    WHERE idUsuario NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idUsuario') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'usuario'
    );

    -- Tabla organizacion
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'organizacion',
        'UPDATE',
        JSON_OBJECT(
            'idOrganizacion', idOrganizacion,
            'Nombre', Nombre,
            'Descripcion', Descripcion,
            'idCreador', idCreador
        )
    FROM organizacion
    WHERE idOrganizacion NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idOrganizacion') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'organizacion'
    );

    -- Tabla grupo
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'grupo',
        'UPDATE',
        JSON_OBJECT(
            'idGrupo', idGrupo,
            'Nombre', Nombre,
            'Descripcion', Descripcion,
            'idOrg', idOrg
        )
    FROM grupo
    WHERE idGrupo NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idGrupo') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'grupo'
    );

    -- Tabla privilegio
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'privilegio',
        'UPDATE',
        JSON_OBJECT(
            'idPrivilegio', idPrivilegio,
            'Nombre', Nombre,
            'Descripcion', Descripcion
        )
    FROM privilegio
    WHERE idPrivilegio NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idPrivilegio') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'privilegio'
    );

    -- Tabla monitorizacion
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'monitorizacion',
        'UPDATE',
        JSON_OBJECT(
            'idMonitorizacion', idMonitorizacion,
            'TipoEvento', TipoEvento,
            'Descripcion', Descripcion,
            'Fecha', Fecha,
            'Hora', Hora,
            'idPaaS', idPaaS,
            'idSaaS', idSaaS
        )
    FROM monitorizacion
    WHERE idMonitorizacion NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idMonitorizacion') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'monitorizacion'
    );

    -- Tabla trabajador
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'trabajador',
        'UPDATE',
        JSON_OBJECT(
            'idTrabajador', idTrabajador,
            'idUsuario', idUsuario
        )
    FROM trabajador
    WHERE idTrabajador NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idTrabajador') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'trabajador'
    );

    -- Tabla r_grup_priv
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_grup_priv',
        'UPDATE',
        JSON_OBJECT(
            'idGrup', idGrup,
            'idPriv', idPriv
        )
    FROM r_grup_priv
    WHERE idGrup NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idGrup') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_grup_priv'
    );

    -- Tabla r_paas_almacenamiento
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_paas_almacenamiento',
        'UPDATE',
        JSON_OBJECT(
            'idPaaS', idPaaS,
            'idAlmacenamiento', idAlmacenamiento,
            'Cantidad', Cantidad
        )
    FROM r_paas_almacenamiento
    WHERE idPaaS NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idPaaS') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_paas_almacenamiento'
    );

    -- Tabla r_paas_cpu
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_paas_cpu',
        'UPDATE',
        JSON_OBJECT(
            'idPaaS', idPaaS,
            'idCPU', idCPU,
            'Cantidad', Cantidad
        )
    FROM r_paas_cpu
    WHERE idPaaS NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idPaaS') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_paas_cpu'
    );

    -- Tabla r_paas_grup
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_paas_grup',
        'UPDATE',
        JSON_OBJECT(
            'idPaaS', idPaaS,
            'idGrup', idGrup
        )
    FROM r_paas_grup
    WHERE idPaaS NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idPaaS') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_paas_grup'
    );

    -- Tabla r_paas_ram
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_paas_ram',
        'UPDATE',
        JSON_OBJECT(
            'idPaaS', idPaaS,
            'idRAM', idRAM,
            'Cantidad', Cantidad
        )
    FROM r_paas_ram
    WHERE idPaaS NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idPaaS') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_paas_ram'
    );

    -- Tabla r_saas_grup
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_saas_grup',
        'UPDATE',
        JSON_OBJECT(
            'idSaaS', idSaaS,
            'idGrup', idGrup
        )
    FROM r_saas_grup
    WHERE idSaaS NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idSaaS') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_saas_grup'
    );

    -- Tabla r_usuario_grupo
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_usuario_grupo',
        'UPDATE',
        JSON_OBJECT(
            'idUsuario', idUsuario,
            'idGrupo', idGrupo
        )
    FROM r_usuario_grupo
    WHERE idUsuario NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idUsuario') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_usuario_grupo'
    );

    -- Tabla r_usuario_org
    INSERT INTO backup (Fecha, Hora, Tipo, Tabla, Accion, Datos)
    SELECT 
        CURDATE(),
        CURTIME(),
        'Incremental',
        'r_usuario_org',
        'UPDATE',
        JSON_OBJECT(
            'idUsuario', idUsuario,
            'idOrg', idOrg
        )
    FROM r_usuario_org
    WHERE idUsuario NOT IN (
        SELECT JSON_EXTRACT(Datos, '$.idUsuario') 
        FROM backup 
        WHERE Fecha = CURDATE() AND Tabla = 'r_usuario_org'
    );

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacenamiento`
--

CREATE TABLE `almacenamiento` (
  `idAlmacenamiento` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `VelocidadLectura` decimal(10,2) NOT NULL,
  `VelocidadEscritura` decimal(10,2) NOT NULL,
  `Capacidad` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `almacenamiento`
--

INSERT INTO `almacenamiento` (`idAlmacenamiento`, `Nombre`, `Tipo`, `VelocidadLectura`, `VelocidadEscritura`, `Capacidad`, `PrecioH`, `Cantidad`) VALUES
(1, 'Samsung 970 EVO Plus', 'SSD', 3500.00, 3300.00, 1000.00, 0.20, 0),
(2, 'Seagate Barracuda', 'HDD', 150.00, 140.00, 2000.00, 0.10, 50),
(3, 'Western Digital Blue SN570', 'NVMe', 3500.00, 3000.00, 512.00, 0.25, 10),
(4, 'Kingston A400', 'SATA', 500.00, 450.00, 256.00, 0.05, 40);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backup`
--

CREATE TABLE `backup` (
  `idBackup` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Tabla` varchar(100) NOT NULL,
  `Accion` varchar(50) NOT NULL,
  `Datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`Datos`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `backup`
--

INSERT INTO `backup` (`idBackup`, `Fecha`, `Hora`, `Tipo`, `Tabla`, `Accion`, `Datos`) VALUES
(51, '2024-12-12', '16:38:39', 'Incremental', 'almacenamiento', 'UPDATE', '{\"idAlmacenamiento\": 1, \"Nombre\": \"Samsung 970 EVO Plus\", \"Tipo\": \"SSD\", \"VelocidadLectura\": 3500.00, \"VelocidadEscritura\": 3300.00, \"Capacidad\": 1000.00, \"PrecioH\": 0.20, \"Cantidad\": 0}'),
(52, '2024-12-12', '16:38:39', 'Incremental', 'almacenamiento', 'UPDATE', '{\"idAlmacenamiento\": 2, \"Nombre\": \"Seagate Barracuda\", \"Tipo\": \"HDD\", \"VelocidadLectura\": 150.00, \"VelocidadEscritura\": 140.00, \"Capacidad\": 2000.00, \"PrecioH\": 0.10, \"Cantidad\": 53}'),
(53, '2024-12-12', '16:38:39', 'Incremental', 'almacenamiento', 'UPDATE', '{\"idAlmacenamiento\": 3, \"Nombre\": \"Western Digital Blue SN570\", \"Tipo\": \"NVMe\", \"VelocidadLectura\": 3500.00, \"VelocidadEscritura\": 3000.00, \"Capacidad\": 512.00, \"PrecioH\": 0.25, \"Cantidad\": 15}'),
(54, '2024-12-12', '16:38:39', 'Incremental', 'almacenamiento', 'UPDATE', '{\"idAlmacenamiento\": 4, \"Nombre\": \"Kingston A400\", \"Tipo\": \"SATA\", \"VelocidadLectura\": 500.00, \"VelocidadEscritura\": 450.00, \"Capacidad\": 256.00, \"PrecioH\": 0.05, \"Cantidad\": 40}'),
(58, '2024-12-12', '16:38:39', 'Incremental', 'cpu', 'UPDATE', '{\"idCPU\": 1, \"Nombre\": \"Intel Core i5-11600K\", \"Fabricante\": \"Intel\", \"Arquitectura\": \"x86-64\", \"Nucleos\": 6, \"Frecuencia\": 3.90, \"PrecioH\": 0.25, \"Cantidad\": 0}'),
(59, '2024-12-12', '16:38:39', 'Incremental', 'cpu', 'UPDATE', '{\"idCPU\": 2, \"Nombre\": \"AMD Ryzen 7 5800X\", \"Fabricante\": \"AMD\", \"Arquitectura\": \"x86-64\", \"Nucleos\": 8, \"Frecuencia\": 3.80, \"PrecioH\": 0.30, \"Cantidad\": 0}'),
(60, '2024-12-12', '16:38:39', 'Incremental', 'cpu', 'UPDATE', '{\"idCPU\": 3, \"Nombre\": \"Intel Core i9-12900K\", \"Fabricante\": \"Intel\", \"Arquitectura\": \"x86-64\", \"Nucleos\": 16, \"Frecuencia\": 3.20, \"PrecioH\": 0.50, \"Cantidad\": 10}'),
(61, '2024-12-12', '16:38:39', 'Incremental', 'cpu', 'UPDATE', '{\"idCPU\": 4, \"Nombre\": \"AMD Ryzen Threadripper 3990X\", \"Fabricante\": \"AMD\", \"Arquitectura\": \"x86-64\", \"Nucleos\": 64, \"Frecuencia\": 2.90, \"PrecioH\": 1.00, \"Cantidad\": 5}'),
(62, '2024-12-12', '16:38:39', 'Incremental', 'cpu', 'UPDATE', '{\"idCPU\": 5, \"Nombre\": \"Intel Xeon Gold 6258R\", \"Fabricante\": \"Intel\", \"Arquitectura\": \"x86-64\", \"Nucleos\": 28, \"Frecuencia\": 3.00, \"PrecioH\": 0.75, \"Cantidad\": 8}'),
(63, '2024-12-12', '16:38:39', 'Incremental', 'cpu', 'UPDATE', '{\"idCPU\": 6, \"Nombre\": \"AMD EPYC 7763\", \"Fabricante\": \"AMD\", \"Arquitectura\": \"x86-64\", \"Nucleos\": 64, \"Frecuencia\": 2.45, \"PrecioH\": 0.90, \"Cantidad\": 6}'),
(65, '2024-12-12', '16:38:39', 'Incremental', 'direccionip', 'UPDATE', '{\"idIp\": 1, \"Direccion\": \"192.8.9.1\", \"PrecioH\": 0.10, \"idPaaS\": null}'),
(66, '2024-12-12', '16:38:39', 'Incremental', 'direccionip', 'UPDATE', '{\"idIp\": 2, \"Direccion\": \"192.8.9.2\", \"PrecioH\": 0.10, \"idPaaS\": null}'),
(68, '2024-12-12', '16:38:39', 'Incremental', 'paas', 'UPDATE', '{\"idPaaS\": 1, \"Nombre\": \"PaaS1\", \"Estado\": \"Activo\", \"idSO\": 1}'),
(69, '2024-12-12', '16:38:39', 'Incremental', 'ram', 'UPDATE', '{\"idRAM\": 1, \"Nombre\": \"Kingston DDR4\", \"Fabricante\": \"Kingston\", \"Frecuencia\": 3200.00, \"Capacidad\": 16.00, \"Tipo\": \"DDR4\", \"PrecioH\": 0.10, \"Cantidad\": 48}'),
(70, '2024-12-12', '16:38:39', 'Incremental', 'ram', 'UPDATE', '{\"idRAM\": 2, \"Nombre\": \"Corsair Vengeance LPX\", \"Fabricante\": \"Corsair\", \"Frecuencia\": 3600.00, \"Capacidad\": 32.00, \"Tipo\": \"DDR4\", \"PrecioH\": 0.15, \"Cantidad\": 29}'),
(71, '2024-12-12', '16:38:39', 'Incremental', 'ram', 'UPDATE', '{\"idRAM\": 3, \"Nombre\": \"HyperX Fury DDR5\", \"Fabricante\": \"HyperX\", \"Frecuencia\": 4800.00, \"Capacidad\": 16.00, \"Tipo\": \"DDR5\", \"PrecioH\": 0.20, \"Cantidad\": 20}'),
(72, '2024-12-12', '16:38:39', 'Incremental', 'ram', 'UPDATE', '{\"idRAM\": 4, \"Nombre\": \"G.Skill Trident Z\", \"Fabricante\": \"G.Skill\", \"Frecuencia\": 3200.00, \"Capacidad\": 64.00, \"Tipo\": \"DDR4\", \"PrecioH\": 0.25, \"Cantidad\": 10}'),
(76, '2024-12-12', '16:38:39', 'Incremental', 'motor', 'UPDATE', '{\"idMotor\": 1, \"Nombre\": \"SaaSNumero1\", \"Version\": \"125\", \"PrecioH\": 0.39}'),
(77, '2024-12-12', '16:38:39', 'Incremental', 'motor', 'UPDATE', '{\"idMotor\": 2, \"Nombre\": \"SaaSNumero2\", \"Version\": \"1232\", \"PrecioH\": 0.34}'),
(78, '2024-12-12', '16:38:39', 'Incremental', 'motor', 'UPDATE', '{\"idMotor\": 3, \"Nombre\": \"SaaSNumero3\", \"Version\": \"125\", \"PrecioH\": 1.11}'),
(79, '2024-12-12', '16:38:39', 'Incremental', 'sistemaoperativo', 'UPDATE', '{\"idSO\": 1, \"Nombre\": \"Ubuntu\", \"Arquitectura\": \"x64\", \"Version\": \"22.04\", \"Tipo\": \"Linux\", \"MinAlmacenamiento\": 20.00, \"MinRAM\": 2.00, \"PrecioH\": 0.10}'),
(80, '2024-12-12', '16:38:39', 'Incremental', 'sistemaoperativo', 'UPDATE', '{\"idSO\": 2, \"Nombre\": \"Windows Server 2022\", \"Arquitectura\": \"x64\", \"Version\": \"21H2\", \"Tipo\": \"Windows\", \"MinAlmacenamiento\": 50.00, \"MinRAM\": 8.00, \"PrecioH\": 0.50}'),
(81, '2024-12-12', '16:38:39', 'Incremental', 'sistemaoperativo', 'UPDATE', '{\"idSO\": 3, \"Nombre\": \"Debian\", \"Arquitectura\": \"x64\", \"Version\": \"11\", \"Tipo\": \"Linux\", \"MinAlmacenamiento\": 25.00, \"MinRAM\": 2.00, \"PrecioH\": 0.08}'),
(82, '2024-12-12', '16:38:39', 'Incremental', 'sistemaoperativo', 'UPDATE', '{\"idSO\": 4, \"Nombre\": \"CentOS\", \"Arquitectura\": \"x64\", \"Version\": \"7\", \"Tipo\": \"Linux\", \"MinAlmacenamiento\": 30.00, \"MinRAM\": 4.00, \"PrecioH\": 0.15}'),
(83, '2024-12-12', '16:38:39', 'Incremental', 'sistemaoperativo', 'UPDATE', '{\"idSO\": 5, \"Nombre\": \"Red Hat Enterprise Linux\", \"Arquitectura\": \"x64\", \"Version\": \"8\", \"Tipo\": \"Linux\", \"MinAlmacenamiento\": 30.00, \"MinRAM\": 4.00, \"PrecioH\": 0.20}'),
(84, '2024-12-12', '16:38:39', 'Incremental', 'sistemaoperativo', 'UPDATE', '{\"idSO\": 6, \"Nombre\": \"Windows Server 2016\", \"Arquitectura\": \"x64\", \"Version\": \"1607\", \"Tipo\": \"Windows\", \"MinAlmacenamiento\": 40.00, \"MinRAM\": 4.00, \"PrecioH\": 0.40}'),
(86, '2024-12-12', '16:38:39', 'Incremental', 'usuario', 'UPDATE', '{\"idUsuario\": 0, \"Nombre\": \"Xisco Gaya\", \"Usuario\": \"xiiscogaya\", \"Email\": \"xiiscogaya@hotmail.com\", \"Telefono\": \"62378348\", \"Direccion\": \"C de bunyola, 6\", \"FechaRegistro\": \"2024-12-08 19:56:33\"}'),
(87, '2024-12-12', '16:38:39', 'Incremental', 'usuario', 'UPDATE', '{\"idUsuario\": 1, \"Nombre\": \"Toni\", \"Usuario\": \"tonii100\", \"Email\": \"toni100@hotmail.com\", \"Telefono\": \"3784848983\", \"Direccion\": \"Avinguda des Tren 20\", \"FechaRegistro\": \"2024-12-09 21:21:32\"}'),
(89, '2024-12-12', '16:38:39', 'Incremental', 'organizacion', 'UPDATE', '{\"idOrganizacion\": 3, \"Nombre\": \"Confi1\", \"Descripcion\": \"hola\", \"idCreador\": 0}'),
(90, '2024-12-12', '16:38:39', 'Incremental', 'organizacion', 'UPDATE', '{\"idOrganizacion\": 4, \"Nombre\": \"OrgaToni\", \"Descripcion\": \"Creada por Tonii100\", \"idCreador\": 1}'),
(91, '2024-12-12', '16:38:39', 'Incremental', 'organizacion', 'UPDATE', '{\"idOrganizacion\": 5, \"Nombre\": \"Rizem\", \"Descripcion\": \"Grupo\", \"idCreador\": 0}'),
(92, '2024-12-12', '16:38:39', 'Incremental', 'grupo', 'UPDATE', '{\"idGrupo\": 3, \"Nombre\": \"admin\", \"Descripcion\": \"Grupo con todos los permisos\", \"idOrg\": 4}'),
(93, '2024-12-12', '16:38:39', 'Incremental', 'grupo', 'UPDATE', '{\"idGrupo\": 4, \"Nombre\": \"admin\", \"Descripcion\": \"Grupo con todos los permisos\", \"idOrg\": 5}'),
(94, '2024-12-12', '16:38:39', 'Incremental', 'grupo', 'UPDATE', '{\"idGrupo\": 5, \"Nombre\": \"hola\", \"Descripcion\": \"hola\", \"idOrg\": 5}'),
(95, '2024-12-12', '16:38:39', 'Incremental', 'grupo', 'UPDATE', '{\"idGrupo\": 6, \"Nombre\": \"Confi1\", \"Descripcion\": \"ne\", \"idOrg\": 5}'),
(99, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 1, \"Nombre\": \"Contratar paas\", \"Descripcion\": \"Permite al usuario contratar nuevos servicios PaaS\"}'),
(100, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 2, \"Nombre\": \"Contratar saas\", \"Descripcion\": \"Permite al usuario contratar nuevos servicios SaaS\"}'),
(101, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 3, \"Nombre\": \"Modificar paas\", \"Descripcion\": \"Permite al usuario modificar los servicios PaaS contratados\"}'),
(102, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 4, \"Nombre\": \"Modificar saas\", \"Descripcion\": \"Permite al usuario modificar los servicios SaaS contratados\"}'),
(103, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 5, \"Nombre\": \"Eliminar paas\", \"Descripcion\": \"Permite al usuario eliminar servicios PaaS contratados\"}'),
(104, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 6, \"Nombre\": \"Eliminar saas\", \"Descripcion\": \"Permite al usuario eliminar servicios SaaS contratados\"}'),
(105, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 7, \"Nombre\": \"Añadir usuarios\", \"Descripcion\": \"Permite al usuario añadir usuarios a la organización\"}'),
(106, '2024-12-12', '16:38:39', 'Incremental', 'privilegio', 'UPDATE', '{\"idPrivilegio\": 8, \"Nombre\": \"Gestionar grupos\", \"Descripcion\": \"Permite al usuario gestionar y modificar los grupos de la organización\"}'),
(114, '2024-12-12', '16:38:39', 'Incremental', 'trabajador', 'UPDATE', '{\"idTrabajador\": 0, \"idUsuario\": 0}'),
(115, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 1}'),
(116, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 2}'),
(117, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 3}'),
(118, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 4}'),
(119, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 5}'),
(120, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 6}'),
(121, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 7}'),
(122, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 3, \"idPriv\": 8}'),
(123, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 1}'),
(124, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 2}'),
(125, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 3}'),
(126, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 4}'),
(127, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 5}'),
(128, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 6}'),
(129, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 7}'),
(130, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 4, \"idPriv\": 8}'),
(131, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 5, \"idPriv\": 3}'),
(132, '2024-12-12', '16:38:39', 'Incremental', 'r_grup_priv', 'UPDATE', '{\"idGrup\": 5, \"idPriv\": 4}'),
(146, '2024-12-12', '16:38:39', 'Incremental', 'r_paas_almacenamiento', 'UPDATE', '{\"idPaaS\": 1, \"idAlmacenamiento\": 1, \"Cantidad\": 4}'),
(147, '2024-12-12', '16:38:39', 'Incremental', 'r_paas_cpu', 'UPDATE', '{\"idPaaS\": 1, \"idCPU\": 2, \"Cantidad\": 20}'),
(148, '2024-12-12', '16:38:39', 'Incremental', 'r_paas_grup', 'UPDATE', '{\"idPaaS\": 1, \"idGrup\": 4}'),
(149, '2024-12-12', '16:38:39', 'Incremental', 'r_paas_ram', 'UPDATE', '{\"idPaaS\": 1, \"idRAM\": 1, \"Cantidad\": 2}'),
(150, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_grupo', 'UPDATE', '{\"idUsuario\": 0, \"idGrupo\": 4}'),
(151, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_grupo', 'UPDATE', '{\"idUsuario\": 0, \"idGrupo\": 5}'),
(152, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_grupo', 'UPDATE', '{\"idUsuario\": 1, \"idGrupo\": 3}'),
(153, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_grupo', 'UPDATE', '{\"idUsuario\": 1, \"idGrupo\": 5}'),
(154, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_grupo', 'UPDATE', '{\"idUsuario\": 1, \"idGrupo\": 6}'),
(157, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_org', 'UPDATE', '{\"idUsuario\": 0, \"idOrg\": 5}'),
(158, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_org', 'UPDATE', '{\"idUsuario\": 1, \"idOrg\": 3}'),
(159, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_org', 'UPDATE', '{\"idUsuario\": 1, \"idOrg\": 4}'),
(160, '2024-12-12', '16:38:39', 'Incremental', 'r_usuario_org', 'UPDATE', '{\"idUsuario\": 1, \"idOrg\": 5}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cpu`
--

CREATE TABLE `cpu` (
  `idCPU` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Fabricante` varchar(100) NOT NULL,
  `Arquitectura` varchar(50) NOT NULL,
  `Nucleos` int(11) NOT NULL,
  `Frecuencia` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cpu`
--

INSERT INTO `cpu` (`idCPU`, `Nombre`, `Fabricante`, `Arquitectura`, `Nucleos`, `Frecuencia`, `PrecioH`, `Cantidad`) VALUES
(1, 'Intel Core i5-11600K', 'Intel', 'x86-64', 6, 3.90, 0.25, 0),
(2, 'AMD Ryzen 7 5800X', 'AMD', 'x86-64', 8, 3.80, 0.30, 0),
(3, 'Intel Core i9-12900K', 'Intel', 'x86-64', 16, 3.20, 0.50, 8),
(4, 'AMD Ryzen Threadripper 3990X', 'AMD', 'x86-64', 64, 2.90, 1.00, 5),
(5, 'Intel Xeon Gold 6258R', 'Intel', 'x86-64', 28, 3.00, 0.75, 5),
(6, 'AMD EPYC 7763', 'AMD', 'x86-64', 64, 2.45, 0.90, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccionip`
--

CREATE TABLE `direccionip` (
  `idIp` int(11) NOT NULL,
  `Direccion` varchar(45) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccionip`
--

INSERT INTO `direccionip` (`idIp`, `Direccion`, `PrecioH`, `idPaaS`) VALUES
(1, '192.8.9.1', 0.10, 2),
(2, '192.8.9.2', 0.10, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `idGrupo` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idOrg` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo`
--

INSERT INTO `grupo` (`idGrupo`, `Nombre`, `Descripcion`, `idOrg`) VALUES
(4, 'admin', 'Grupo con todos los permisos', 5),
(5, 'hola', 'hola', 5),
(6, 'Confi1', 'ne', 5),
(7, 'admin', 'Grupo con todos los permisos', 4),
(8, 'grupo2', 'hola', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitorizacion`
--

CREATE TABLE `monitorizacion` (
  `idMonitorizacion` int(11) NOT NULL,
  `TipoEvento` varchar(50) DEFAULT NULL,
  `Descripcion` text DEFAULT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL,
  `idPaaS` int(11) DEFAULT NULL,
  `idSaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `motor`
--

CREATE TABLE `motor` (
  `idMotor` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Version` varchar(50) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `motor`
--

INSERT INTO `motor` (`idMotor`, `Nombre`, `Version`, `PrecioH`) VALUES
(1, 'SaaSNumero1', '125', 0.39),
(2, 'SaaSNumero2', '1232', 0.34),
(3, 'SaaSNumero3', '125', 1.11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organizacion`
--

CREATE TABLE `organizacion` (
  `idOrganizacion` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idCreador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `organizacion`
--

INSERT INTO `organizacion` (`idOrganizacion`, `Nombre`, `Descripcion`, `idCreador`) VALUES
(3, 'Confi1', 'hola', 0),
(4, 'Orga12', 'heuhe', 1),
(5, 'Rizem', 'Grupo', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paas`
--

CREATE TABLE `paas` (
  `idPaaS` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idSO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paas`
--

INSERT INTO `paas` (`idPaaS`, `Nombre`, `Estado`, `idSO`) VALUES
(1, 'PaaS1', 'Activo', NULL),
(2, 'Pass2', 'Activo', 2),
(3, 'pass3', 'Activo', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `privilegio`
--

CREATE TABLE `privilegio` (
  `idPrivilegio` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `privilegio`
--

INSERT INTO `privilegio` (`idPrivilegio`, `Nombre`, `Descripcion`) VALUES
(1, 'Contratar paas', 'Permite al usuario contratar nuevos servicios PaaS'),
(2, 'Contratar saas', 'Permite al usuario contratar nuevos servicios SaaS'),
(3, 'Modificar paas', 'Permite al usuario modificar los servicios PaaS contratados'),
(4, 'Modificar saas', 'Permite al usuario modificar los servicios SaaS contratados'),
(5, 'Eliminar paas', 'Permite al usuario eliminar servicios PaaS contratados'),
(6, 'Eliminar saas', 'Permite al usuario eliminar servicios SaaS contratados'),
(7, 'Añadir usuarios', 'Permite al usuario añadir usuarios a la organización'),
(8, 'Gestionar grupos', 'Permite al usuario gestionar y modificar los grupos de la organización');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ram`
--

CREATE TABLE `ram` (
  `idRAM` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Fabricante` varchar(100) NOT NULL,
  `Frecuencia` decimal(10,2) NOT NULL,
  `Capacidad` decimal(10,2) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ram`
--

INSERT INTO `ram` (`idRAM`, `Nombre`, `Fabricante`, `Frecuencia`, `Capacidad`, `Tipo`, `PrecioH`, `Cantidad`) VALUES
(1, 'Kingston DDR4', 'Kingston', 3200.00, 16.00, 'DDR4', 0.10, 48),
(2, 'Corsair Vengeance LPX', 'Corsair', 3600.00, 32.00, 'DDR4', 0.15, 25),
(3, 'HyperX Fury DDR5', 'HyperX', 4800.00, 16.00, 'DDR5', 0.20, 20),
(4, 'G.Skill Trident Z', 'G.Skill', 3200.00, 64.00, 'DDR4', 0.25, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_grup_priv`
--

CREATE TABLE `r_grup_priv` (
  `idGrup` int(11) NOT NULL,
  `idPriv` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_grup_priv`
--

INSERT INTO `r_grup_priv` (`idGrup`, `idPriv`) VALUES
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(4, 8),
(5, 3),
(5, 4),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(7, 5),
(7, 6),
(7, 7),
(7, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_paas_almacenamiento`
--

CREATE TABLE `r_paas_almacenamiento` (
  `idPaaS` int(11) NOT NULL,
  `idAlmacenamiento` int(11) NOT NULL,
  `Cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_paas_almacenamiento`
--

INSERT INTO `r_paas_almacenamiento` (`idPaaS`, `idAlmacenamiento`, `Cantidad`) VALUES
(1, 1, 4),
(2, 2, 3),
(3, 3, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_paas_cpu`
--

CREATE TABLE `r_paas_cpu` (
  `idPaaS` int(11) NOT NULL,
  `idCPU` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_paas_cpu`
--

INSERT INTO `r_paas_cpu` (`idPaaS`, `idCPU`, `Cantidad`) VALUES
(1, 2, 20),
(2, 5, 3),
(3, 3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_paas_grup`
--

CREATE TABLE `r_paas_grup` (
  `idPaaS` int(11) NOT NULL,
  `idGrup` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_paas_grup`
--

INSERT INTO `r_paas_grup` (`idPaaS`, `idGrup`) VALUES
(1, 4),
(3, 7),
(3, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_paas_ram`
--

CREATE TABLE `r_paas_ram` (
  `idPaaS` int(11) NOT NULL,
  `idRAM` int(11) NOT NULL,
  `Cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_paas_ram`
--

INSERT INTO `r_paas_ram` (`idPaaS`, `idRAM`, `Cantidad`) VALUES
(1, 1, 2),
(2, 2, 4),
(3, 4, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_saas_grup`
--

CREATE TABLE `r_saas_grup` (
  `idSaaS` int(11) NOT NULL,
  `idGrup` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_saas_grup`
--

INSERT INTO `r_saas_grup` (`idSaaS`, `idGrup`) VALUES
(1, 4),
(3, 7),
(3, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_usuario_grupo`
--

CREATE TABLE `r_usuario_grupo` (
  `idUsuario` int(11) NOT NULL,
  `idGrupo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_usuario_grupo`
--

INSERT INTO `r_usuario_grupo` (`idUsuario`, `idGrupo`) VALUES
(0, 4),
(0, 5),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(2, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_usuario_org`
--

CREATE TABLE `r_usuario_org` (
  `idUsuario` int(11) NOT NULL,
  `idOrg` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_usuario_org`
--

INSERT INTO `r_usuario_org` (`idUsuario`, `idOrg`) VALUES
(0, 5),
(1, 3),
(1, 4),
(1, 5),
(2, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saas`
--

CREATE TABLE `saas` (
  `idSaaS` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Usuario` varchar(50) DEFAULT NULL,
  `Contraseña` varchar(255) DEFAULT NULL,
  `idPaaS` int(11) DEFAULT NULL,
  `idMotor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `saas`
--

INSERT INTO `saas` (`idSaaS`, `Nombre`, `Usuario`, `Contraseña`, `idPaaS`, `idMotor`) VALUES
(1, 'SaaS1', 'Xisco Gaya', '$2y$10$YD7VEm47oR51rhERUsbtv.qvPXsbEol/.RWpzdzY5H6jWP5mn85Hq', 1, 2),
(2, 'SaaS1', 'Toni', '$2y$10$3pUOeyn.vMUb3Yx1Ag.F/e6oFRL/HmmLmIdUMOkXerymohxZb.z2y', 2, 2),
(3, 'saas2', 'Toni', '$2y$10$QtfmJEb3NP14uIRfM0pD8eKVntSXgznkXAvfME6ddoW3k5p8quAFi', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistemaoperativo`
--

CREATE TABLE `sistemaoperativo` (
  `idSO` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Arquitectura` varchar(50) NOT NULL,
  `Version` varchar(50) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sistemaoperativo`
--

INSERT INTO `sistemaoperativo` (`idSO`, `Nombre`, `Arquitectura`, `Version`, `Tipo`, `PrecioH`) VALUES
(2, 'Windows Server 2022', 'x64', '21H2', 'Windows', 0.50),
(3, 'Debian', 'x64', '11', 'Linux', 0.08),
(4, 'CentOS', 'x64', '7', 'Linux', 0.15),
(5, 'Red Hat Enterprise Linux', 'x64', '8', 'Linux', 0.20),
(6, 'Windows Server 2016', 'x64', '1607', 'Windows', 0.40),
(7, 'NuevoSO', '82', '14', 'Linux', 0.10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `idTrabajador` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajador`
--

INSERT INTO `trabajador` (`idTrabajador`, `idUsuario`) VALUES
(0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `Direccion` text DEFAULT NULL,
  `FechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `Nombre`, `Usuario`, `Email`, `Telefono`, `Contraseña`, `Direccion`, `FechaRegistro`) VALUES
(0, 'Xisco Gaya', 'xiiscogaya', 'xiiscogaya@hotmail.com', '62378348', '$2y$10$FBtDXX02Lg2QnzudJzqpauMCRejRwS3Gk1kJXYInc7JOHkErOHFeO', 'C de bunyola, 6', '2024-12-08 19:56:33'),
(1, 'Toni', 'tonii100', 'toni100@hotmail.com', '3784848983', '$2y$10$2HGueF9aisFDS7v3ZC72sOZ7HTajGS1DReQwW.kgTQrZuJrsA161y', 'Avinguda des Tren 20', '2024-12-09 21:21:32'),
(2, 'Jaume Aleix', 'jaume200', 'jaume200@hotmail.com', '6348278674', '$2y$10$haTABv5R8G54FeVjgpaqBOnUFZXppzC6cR9B.QUhIQwe./Kn/b91K', 'Avinguda des Tren 36', '2024-12-12 17:29:22');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `almacenamiento`
--
ALTER TABLE `almacenamiento`
  ADD PRIMARY KEY (`idAlmacenamiento`);

--
-- Indices de la tabla `backup`
--
ALTER TABLE `backup`
  ADD PRIMARY KEY (`idBackup`),
  ADD KEY `idx_fecha_tabla` (`Fecha`,`Tabla`);

--
-- Indices de la tabla `cpu`
--
ALTER TABLE `cpu`
  ADD PRIMARY KEY (`idCPU`);

--
-- Indices de la tabla `direccionip`
--
ALTER TABLE `direccionip`
  ADD PRIMARY KEY (`idIp`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`idGrupo`),
  ADD KEY `idOrg` (`idOrg`);

--
-- Indices de la tabla `monitorizacion`
--
ALTER TABLE `monitorizacion`
  ADD PRIMARY KEY (`idMonitorizacion`),
  ADD KEY `idPaaS` (`idPaaS`),
  ADD KEY `idSaaS` (`idSaaS`);

--
-- Indices de la tabla `motor`
--
ALTER TABLE `motor`
  ADD PRIMARY KEY (`idMotor`);

--
-- Indices de la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD PRIMARY KEY (`idOrganizacion`),
  ADD KEY `idCreador` (`idCreador`);

--
-- Indices de la tabla `paas`
--
ALTER TABLE `paas`
  ADD PRIMARY KEY (`idPaaS`),
  ADD KEY `idSO` (`idSO`);

--
-- Indices de la tabla `privilegio`
--
ALTER TABLE `privilegio`
  ADD PRIMARY KEY (`idPrivilegio`);

--
-- Indices de la tabla `ram`
--
ALTER TABLE `ram`
  ADD PRIMARY KEY (`idRAM`);

--
-- Indices de la tabla `r_grup_priv`
--
ALTER TABLE `r_grup_priv`
  ADD PRIMARY KEY (`idGrup`,`idPriv`),
  ADD KEY `idPriv` (`idPriv`);

--
-- Indices de la tabla `r_paas_almacenamiento`
--
ALTER TABLE `r_paas_almacenamiento`
  ADD PRIMARY KEY (`idPaaS`,`idAlmacenamiento`),
  ADD KEY `idAlmacenamiento` (`idAlmacenamiento`);

--
-- Indices de la tabla `r_paas_cpu`
--
ALTER TABLE `r_paas_cpu`
  ADD PRIMARY KEY (`idPaaS`,`idCPU`),
  ADD KEY `idCPU` (`idCPU`);

--
-- Indices de la tabla `r_paas_grup`
--
ALTER TABLE `r_paas_grup`
  ADD PRIMARY KEY (`idPaaS`,`idGrup`),
  ADD KEY `idGrup` (`idGrup`);

--
-- Indices de la tabla `r_paas_ram`
--
ALTER TABLE `r_paas_ram`
  ADD PRIMARY KEY (`idPaaS`,`idRAM`),
  ADD KEY `idRAM` (`idRAM`);

--
-- Indices de la tabla `r_saas_grup`
--
ALTER TABLE `r_saas_grup`
  ADD PRIMARY KEY (`idSaaS`,`idGrup`),
  ADD KEY `idGrup` (`idGrup`);

--
-- Indices de la tabla `r_usuario_grupo`
--
ALTER TABLE `r_usuario_grupo`
  ADD PRIMARY KEY (`idUsuario`,`idGrupo`),
  ADD KEY `idGrupo` (`idGrupo`);

--
-- Indices de la tabla `r_usuario_org`
--
ALTER TABLE `r_usuario_org`
  ADD PRIMARY KEY (`idUsuario`,`idOrg`),
  ADD KEY `idOrg` (`idOrg`);

--
-- Indices de la tabla `saas`
--
ALTER TABLE `saas`
  ADD PRIMARY KEY (`idSaaS`),
  ADD KEY `idPaaS` (`idPaaS`),
  ADD KEY `idMotor` (`idMotor`);

--
-- Indices de la tabla `sistemaoperativo`
--
ALTER TABLE `sistemaoperativo`
  ADD PRIMARY KEY (`idSO`);

--
-- Indices de la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD PRIMARY KEY (`idTrabajador`),
  ADD KEY `idUsuario` (`idUsuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `Usuario` (`Usuario`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `backup`
--
ALTER TABLE `backup`
  MODIFY `idBackup` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `direccionip`
--
ALTER TABLE `direccionip`
  ADD CONSTRAINT `direccionip_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE SET NULL;

--
-- Filtros para la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD CONSTRAINT `grupo_ibfk_1` FOREIGN KEY (`idOrg`) REFERENCES `organizacion` (`idOrganizacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `monitorizacion`
--
ALTER TABLE `monitorizacion`
  ADD CONSTRAINT `monitorizacion_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitorizacion_ibfk_2` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`) ON DELETE CASCADE;

--
-- Filtros para la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD CONSTRAINT `organizacion_ibfk_1` FOREIGN KEY (`idCreador`) REFERENCES `usuario` (`idUsuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `paas`
--
ALTER TABLE `paas`
  ADD CONSTRAINT `paas_ibfk_1` FOREIGN KEY (`idSO`) REFERENCES `sistemaoperativo` (`idSO`) ON DELETE SET NULL;

--
-- Filtros para la tabla `r_grup_priv`
--
ALTER TABLE `r_grup_priv`
  ADD CONSTRAINT `r_grup_priv_ibfk_1` FOREIGN KEY (`idGrup`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_grup_priv_ibfk_2` FOREIGN KEY (`idPriv`) REFERENCES `privilegio` (`idPrivilegio`);

--
-- Filtros para la tabla `r_paas_almacenamiento`
--
ALTER TABLE `r_paas_almacenamiento`
  ADD CONSTRAINT `r_paas_almacenamiento_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_paas_almacenamiento_ibfk_2` FOREIGN KEY (`idAlmacenamiento`) REFERENCES `almacenamiento` (`idAlmacenamiento`) ON DELETE CASCADE;

--
-- Filtros para la tabla `r_paas_cpu`
--
ALTER TABLE `r_paas_cpu`
  ADD CONSTRAINT `r_paas_cpu_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_paas_cpu_ibfk_2` FOREIGN KEY (`idCPU`) REFERENCES `cpu` (`idCPU`) ON DELETE CASCADE;

--
-- Filtros para la tabla `r_paas_grup`
--
ALTER TABLE `r_paas_grup`
  ADD CONSTRAINT `r_paas_grup_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_paas_grup_ibfk_2` FOREIGN KEY (`idGrup`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `r_paas_ram`
--
ALTER TABLE `r_paas_ram`
  ADD CONSTRAINT `r_paas_ram_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_paas_ram_ibfk_2` FOREIGN KEY (`idRAM`) REFERENCES `ram` (`idRAM`) ON DELETE CASCADE;

--
-- Filtros para la tabla `r_saas_grup`
--
ALTER TABLE `r_saas_grup`
  ADD CONSTRAINT `r_saas_grup_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_saas_grup_ibfk_2` FOREIGN KEY (`idGrup`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `r_usuario_grupo`
--
ALTER TABLE `r_usuario_grupo`
  ADD CONSTRAINT `r_usuario_grupo_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_usuario_grupo_ibfk_2` FOREIGN KEY (`idGrupo`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `r_usuario_org`
--
ALTER TABLE `r_usuario_org`
  ADD CONSTRAINT `r_usuario_org_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `r_usuario_org_ibfk_2` FOREIGN KEY (`idOrg`) REFERENCES `organizacion` (`idOrganizacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `saas`
--
ALTER TABLE `saas`
  ADD CONSTRAINT `saas_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE SET NULL,
  ADD CONSTRAINT `saas_ibfk_2` FOREIGN KEY (`idMotor`) REFERENCES `motor` (`idMotor`) ON DELETE SET NULL;

--
-- Filtros para la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD CONSTRAINT `trabajador_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `backup_incremental_event` ON SCHEDULE EVERY 1 DAY STARTS '2024-12-12 16:39:26' ON COMPLETION NOT PRESERVE ENABLE DO CALL realizar_backup_incremental()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
