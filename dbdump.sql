-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: totclouddb
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `almacenamiento`
--

DROP TABLE IF EXISTS `almacenamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `almacenamiento` (
  `idAlmacenamiento` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `VelocidadLectura` decimal(10,2) NOT NULL,
  `VelocidadEscritura` decimal(10,2) NOT NULL,
  `Capacidad` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  PRIMARY KEY (`idAlmacenamiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `almacenamiento`
--

LOCK TABLES `almacenamiento` WRITE;
/*!40000 ALTER TABLE `almacenamiento` DISABLE KEYS */;
INSERT INTO `almacenamiento` VALUES (1,'Samsung 970 EVO Plus','SSD',3500.00,3300.00,1000.00,0.20,24),(2,'Seagate Barracuda','HDD',150.00,140.00,2000.00,0.10,53),(3,'Western Digital Blue SN570','NVMe',3500.00,3000.00,512.00,0.25,15),(4,'Kingston A400','SATA',500.00,450.00,256.00,0.05,40);
/*!40000 ALTER TABLE `almacenamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup`
--

DROP TABLE IF EXISTS `backup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup` (
  `idBackup` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Datos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`Datos`)),
  `idSaaS` int(11) DEFAULT NULL,
  `idPaaS` int(11) DEFAULT NULL,
  PRIMARY KEY (`idBackup`),
  KEY `idSaaS` (`idSaaS`),
  KEY `idPaaS` (`idPaaS`),
  CONSTRAINT `backup_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`) ON DELETE CASCADE,
  CONSTRAINT `backup_ibfk_2` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup`
--

LOCK TABLES `backup` WRITE;
/*!40000 ALTER TABLE `backup` DISABLE KEYS */;
/*!40000 ALTER TABLE `backup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cpu`
--

DROP TABLE IF EXISTS `cpu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cpu` (
  `idCPU` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Fabricante` varchar(100) NOT NULL,
  `Arquitectura` varchar(50) NOT NULL,
  `Nucleos` int(11) NOT NULL,
  `Frecuencia` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  PRIMARY KEY (`idCPU`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cpu`
--

LOCK TABLES `cpu` WRITE;
/*!40000 ALTER TABLE `cpu` DISABLE KEYS */;
INSERT INTO `cpu` VALUES (1,'asas','SsQWEQ','QWEXZDA',13,0.90,0.00,8);
/*!40000 ALTER TABLE `cpu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `direccionip`
--

DROP TABLE IF EXISTS `direccionip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `direccionip` (
  `idIp` int(11) NOT NULL,
  `Direccion` varchar(45) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `idPaaS` int(11) DEFAULT NULL,
  PRIMARY KEY (`idIp`),
  KEY `idPaaS` (`idPaaS`),
  CONSTRAINT `direccionip_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `direccionip`
--

LOCK TABLES `direccionip` WRITE;
/*!40000 ALTER TABLE `direccionip` DISABLE KEYS */;
/*!40000 ALTER TABLE `direccionip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo`
--

DROP TABLE IF EXISTS `grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo` (
  `idGrupo` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idOrg` int(11) NOT NULL,
  PRIMARY KEY (`idGrupo`),
  KEY `idOrg` (`idOrg`),
  CONSTRAINT `grupo_ibfk_1` FOREIGN KEY (`idOrg`) REFERENCES `organizacion` (`idOrganizacion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo`
--

LOCK TABLES `grupo` WRITE;
/*!40000 ALTER TABLE `grupo` DISABLE KEYS */;
INSERT INTO `grupo` VALUES (3,'admin','Grupo con todos los permisos',4),(4,'admin','Grupo con todos los permisos',5),(5,'hola','hola',5),(6,'Confi1','ne',5);
/*!40000 ALTER TABLE `grupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monitorizacion`
--

DROP TABLE IF EXISTS `monitorizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitorizacion` (
  `idMonitorizacion` int(11) NOT NULL,
  `TipoEvento` varchar(50) DEFAULT NULL,
  `Descripcion` text DEFAULT NULL,
  `Fecha` date NOT NULL,
  `Hora` time NOT NULL,
  `idPaaS` int(11) DEFAULT NULL,
  `idSaaS` int(11) DEFAULT NULL,
  PRIMARY KEY (`idMonitorizacion`),
  KEY `idPaaS` (`idPaaS`),
  KEY `idSaaS` (`idSaaS`),
  CONSTRAINT `monitorizacion_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  CONSTRAINT `monitorizacion_ibfk_2` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitorizacion`
--

LOCK TABLES `monitorizacion` WRITE;
/*!40000 ALTER TABLE `monitorizacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `monitorizacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motor`
--

DROP TABLE IF EXISTS `motor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motor` (
  `idMotor` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Version` varchar(50) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idMotor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motor`
--

LOCK TABLES `motor` WRITE;
/*!40000 ALTER TABLE `motor` DISABLE KEYS */;
INSERT INTO `motor` VALUES (1,'asas','125',0.39),(2,'asas','1232',0.34),(3,'asas','125',1.11);
/*!40000 ALTER TABLE `motor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizacion`
--

DROP TABLE IF EXISTS `organizacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizacion` (
  `idOrganizacion` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `idCreador` int(11) DEFAULT NULL,
  PRIMARY KEY (`idOrganizacion`),
  KEY `idCreador` (`idCreador`),
  CONSTRAINT `organizacion_ibfk_1` FOREIGN KEY (`idCreador`) REFERENCES `usuario` (`idUsuario`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizacion`
--

LOCK TABLES `organizacion` WRITE;
/*!40000 ALTER TABLE `organizacion` DISABLE KEYS */;
INSERT INTO `organizacion` VALUES (3,'Confi1','hola',0),(4,'OrgaToni','Creada por Tonii100',1),(5,'Rizem','Grupo',0);
/*!40000 ALTER TABLE `organizacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paas`
--

DROP TABLE IF EXISTS `paas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paas` (
  `idPaaS` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `idSO` int(11) DEFAULT NULL,
  PRIMARY KEY (`idPaaS`),
  KEY `idSO` (`idSO`),
  CONSTRAINT `paas_ibfk_1` FOREIGN KEY (`idSO`) REFERENCES `sistemaoperativo` (`idSO`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paas`
--

LOCK TABLES `paas` WRITE;
/*!40000 ALTER TABLE `paas` DISABLE KEYS */;
/*!40000 ALTER TABLE `paas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privilegio`
--

DROP TABLE IF EXISTS `privilegio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `privilegio` (
  `idPrivilegio` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  PRIMARY KEY (`idPrivilegio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privilegio`
--

LOCK TABLES `privilegio` WRITE;
/*!40000 ALTER TABLE `privilegio` DISABLE KEYS */;
INSERT INTO `privilegio` VALUES (1,'Contratar paas','Permite al usuario contratar nuevos servicios PaaS'),(2,'Contratar saas','Permite al usuario contratar nuevos servicios SaaS'),(3,'Modificar paas','Permite al usuario modificar los servicios PaaS contratados'),(4,'Modificar saas','Permite al usuario modificar los servicios SaaS contratados'),(5,'Eliminar paas','Permite al usuario eliminar servicios PaaS contratados'),(6,'Eliminar saas','Permite al usuario eliminar servicios SaaS contratados'),(7,'Añadir usuarios','Permite al usuario añadir usuarios a la organización'),(8,'Gestionar grupos','Permite al usuario gestionar y modificar los grupos de la organización');
/*!40000 ALTER TABLE `privilegio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_grup_priv`
--

DROP TABLE IF EXISTS `r_grup_priv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_grup_priv` (
  `idGrup` int(11) NOT NULL,
  `idPriv` int(11) NOT NULL,
  PRIMARY KEY (`idGrup`,`idPriv`),
  KEY `idPriv` (`idPriv`),
  CONSTRAINT `r_grup_priv_ibfk_1` FOREIGN KEY (`idGrup`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE,
  CONSTRAINT `r_grup_priv_ibfk_2` FOREIGN KEY (`idPriv`) REFERENCES `privilegio` (`idPrivilegio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_grup_priv`
--

LOCK TABLES `r_grup_priv` WRITE;
/*!40000 ALTER TABLE `r_grup_priv` DISABLE KEYS */;
INSERT INTO `r_grup_priv` VALUES (3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),(4,1),(4,2),(4,3),(4,4),(4,5),(4,6),(4,7),(4,8),(5,3),(5,4);
/*!40000 ALTER TABLE `r_grup_priv` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_paas_almacenamiento`
--

DROP TABLE IF EXISTS `r_paas_almacenamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_paas_almacenamiento` (
  `idPaaS` int(11) NOT NULL,
  `idAlmacenamiento` int(11) NOT NULL,
  `Cantidad` int(11) DEFAULT NULL,
  PRIMARY KEY (`idPaaS`,`idAlmacenamiento`),
  KEY `idAlmacenamiento` (`idAlmacenamiento`),
  CONSTRAINT `r_paas_almacenamiento_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  CONSTRAINT `r_paas_almacenamiento_ibfk_2` FOREIGN KEY (`idAlmacenamiento`) REFERENCES `almacenamiento` (`idAlmacenamiento`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_paas_almacenamiento`
--

LOCK TABLES `r_paas_almacenamiento` WRITE;
/*!40000 ALTER TABLE `r_paas_almacenamiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_paas_almacenamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_paas_cpu`
--

DROP TABLE IF EXISTS `r_paas_cpu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_paas_cpu` (
  `idPaaS` int(11) NOT NULL,
  `idCPU` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  PRIMARY KEY (`idPaaS`,`idCPU`),
  KEY `idCPU` (`idCPU`),
  CONSTRAINT `r_paas_cpu_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  CONSTRAINT `r_paas_cpu_ibfk_2` FOREIGN KEY (`idCPU`) REFERENCES `cpu` (`idCPU`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_paas_cpu`
--

LOCK TABLES `r_paas_cpu` WRITE;
/*!40000 ALTER TABLE `r_paas_cpu` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_paas_cpu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_paas_grup`
--

DROP TABLE IF EXISTS `r_paas_grup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_paas_grup` (
  `idPaaS` int(11) NOT NULL,
  `idGrup` int(11) NOT NULL,
  PRIMARY KEY (`idPaaS`,`idGrup`),
  KEY `idGrup` (`idGrup`),
  CONSTRAINT `r_paas_grup_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  CONSTRAINT `r_paas_grup_ibfk_2` FOREIGN KEY (`idGrup`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_paas_grup`
--

LOCK TABLES `r_paas_grup` WRITE;
/*!40000 ALTER TABLE `r_paas_grup` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_paas_grup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_paas_ram`
--

DROP TABLE IF EXISTS `r_paas_ram`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_paas_ram` (
  `idPaaS` int(11) NOT NULL,
  `idRAM` int(11) NOT NULL,
  `Cantidad` int(11) DEFAULT NULL,
  PRIMARY KEY (`idPaaS`,`idRAM`),
  KEY `idRAM` (`idRAM`),
  CONSTRAINT `r_paas_ram_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE CASCADE,
  CONSTRAINT `r_paas_ram_ibfk_2` FOREIGN KEY (`idRAM`) REFERENCES `ram` (`idRAM`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_paas_ram`
--

LOCK TABLES `r_paas_ram` WRITE;
/*!40000 ALTER TABLE `r_paas_ram` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_paas_ram` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_saas_grup`
--

DROP TABLE IF EXISTS `r_saas_grup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_saas_grup` (
  `idSaaS` int(11) NOT NULL,
  `idGrup` int(11) NOT NULL,
  PRIMARY KEY (`idSaaS`,`idGrup`),
  KEY `idGrup` (`idGrup`),
  CONSTRAINT `r_saas_grup_ibfk_1` FOREIGN KEY (`idSaaS`) REFERENCES `saas` (`idSaaS`) ON DELETE CASCADE,
  CONSTRAINT `r_saas_grup_ibfk_2` FOREIGN KEY (`idGrup`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_saas_grup`
--

LOCK TABLES `r_saas_grup` WRITE;
/*!40000 ALTER TABLE `r_saas_grup` DISABLE KEYS */;
/*!40000 ALTER TABLE `r_saas_grup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_usuario_grupo`
--

DROP TABLE IF EXISTS `r_usuario_grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_usuario_grupo` (
  `idUsuario` int(11) NOT NULL,
  `idGrupo` int(11) NOT NULL,
  PRIMARY KEY (`idUsuario`,`idGrupo`),
  KEY `idGrupo` (`idGrupo`),
  CONSTRAINT `r_usuario_grupo_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `r_usuario_grupo_ibfk_2` FOREIGN KEY (`idGrupo`) REFERENCES `grupo` (`idGrupo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_usuario_grupo`
--

LOCK TABLES `r_usuario_grupo` WRITE;
/*!40000 ALTER TABLE `r_usuario_grupo` DISABLE KEYS */;
INSERT INTO `r_usuario_grupo` VALUES (0,4),(0,5),(1,3),(1,4),(1,6);
/*!40000 ALTER TABLE `r_usuario_grupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `r_usuario_org`
--

DROP TABLE IF EXISTS `r_usuario_org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `r_usuario_org` (
  `idUsuario` int(11) NOT NULL,
  `idOrg` int(11) NOT NULL,
  PRIMARY KEY (`idUsuario`,`idOrg`),
  KEY `idOrg` (`idOrg`),
  CONSTRAINT `r_usuario_org_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE,
  CONSTRAINT `r_usuario_org_ibfk_2` FOREIGN KEY (`idOrg`) REFERENCES `organizacion` (`idOrganizacion`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `r_usuario_org`
--

LOCK TABLES `r_usuario_org` WRITE;
/*!40000 ALTER TABLE `r_usuario_org` DISABLE KEYS */;
INSERT INTO `r_usuario_org` VALUES (0,5),(1,3),(1,4),(1,5);
/*!40000 ALTER TABLE `r_usuario_org` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ram`
--

DROP TABLE IF EXISTS `ram`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ram` (
  `idRAM` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Fabricante` varchar(100) NOT NULL,
  `Frecuencia` decimal(10,2) NOT NULL,
  `Capacidad` decimal(10,2) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  PRIMARY KEY (`idRAM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ram`
--

LOCK TABLES `ram` WRITE;
/*!40000 ALTER TABLE `ram` DISABLE KEYS */;
INSERT INTO `ram` VALUES (1,'Kingston DDR4','Kingston',3200.00,16.00,'DDR4',0.10,52),(2,'Corsair Vengeance LPX','Corsair',3600.00,32.00,'DDR4',0.15,29),(3,'HyperX Fury DDR5','HyperX',4800.00,16.00,'DDR5',0.20,20),(4,'G.Skill Trident Z','G.Skill',3200.00,64.00,'DDR4',0.25,10);
/*!40000 ALTER TABLE `ram` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saas`
--

DROP TABLE IF EXISTS `saas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `saas` (
  `idSaaS` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Usuario` varchar(50) DEFAULT NULL,
  `Contraseña` varchar(255) DEFAULT NULL,
  `idPaaS` int(11) DEFAULT NULL,
  `idMotor` int(11) DEFAULT NULL,
  PRIMARY KEY (`idSaaS`),
  KEY `idPaaS` (`idPaaS`),
  KEY `idMotor` (`idMotor`),
  CONSTRAINT `saas_ibfk_1` FOREIGN KEY (`idPaaS`) REFERENCES `paas` (`idPaaS`) ON DELETE SET NULL,
  CONSTRAINT `saas_ibfk_2` FOREIGN KEY (`idMotor`) REFERENCES `motor` (`idMotor`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saas`
--

LOCK TABLES `saas` WRITE;
/*!40000 ALTER TABLE `saas` DISABLE KEYS */;
/*!40000 ALTER TABLE `saas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistemaoperativo`
--

DROP TABLE IF EXISTS `sistemaoperativo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sistemaoperativo` (
  `idSO` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Arquitectura` varchar(50) NOT NULL,
  `Version` varchar(50) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `MinAlmacenamiento` decimal(10,2) NOT NULL,
  `MinRAM` decimal(10,2) NOT NULL,
  `PrecioH` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idSO`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistemaoperativo`
--

LOCK TABLES `sistemaoperativo` WRITE;
/*!40000 ALTER TABLE `sistemaoperativo` DISABLE KEYS */;
INSERT INTO `sistemaoperativo` VALUES (1,'Ubuntu','x64','22.04','Linux',20.00,2.00,0.10),(2,'Windows Server 2022','x64','21H2','Windows',50.00,8.00,0.50),(3,'Debian','x64','11','Linux',25.00,2.00,0.08),(4,'CentOS','x64','7','Linux',30.00,4.00,0.15),(5,'Red Hat Enterprise Linux','x64','8','Linux',30.00,4.00,0.20),(6,'Windows Server 2016','x64','1607','Windows',40.00,4.00,0.40);
/*!40000 ALTER TABLE `sistemaoperativo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajador`
--

DROP TABLE IF EXISTS `trabajador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trabajador` (
  `idTrabajador` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  PRIMARY KEY (`idTrabajador`),
  KEY `idUsuario` (`idUsuario`),
  CONSTRAINT `trabajador_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajador`
--

LOCK TABLES `trabajador` WRITE;
/*!40000 ALTER TABLE `trabajador` DISABLE KEYS */;
INSERT INTO `trabajador` VALUES (0,0);
/*!40000 ALTER TABLE `trabajador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `Direccion` text DEFAULT NULL,
  `FechaRegistro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `Usuario` (`Usuario`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (0,'Xisco Gaya','xiiscogaya','xiiscogaya@hotmail.com','62378348','$2y$10$FBtDXX02Lg2QnzudJzqpauMCRejRwS3Gk1kJXYInc7JOHkErOHFeO','C de bunyola, 6','2024-12-08 19:56:33'),(1,'Toni','tonii100','toni100@hotmail.com','3784848983','$2y$10$2HGueF9aisFDS7v3ZC72sOZ7HTajGS1DReQwW.kgTQrZuJrsA161y','Avinguda des Tren 20','2024-12-09 21:21:32');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'totclouddb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-12-10 23:09:41
