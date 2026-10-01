-- =========================================================
-- Proyecto SAE - Base de datos alineada con los crud*.php
-- OJO: borra y recrea profesores, grupos y materias
-- (se pierden los datos de esas tablas). alumnos no se toca.
-- =========================================================

CREATE DATABASE IF NOT EXISTS proyecto_sae
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE proyecto_sae;

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

-- Tablas viejas que ya no se usan
DROP TABLE IF EXISTS grupo;
DROP TABLE IF EXISTS grupos;
DROP TABLE IF EXISTS materias;
DROP TABLE IF EXISTS profesores;

CREATE TABLE profesores (
    ID_PROFESORES     INT AUTO_INCREMENT PRIMARY KEY,
    CLAVE             VARCHAR(15) NOT NULL,
    NOMBRE            VARCHAR(30) NOT NULL,
    APELLIDO_PATERNO  VARCHAR(15) NOT NULL,
    APELLIDO_MATERNO  VARCHAR(15) NOT NULL,
    CORREO            VARCHAR(50) NOT NULL,
    TELEFONO          VARCHAR(35) NOT NULL,
    ESTATUS           VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

CREATE TABLE grupos (
    ID_GRUPOS     INT AUTO_INCREMENT PRIMARY KEY,
    NOMBRE_GRUPO  VARCHAR(20) NOT NULL,
    TURNO         VARCHAR(15) NOT NULL,
    SEMESTRE      VARCHAR(10) NOT NULL,
    ESTATUS       VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

CREATE TABLE materias (
    ID_MATERIAS     INT AUTO_INCREMENT PRIMARY KEY,
    CLAVE_MATERIA   VARCHAR(15) NOT NULL,
    NOMBRE_MATERIA  VARCHAR(50) NOT NULL,
    CREDITOS        VARCHAR(5)  NOT NULL,
    ESTATUS         VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);
