-- =========================================================
-- Proyecto SAE - Script de creación de base de datos
-- =========================================================

CREATE DATABASE IF NOT EXISTS proyecto_sae
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE proyecto_sae;

-- ---------------------------------------------------------
-- Tabla: alumnos
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS alumnos (
    ID_ALUMNOS        INT AUTO_INCREMENT PRIMARY KEY,
    MATRICULA         VARCHAR(15) NOT NULL,
    NOMBRE            VARCHAR(30) NOT NULL,
    APELLIDO_PATERNO  VARCHAR(15) NOT NULL,
    APELLIDO_MATERNO  VARCHAR(15) NOT NULL,
    DOMICILIO         VARCHAR(80) NOT NULL,
    CORREO            VARCHAR(50) NOT NULL,
    TELEFONO          VARCHAR(35) NOT NULL,
    ESTATUS           VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

-- ---------------------------------------------------------
-- Tabla: grupo
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS grupo (
    ID_GRUPO        INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE_G        VARCHAR(15) NOT NULL,
    DESCRIPCION_G   VARCHAR(80) NOT NULL,
    ESTATUS_G       VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

-- ---------------------------------------------------------
-- Tabla: materias
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS materias (
    ID_MATERIA      INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE_M        VARCHAR(15) NOT NULL,
    DESCRIPCION_M   VARCHAR(80) NOT NULL,
    ESTATUS_M       VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

-- ---------------------------------------------------------
-- Tabla: profesores
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS profesores (
    ID_PROFESORES     INT AUTO_INCREMENT PRIMARY KEY,
    NUMERO_EMPLEADO   VARCHAR(15) NOT NULL,
    NOMBRE            VARCHAR(30) NOT NULL,
    APELLIDO_PATERNO  VARCHAR(15) NOT NULL,
    APELLIDO_MATERNO  VARCHAR(15) NOT NULL,
    DOMICILIO         VARCHAR(80) NOT NULL,
    CORREO            VARCHAR(50) NOT NULL,
    TELEFONO          VARCHAR(35) NOT NULL,
    ESTATUS           VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);
