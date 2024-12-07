-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-12-2024 a las 17:56:20
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
  `Nombre` varchar(255) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `VelocidadLectura` decimal(10,2) NOT NULL,
  `VelocidadEscritura` decimal(10,2) NOT NULL,
  `Capacidad` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `almacenamiento`
--

INSERT INTO `almacenamiento` (`idAlmacenamiento`, `Nombre`, `Tipo`, `VelocidadLectura`, `VelocidadEscritura`, `Capacidad`, `PrecioH`, `Estado`, `idPaaS`) VALUES
(1, 'SSD Samsung EVO 1TB', 'SSD', 550.00, 520.00, 1024.00, 0.25, 'Activo', 1),
(2, 'HDD Seagate 2TB', 'HDD', 150.00, 140.00, 2048.00, 0.10, 'Activo', 2),
(3, 'NVMe WD Black 1TB', 'NVMe', 3500.00, 3000.00, 1024.00, 0.50, 'Activo', 3),
(4, 'SATA Toshiba 1TB', 'SATA', 200.00, 180.00, 1024.00, 0.15, 'En pruebas', 4);

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

--
-- Volcado de datos para la tabla `backup`
--

INSERT INTO `backup` (`idBackup`, `Fecha`, `Hora`, `Tipo`, `Datos`, `idSaaS`, `idPaaS`) VALUES
(1, '2024-12-06', '01:00:00', 'Incremental', '{\"backup\":\"SaaS Configurations Backup\"}', 1, NULL),
(2, '2024-12-06', '02:00:00', 'Completo', '{\"backup\":\"PaaS Server Backup\"}', NULL, 2),
(3, '2024-12-06', '03:30:00', 'Incremental', '{\"backup\":\"Database Backup\"}', 3, NULL),
(4, '2024-12-06', '04:15:00', 'Completo', '{\"backup\":\"Full System Backup\"}', NULL, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracionsaas`
--

CREATE TABLE `configuracionsaas` (
  `idConfiguracion` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `VersionMotor` varchar(100) NOT NULL,
  `Admin` varchar(255) NOT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `idSaaS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracionsaas`
--

INSERT INTO `configuracionsaas` (`idConfiguracion`, `Nombre`, `VersionMotor`, `Admin`, `Contraseña`, `idSaaS`) VALUES
(1, 'MySQL Advanced', '8.0.25', 'admin.mysql', 'mysql_secure123', 1),
(2, 'Apache WebServer Config', '2.4.54', 'admin.apache', 'apache_secure123', 2),
(3, 'PostgreSQL Advanced', '14.5', 'admin.postgres', 'postgres_secure123', 3),
(4, 'Nginx Load Balancer', '1.22', 'admin.nginx', 'nginx_secure123', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cpu`
--

CREATE TABLE `cpu` (
  `idCPU` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Fabricante` varchar(255) NOT NULL,
  `Arquitectura` varchar(50) NOT NULL,
  `Nucleos` int(11) NOT NULL,
  `Frecuencia` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cpu`
--

INSERT INTO `cpu` (`idCPU`, `Nombre`, `Fabricante`, `Arquitectura`, `Nucleos`, `Frecuencia`, `PrecioH`, `Estado`, `idPaaS`) VALUES
(1, 'Intel Xeon Gold 6226R', 'Intel', 'x64', 16, 2.90, 0.40, 'Activo', 1),
(2, 'AMD EPYC 7302P', 'AMD', 'x64', 16, 3.00, 0.45, 'Activo', 2),
(3, 'Intel i9-12900K', 'Intel', 'x64', 24, 3.20, 0.60, 'Activo', 3),
(4, 'AMD Threadripper 3970X', 'AMD', 'x64', 32, 3.70, 0.75, 'En pruebas', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccionip`
--

CREATE TABLE `direccionip` (
  `idIp` int(11) NOT NULL,
  `Direccion` varchar(45) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccionip`
--

INSERT INTO `direccionip` (`idIp`, `Direccion`, `PrecioH`, `Estado`, `idPaaS`) VALUES
(1, '10.0.0.1', 0.05, 'Reservada', 1),
(2, '10.0.0.2', 0.05, 'Disponible', 2),
(3, '10.0.0.3', 0.05, 'En uso', 3),
(4, '10.0.0.4', 0.05, 'Reservada', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `idGrupo` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `grupo`
--

INSERT INTO `grupo` (`idGrupo`, `Nombre`, `Descripcion`) VALUES
(1, 'Administradores', 'Usuarios con control total de la organización.'),
(2, 'Gestores de Servicios', 'Usuarios encargados de la administración de servicios.'),
(3, 'Usuarios Básicos', 'Usuarios con permisos básicos para acceder y usar servicios.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitorizacion`
--

CREATE TABLE `monitorizacion` (
  `idMonitorizacion` int(11) NOT NULL,
  `TipoEvento` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `monitorizacion`
--

INSERT INTO `monitorizacion` (`idMonitorizacion`, `TipoEvento`, `Descripcion`, `Fecha`, `Hora`) VALUES
(1, 'CPU Overload', 'El uso de CPU alcanzó el 95% en el servidor.', '2024-12-05', '14:30:00'),
(2, 'RAM Utilization High', 'El uso de RAM superó el 90% en el clúster.', '2024-12-06', '10:00:00'),
(3, 'Disk Failure Alert', 'Se detectó un error en el disco 3.', '2024-12-06', '11:45:00'),
(4, 'Network Down', 'Interrupción en la red del datacenter.', '2024-12-06', '12:30:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `organizacion`
--

CREATE TABLE `organizacion` (
  `idOrganizacion` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idAdmin` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `organizacion`
--

INSERT INTO `organizacion` (`idOrganizacion`, `Nombre`, `Descripcion`, `idAdmin`) VALUES
(1, 'Tech Innovators', 'Organización centrada en soluciones tecnológicas.', 1),
(2, 'Cloud Solutions Ltd.', 'Empresa de servicios en la nube para negocios medianos.', 2),
(3, 'Global Hosting Inc.', 'Proveedor global de servicios de hosting.', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paas`
--

CREATE TABLE `paas` (
  `idPaaS` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idSO` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paas`
--

INSERT INTO `paas` (`idPaaS`, `Nombre`, `Tipo`, `Estado`, `idSO`) VALUES
(1, 'Ubuntu Virtual Machine', 'VM', 'Activo', 1),
(2, 'Windows Server 2022', 'VM', 'Activo', 2),
(3, 'Kubernetes Cluster', 'Cluster', 'En pruebas', 3),
(4, 'AWS S3 Storage', 'Almacenamiento', 'Activo', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `privilegio`
--

CREATE TABLE `privilegio` (
  `idPrivilegio` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idSaaS` int(11) DEFAULT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `privilegio`
--

INSERT INTO `privilegio` (`idPrivilegio`, `Nombre`, `Descripcion`, `idSaaS`, `idPaaS`) VALUES
(1, 'Crear Servicio', 'Permite crear nuevos servicios.', NULL, NULL),
(2, 'Modificar Configuración', 'Permite modificar configuraciones de servicios.', NULL, NULL),
(3, 'Ver Estadísticas', 'Permite visualizar estadísticas del uso.', NULL, NULL),
(4, 'Asignar Recursos', 'Permite asignar recursos a usuarios y servicios.', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ram`
--

CREATE TABLE `ram` (
  `idRAM` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Fabricante` varchar(255) NOT NULL,
  `Frecuencia` decimal(10,2) NOT NULL,
  `Capacidad` decimal(10,2) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idPaaS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ram`
--

INSERT INTO `ram` (`idRAM`, `Nombre`, `Fabricante`, `Frecuencia`, `Capacidad`, `Tipo`, `PrecioH`, `Estado`, `idPaaS`) VALUES
(1, 'Kingston HyperX', 'Kingston', 3200.00, 16.00, 'DDR4', 0.10, 'Activo', 1),
(2, 'Corsair Vengeance', 'Corsair', 3600.00, 32.00, 'DDR4', 0.20, 'Activo', 2),
(3, 'G.Skill TridentZ', 'G.Skill', 4000.00, 64.00, 'DDR5', 0.35, 'Activo', 3),
(4, 'Samsung DDR4', 'Samsung', 3000.00, 8.00, 'DDR4', 0.08, 'En pruebas', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_grup_priv`
--

CREATE TABLE `r_grup_priv` (
  `idGrupo` int(11) NOT NULL,
  `idPrivilegio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_grup_priv`
--

INSERT INTO `r_grup_priv` (`idGrupo`, `idPrivilegio`) VALUES
(1, 1),
(1, 4),
(2, 2),
(2, 3),
(3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_org_grup`
--

CREATE TABLE `r_org_grup` (
  `idOrganizacion` int(11) NOT NULL,
  `idGrupo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_org_grup`
--

INSERT INTO `r_org_grup` (`idOrganizacion`, `idGrupo`) VALUES
(1, 1),
(1, 2),
(2, 2),
(3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_paas_monit`
--

CREATE TABLE `r_paas_monit` (
  `idPaaS` int(11) NOT NULL,
  `idMonitorizacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_paas_monit`
--

INSERT INTO `r_paas_monit` (`idPaaS`, `idMonitorizacion`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_saas_monit`
--

CREATE TABLE `r_saas_monit` (
  `idSaaS` int(11) NOT NULL,
  `idMonitorizacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_saas_monit`
--

INSERT INTO `r_saas_monit` (`idSaaS`, `idMonitorizacion`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `r_usuario_org`
--

CREATE TABLE `r_usuario_org` (
  `idUsuario` int(11) NOT NULL,
  `idOrganizacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `r_usuario_org`
--

INSERT INTO `r_usuario_org` (`idUsuario`, `idOrganizacion`) VALUES
(1, 1),
(2, 2),
(3, 3),
(5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saas`
--

CREATE TABLE `saas` (
  `idSaaS` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `TipoServicio` varchar(100) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idPaaS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `saas`
--

INSERT INTO `saas` (`idSaaS`, `Nombre`, `TipoServicio`, `Estado`, `idPaaS`) VALUES
(1, 'MySQL Database', 'Base de datos', 'Activo', 1),
(2, 'Apache Server', 'Servidor Web', 'Activo', 2),
(3, 'PostgreSQL Database', 'Base de datos', 'En pruebas', 3),
(4, 'Nginx Load Balancer', 'Servidor Web', 'Activo', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistemaoperativo`
--

CREATE TABLE `sistemaoperativo` (
  `idSO` int(11) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Arquitectura` varchar(50) NOT NULL,
  `Version` varchar(50) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `MinAlmacenamiento` decimal(10,2) NOT NULL,
  `MinRAM` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sistemaoperativo`
--

INSERT INTO `sistemaoperativo` (`idSO`, `Nombre`, `Arquitectura`, `Version`, `Tipo`, `MinAlmacenamiento`, `MinRAM`, `PrecioH`, `Estado`) VALUES
(1, 'Ubuntu 20.04', 'x64', '20.04', 'Linux', 20.00, 4.00, 0.50, 'Activo'),
(2, 'Windows Server 2019', 'x64', '2019', 'Windows', 50.00, 8.00, 1.00, 'Activo'),
(3, 'Debian 11', 'x64', '11', 'Linux', 25.00, 4.00, 0.40, 'Activo'),
(4, 'CentOS 8', 'x64', '8', 'Linux', 30.00, 6.00, 0.45, 'En pruebas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajador`
--

CREATE TABLE `trabajador` (
  `idTrabajador` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `Nombre` varchar(255) DEFAULT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `Direccion` varchar(255) DEFAULT NULL,
  `FechaRegistro` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `Nombre`, `Usuario`, `Email`, `Telefono`, `Contraseña`, `Direccion`, `FechaRegistro`) VALUES
(1, 'Alice Admin', 'alice', 'alice@techinnovators.com', '123456789', '$2y$10$fXFjWD6z2zPDcfzk7dCRb.29ACWmhSx38HjkiGIGOyBViyxYCvpZa', 'Calle Innovación 123', '2024-12-01'),
(2, 'Bob Manager', 'bob_manager', 'bob@cloudsolutions.com', '987654321', '$2y$10$mt224oVuR8mfTCjsOHDFG.qK/fEIp8.Ucfz4G9vk8ktwbuQ6B1IUm', 'Calle Soluciones 456', '2024-12-02'),
(3, 'Charlie User', 'charlie_user', 'charlie@globalhosting.com', '456789123', '$2y$10$zMLvQ5gRkkOQxudvQKJSheXjIglBLufu741AXViOfR59bnvFwAp76', 'Calle Global 789', '2024-12-03'),
(5, 'John Admin', 'admin_john', 'admin6.john@example.com', '123456789', '$2y$10$hf8LCmKgX2Fc.1Wou2RmnuT8t4qMDr1zL7Fq7D4aC41nSDUgEKZ9e', '123 Calle Administración', '2024-12-07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `almacenamiento`
--
ALTER TABLE `almacenamiento`
  ADD PRIMARY KEY (`idAlmacenamiento`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `backup`
--
ALTER TABLE `backup`
  ADD PRIMARY KEY (`idBackup`),
  ADD KEY `idSaaS` (`idSaaS`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `configuracionsaas`
--
ALTER TABLE `configuracionsaas`
  ADD PRIMARY KEY (`idConfiguracion`),
  ADD KEY `idSaaS` (`idSaaS`);

--
-- Indices de la tabla `cpu`
--
ALTER TABLE `cpu`
  ADD PRIMARY KEY (`idCPU`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `direccionip`
--
ALTER TABLE `direccionip`
  ADD PRIMARY KEY (`idIp`),
  ADD UNIQUE KEY `Direccion` (`Direccion`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`idGrupo`);

--
-- Indices de la tabla `monitorizacion`
--
ALTER TABLE `monitorizacion`
  ADD PRIMARY KEY (`idMonitorizacion`);

--
-- Indices de la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD PRIMARY KEY (`idOrganizacion`),
  ADD KEY `idAdmin` (`idAdmin`);

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
  ADD PRIMARY KEY (`idPrivilegio`),
  ADD KEY `idSaaS` (`idSaaS`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `ram`
--
ALTER TABLE `ram`
  ADD PRIMARY KEY (`idRAM`),
  ADD KEY `idPaaS` (`idPaaS`);

--
-- Indices de la tabla `r_grup_priv`
--
ALTER TABLE `r_grup_priv`
  ADD PRIMARY KEY (`idGrupo`,`idPrivilegio`),
  ADD KEY `idPrivilegio` (`idPrivilegio`);

--
-- Indices de la tabla `r_org_grup`
--
ALTER TABLE `r_org_grup`
  ADD PRIMARY KEY (`idOrganizacion`,`idGrupo`),
  ADD KEY `idGrupo` (`idGrupo`);

--
-- Indices de la tabla `r_paas_monit`
--
ALTER TABLE `r_paas_monit`
  ADD PRIMARY KEY (`idPaaS`,`idMonitorizacion`),
  ADD KEY `idMonitorizacion` (`idMonitorizacion`);

--
-- Indices de la tabla `r_saas_monit`
--
ALTER TABLE `r_saas_monit`
  ADD PRIMARY KEY (`idSaaS`,`idMonitorizacion`),
  ADD KEY `idMonitorizacion` (`idMonitorizacion`);

--
-- Indices de la tabla `r_usuario_org`
--
ALTER TABLE `r_usuario_org`
  ADD PRIMARY KEY (`idUsuario`,`idOrganizacion`),
  ADD KEY `idOrganizacion` (`idOrganizacion`);

--
-- Indices de la tabla `saas`
--
ALTER TABLE `saas`
  ADD PRIMARY KEY (`idSaaS`),
  ADD KEY `idPaaS` (`idPaaS`);

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
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `almacenamiento`
--
ALTER TABLE `almacenamiento`
  ADD CONSTRAINT `almacenamiento_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `backup`
--
ALTER TABLE `backup`
  ADD CONSTRAINT `backup_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`),
  ADD CONSTRAINT `backup_ibfk_2` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `configuracionsaas`
--
ALTER TABLE `configuracionsaas`
  ADD CONSTRAINT `configuracionsaas_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`);

--
-- Filtros para la tabla `cpu`
--
ALTER TABLE `cpu`
  ADD CONSTRAINT `cpu_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `direccionip`
--
ALTER TABLE `direccionip`
  ADD CONSTRAINT `direccionip_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `organizacion`
--
ALTER TABLE `organizacion`
  ADD CONSTRAINT `organizacion_ibfk_1` FOREIGN KEY (`idAdmin`) REFERENCES `usuario` (`idUsuario`);

--
-- Filtros para la tabla `paas`
--
ALTER TABLE `paas`
  ADD CONSTRAINT `paas_ibfk_1` FOREIGN KEY (`idSO`) REFERENCES `sistemaoperativo` (`idSO`);

--
-- Filtros para la tabla `privilegio`
--
ALTER TABLE `privilegio`
  ADD CONSTRAINT `privilegio_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`),
  ADD CONSTRAINT `privilegio_ibfk_2` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `ram`
--
ALTER TABLE `ram`
  ADD CONSTRAINT `ram_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `r_grup_priv`
--
ALTER TABLE `r_grup_priv`
  ADD CONSTRAINT `r_grup_priv_ibfk_1` FOREIGN KEY (`idGrupo`) REFERENCES `grupo` (`idGrupo`),
  ADD CONSTRAINT `r_grup_priv_ibfk_2` FOREIGN KEY (`idPrivilegio`) REFERENCES `privilegio` (`idPrivilegio`);

--
-- Filtros para la tabla `r_org_grup`
--
ALTER TABLE `r_org_grup`
  ADD CONSTRAINT `r_org_grup_ibfk_1` FOREIGN KEY (`idOrganizacion`) REFERENCES `organizacion` (`idOrganizacion`),
  ADD CONSTRAINT `r_org_grup_ibfk_2` FOREIGN KEY (`idGrupo`) REFERENCES `grupo` (`idGrupo`);

--
-- Filtros para la tabla `r_paas_monit`
--
ALTER TABLE `r_paas_monit`
  ADD CONSTRAINT `r_paas_monit_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`),
  ADD CONSTRAINT `r_paas_monit_ibfk_2` FOREIGN KEY (`idMonitorizacion`) REFERENCES `monitorizacion` (`idMonitorizacion`);

--
-- Filtros para la tabla `r_saas_monit`
--
ALTER TABLE `r_saas_monit`
  ADD CONSTRAINT `r_saas_monit_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`),
  ADD CONSTRAINT `r_saas_monit_ibfk_2` FOREIGN KEY (`idMonitorizacion`) REFERENCES `monitorizacion` (`idMonitorizacion`);

--
-- Filtros para la tabla `r_usuario_org`
--
ALTER TABLE `r_usuario_org`
  ADD CONSTRAINT `r_usuario_org_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`),
  ADD CONSTRAINT `r_usuario_org_ibfk_2` FOREIGN KEY (`idOrganizacion`) REFERENCES `organizacion` (`idOrganizacion`);

--
-- Filtros para la tabla `saas`
--
ALTER TABLE `saas`
  ADD CONSTRAINT `saas_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`);

--
-- Filtros para la tabla `trabajador`
--
ALTER TABLE `trabajador`
  ADD CONSTRAINT `trabajador_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
