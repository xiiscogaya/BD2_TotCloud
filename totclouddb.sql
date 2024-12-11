-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-12-2024 a las 18:05:23
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
(2, 'Seagate Barracuda', 'HDD', 150.00, 140.00, 2000.00, 0.10, 53),
(3, 'Western Digital Blue SN570', 'NVMe', 3500.00, 3000.00, 512.00, 0.25, 15),
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
  `Datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`Datos`)),
  `idSaaS` int(11) DEFAULT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(3, 'Intel Core i9-12900K', 'Intel', 'x86-64', 16, 3.20, 0.50, 10),
(4, 'AMD Ryzen Threadripper 3990X', 'AMD', 'x86-64', 64, 2.90, 1.00, 5),
(5, 'Intel Xeon Gold 6258R', 'Intel', 'x86-64', 28, 3.00, 0.75, 8),
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
(1, '192.8.9.1', 0.10, NULL),
(2, '192.8.9.2', 0.10, NULL);

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
(3, 'admin', 'Grupo con todos los permisos', 4),
(4, 'admin', 'Grupo con todos los permisos', 5),
(5, 'hola', 'hola', 5),
(6, 'Confi1', 'ne', 5);

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
(4, 'OrgaToni', 'Creada por Tonii100', 1),
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
(1, 'PaaS1', 'Activo', 1);

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
(2, 'Corsair Vengeance LPX', 'Corsair', 3600.00, 32.00, 'DDR4', 0.15, 29),
(3, 'HyperX Fury DDR5', 'HyperX', 4800.00, 16.00, 'DDR5', 0.20, 20),
(4, 'G.Skill Trident Z', 'G.Skill', 3200.00, 64.00, 'DDR4', 0.25, 10);

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
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(4, 5),
(4, 6),
(4, 7),
(4, 8),
(5, 3),
(5, 4);

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
(1, 1, 4);

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
(1, 2, 20);

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
(1, 4);

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
(1, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_saas_grup`
--

CREATE TABLE `r_saas_grup` (
  `idSaaS` int(11) NOT NULL,
  `idGrup` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 3),
(1, 5),
(1, 6);

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
(1, 5);

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
  `MinAlmacenamiento` decimal(10,2) NOT NULL,
  `MinRAM` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sistemaoperativo`
--

INSERT INTO `sistemaoperativo` (`idSO`, `Nombre`, `Arquitectura`, `Version`, `Tipo`, `MinAlmacenamiento`, `MinRAM`, `PrecioH`) VALUES
(1, 'Ubuntu', 'x64', '22.04', 'Linux', 20.00, 2.00, 0.10),
(2, 'Windows Server 2022', 'x64', '21H2', 'Windows', 50.00, 8.00, 0.50),
(3, 'Debian', 'x64', '11', 'Linux', 25.00, 2.00, 0.08),
(4, 'CentOS', 'x64', '7', 'Linux', 30.00, 4.00, 0.15),
(5, 'Red Hat Enterprise Linux', 'x64', '8', 'Linux', 30.00, 4.00, 0.20),
(6, 'Windows Server 2016', 'x64', '1607', 'Windows', 40.00, 4.00, 0.40);

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
(1, 'Toni', 'tonii100', 'toni100@hotmail.com', '3784848983', '$2y$10$2HGueF9aisFDS7v3ZC72sOZ7HTajGS1DReQwW.kgTQrZuJrsA161y', 'Avinguda des Tren 20', '2024-12-09 21:21:32');

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
  ADD KEY `idSaaS` (`idSaaS`),
  ADD KEY `idPaaS` (`idPaaS`);

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
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `backup`
--
ALTER TABLE `backup`
  ADD CONSTRAINT `backup_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`) ON DELETE CASCADE,
  ADD CONSTRAINT `backup_ibfk_2` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
