
CREATE DATABASE IF NOT EXISTS proyecto_sae
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE proyecto_sae;

DROP TABLE IF EXISTS calificaciones;
DROP TABLE IF EXISTS asignatura;
DROP TABLE IF EXISTS grupo_alumno;
DROP TABLE IF EXISTS unidad;
DROP TABLE IF EXISTS materias;
DROP TABLE IF EXISTS profesores;
DROP TABLE IF EXISTS grupos;
DROP TABLE IF EXISTS grupo;
DROP TABLE IF EXISTS alumnos;

CREATE TABLE alumnos (
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
    DESCRIPCION   VARCHAR(255) NOT NULL DEFAULT '',
    ESTATUS       VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

CREATE TABLE materias (
    ID_MATERIAS     INT AUTO_INCREMENT PRIMARY KEY,
    CLAVE_MATERIA   VARCHAR(15) NOT NULL,
    NOMBRE_MATERIA  VARCHAR(50) NOT NULL,
    CREDITOS        VARCHAR(5)  NOT NULL,
    DESCRIPCION     VARCHAR(255) NOT NULL DEFAULT '',
    ESTATUS         VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

CREATE TABLE unidad (
    ID_UNIDAD    INT PRIMARY KEY,
    DESCRIPCION  VARCHAR(255) NOT NULL,
    ESTATUS      VARCHAR(4) NOT NULL DEFAULT 'ALTA'
);

CREATE TABLE grupo_alumno (
    ID_GRUPOS   INT NOT NULL,
    ID_ALUMNOS  INT NOT NULL,
    ESTATUS     VARCHAR(4) NOT NULL DEFAULT 'ALTA',
    PRIMARY KEY (ID_GRUPOS, ID_ALUMNOS)
);

CREATE TABLE asignatura (
    ID_ASIGNATURA  INT AUTO_INCREMENT PRIMARY KEY,
    ID_MATERIAS    INT NOT NULL,
    ID_ALUMNOS     INT NOT NULL,
    ID_PROFESORES  INT NOT NULL,
    UNIQUE KEY uq_materia_alumno (ID_MATERIAS, ID_ALUMNOS)
);

CREATE TABLE calificaciones (
    ID_CALIFICACION  INT AUTO_INCREMENT PRIMARY KEY,
    ID_ALUMNOS       INT NOT NULL,
    CALIFICACION     DECIMAL(4,1) NOT NULL,
    ID_UNIDAD        INT NOT NULL,
    UNIQUE KEY uq_alumno_unidad (ID_ALUMNOS, ID_UNIDAD)
);

INSERT INTO alumnos (ID_ALUMNOS, MATRICULA, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, DOMICILIO, CORREO, TELEFONO, ESTATUS) VALUES
(1, 'TIC2026001', 'Carlos',   'Hernández', 'López',    'Calle Reforma 101, Centro',    'carlos.hernandez@correo.com', '5512345001', 'ALTA'),
(2, 'TIC2026002', 'María',    'García',    'Ruiz',     'Av. Juárez 202, Col. Norte',   'maria.garcia@correo.com',     '5512345002', 'ALTA'),
(3, 'TIC2026003', 'Luis',     'Martínez',  'Soto',     'Calle Hidalgo 303, Col. Sur',  'luis.martinez@correo.com',    '5512345003', 'ALTA'),
(4, 'TIC2026004', 'Ana',      'López',     'Torres',   'Calle Morelos 404, Centro',    'ana.lopez@correo.com',        '5512345004', 'ALTA'),
(5, 'TIC2026005', 'Jorge',    'Ramírez',   'Flores',   'Av. Insurgentes 505',          'jorge.ramirez@correo.com',    '5512345005', 'ALTA'),
(6, 'TIC2026006', 'Sofía',    'Torres',    'Méndez',   'Calle Allende 606, Col. Este', 'sofia.torres@correo.com',     '5512345006', 'ALTA'),
(7, 'TIC2026007', 'Diego',    'Flores',    'Castillo', 'Calle Victoria 707',           'diego.flores@correo.com',     '5512345007', 'ALTA'),
(8, 'TIC2026008', 'Valeria',  'Mendoza',   'Ríos',     'Av. Universidad 808',          'valeria.mendoza@correo.com',  '5512345008', 'ALTA');

INSERT INTO profesores (ID_PROFESORES, CLAVE, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CORREO, TELEFONO, ESTATUS) VALUES
(1, 'PROF001', 'Roberto',  'Sánchez', 'Peña',     'roberto.sanchez@escuela.mx',  '5598760001', 'ALTA'),
(2, 'PROF002', 'Laura',    'Gómez',   'Ibarra',   'laura.gomez@escuela.mx',      '5598760002', 'ALTA'),
(3, 'PROF003', 'Miguel',   'Díaz',    'Campos',   'miguel.diaz@escuela.mx',      '5598760003', 'ALTA'),
(4, 'PROF004', 'Patricia', 'Reyes',   'Ortega',   'patricia.reyes@escuela.mx',   '5598760004', 'ALTA'),
(5, 'PROF005', 'Ricardo',  'Morales', 'Salinas',  'ricardo.morales@escuela.mx',  '5598760005', 'ALTA'),
(6, 'PROF006', 'Elena',    'Vargas',  'Cruz',     'elena.vargas@escuela.mx',     '5598760006', 'ALTA'),
(7, 'PROF007', 'Héctor',   'Jiménez', 'Paredes',  'hector.jimenez@escuela.mx',   '5598760007', 'ALTA'),
(8, 'PROF008', 'Gabriela', 'Herrera', 'Luna',     'gabriela.herrera@escuela.mx', '5598760008', 'ALTA');

INSERT INTO grupos (ID_GRUPOS, NOMBRE_GRUPO, TURNO, SEMESTRE, DESCRIPCION, ESTATUS) VALUES
(1, 'TIC-101', 'Matutino',   '1', 'Primer semestre grupo A',  'ALTA'),
(2, 'TIC-102', 'Vespertino', '1', 'Primer semestre grupo B',  'ALTA'),
(3, 'TIC-201', 'Matutino',   '2', 'Segundo semestre grupo A', 'ALTA'),
(4, 'TIC-202', 'Vespertino', '2', 'Segundo semestre grupo B', 'ALTA'),
(5, 'TIC-301', 'Matutino',   '3', 'Tercer semestre grupo A',  'ALTA'),
(6, 'TIC-302', 'Vespertino', '3', 'Tercer semestre grupo B',  'ALTA'),
(7, 'TIC-401', 'Matutino',   '4', 'Cuarto semestre grupo A',  'ALTA'),
(8, 'TIC-402', 'Vespertino', '4', 'Cuarto semestre grupo B',  'ALTA');

INSERT INTO materias (ID_MATERIAS, CLAVE_MATERIA, NOMBRE_MATERIA, CREDITOS, DESCRIPCION, ESTATUS) VALUES
(1, 'MAT-001', 'Programación I',         '8', 'Fundamentos de programación',      'ALTA'),
(2, 'MAT-002', 'Programación II',        '8', 'PHP y bases de datos',             'ALTA'),
(3, 'MAT-003', 'Bases de Datos',         '8', 'Modelado y SQL',                   'ALTA'),
(4, 'MAT-004', 'Redes de Computadoras',  '6', 'Fundamentos de redes',             'ALTA'),
(5, 'MAT-005', 'Ingeniería de Software', '6', 'Metodologías y documentación',     'ALTA'),
(6, 'MAT-006', 'Sistemas Operativos',    '6', 'Administración de sistemas',       'ALTA'),
(7, 'MAT-007', 'Electrónica Digital',    '6', 'Circuitos y microcontroladores',   'ALTA'),
(8, 'MAT-008', 'Fibra Óptica',           '4', 'Telecomunicaciones por fibra',     'ALTA');

INSERT INTO unidad (ID_UNIDAD, DESCRIPCION, ESTATUS) VALUES
(1, 'Unidad 1 - Introducción',     'ALTA'),
(2, 'Unidad 2 - Fundamentos',      'ALTA'),
(3, 'Unidad 3 - Desarrollo',       'ALTA'),
(4, 'Unidad 4 - Aplicación',       'ALTA'),
(5, 'Unidad 5 - Integración',      'ALTA'),
(6, 'Unidad 6 - Prácticas',        'ALTA'),
(7, 'Unidad 7 - Proyecto parcial', 'ALTA'),
(8, 'Unidad 8 - Proyecto final',   'ALTA');

-- Inscripciones a grupos (una está en BAJA para ver ambos estatus)
INSERT INTO grupo_alumno (ID_GRUPOS, ID_ALUMNOS, ESTATUS) VALUES
(1, 1, 'ALTA'),
(1, 2, 'ALTA'),
(1, 3, 'ALTA'),
(2, 4, 'ALTA'),
(2, 5, 'ALTA'),
(3, 6, 'ALTA'),
(3, 7, 'BAJA'),
(4, 8, 'ALTA');

-- Asignación de materias (materia, alumno, profesor)
INSERT INTO asignatura (ID_ASIGNATURA, ID_MATERIAS, ID_ALUMNOS, ID_PROFESORES) VALUES
(1, 1, 1, 1),
(2, 2, 1, 2),
(3, 1, 2, 1),
(4, 3, 2, 3),
(5, 2, 3, 2),
(6, 4, 4, 4),
(7, 5, 5, 5),
(8, 6, 6, 6);

-- Calificaciones: alumnos 1 a 4 en las unidades 1 y 2.
-- Quedan libres las unidades 3 a 8 y los alumnos 5 a 8 para probar la captura.
INSERT INTO calificaciones (ID_ALUMNOS, CALIFICACION, ID_UNIDAD) VALUES
(1, 9.5, 1),
(2, 8.0, 1),
(3, 7.5, 1),
(4, 10.0, 1),
(1, 9.0, 2),
(2, 8.5, 2),
(3, 7.0, 2),
(4, 9.5, 2);
