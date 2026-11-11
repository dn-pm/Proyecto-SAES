SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS proyecto_sae
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_spanish_ci;

USE proyecto_sae;

-- ---------- Limpieza ----------
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS puesto;
DROP TABLE IF EXISTS calificaciones;
DROP TABLE IF EXISTS asignatura;
DROP TABLE IF EXISTS grupo_alumno;
DROP TABLE IF EXISTS unidad;
DROP TABLE IF EXISTS materias;
DROP TABLE IF EXISTS profesores;
DROP TABLE IF EXISTS grupos;
DROP TABLE IF EXISTS grupo;
DROP TABLE IF EXISTS alumnos;

-- =====================================================================
--  CATÁLOGOS
-- =====================================================================

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

-- =====================================================================
--  PROCESOS
-- =====================================================================

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
    ID_MATERIAS      INT NOT NULL,           -- materia a la que pertenece la calificación
    CALIFICACION     DECIMAL(4,1) NOT NULL,
    ID_UNIDAD        INT NOT NULL,
    UNIQUE KEY uq_alumno_materia_unidad (ID_ALUMNOS, ID_MATERIAS, ID_UNIDAD)
);

-- =====================================================================
--  USUARIOS Y PUESTOS 
-- =====================================================================

CREATE TABLE puesto (
    ID_PUESTO           INT AUTO_INCREMENT PRIMARY KEY,
    DESCRIPCION_PUESTO  VARCHAR(50) NOT NULL,
    STATUS_PUESTO       VARCHAR(4)  NOT NULL DEFAULT 'ALTA'
);

CREATE TABLE usuarios (
    ID_USER      INT AUTO_INCREMENT PRIMARY KEY,
    NOMBREUSER   VARCHAR(50) NOT NULL,
    PWUSER       VARCHAR(40) NOT NULL,   -- hash MD5 (32 caracteres) o SHA1 (40 caracteres)
    PUESTO       INT NOT NULL,           -- llave foránea a puesto.ID_PUESTO
    STATUS_USER  VARCHAR(4)  NOT NULL DEFAULT 'ALTA',
    CONSTRAINT fk_usuarios_puesto FOREIGN KEY (PUESTO) REFERENCES puesto (ID_PUESTO)
);

-- =====================================================================
--  DATOS DE EJEMPLO 
-- =====================================================================

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

-- Inscripciones a grupos
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

INSERT INTO calificaciones (ID_ALUMNOS, ID_MATERIAS, CALIFICACION, ID_UNIDAD) VALUES
(1, 1, 9.5, 1),
(1, 1, 9.0, 2),
(1, 2, 8.0, 1),
(1, 2, 8.5, 2),
(2, 1, 8.0, 1),
(2, 1, 8.5, 2),
(2, 3, 7.5, 1),
(2, 3, 7.0, 2);

DELETE FROM calificaciones;
DELETE FROM asignatura;
DELETE FROM grupo_alumno;
DELETE FROM alumnos;
DELETE FROM profesores;
DELETE FROM grupos;
DELETE FROM materias;
DELETE FROM unidad;

-- ---------- 1. ALUMNOS (24 Registros) ----------
INSERT INTO alumnos (ID_ALUMNOS, MATRICULA, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, DOMICILIO, CORREO, TELEFONO, ESTATUS) VALUES
(1, 'TIC2026001', 'Carlos', 'Hernández', 'Soto', 'Av. Juárez 105, Col. Centro', 'carlos.hernandez1@correo.com', '5512345001', 'ALTA'),
(2, 'TIC2026002', 'María', 'García', 'Flores', 'Calle Hidalgo 110, Col. Centro', 'maria.garcia2@correo.com', '5512345002', 'ALTA'),
(3, 'TIC2026003', 'Luis', 'Martínez', 'Castillo', 'Calle Morelos 115, Col. Centro', 'luis.martinez3@correo.com', '5512345003', 'ALTA'),
(4, 'TIC2026004', 'Ana', 'López', 'Peña', 'Av. Insurgentes 120, Col. Centro', 'ana.lopez4@correo.com', '5512345004', 'ALTA'),
(5, 'TIC2026005', 'Jorge', 'Ramírez', 'Campos', 'Calle Allende 125, Col. Centro', 'jorge.ramirez5@correo.com', '5512345005', 'ALTA'),
(6, 'TIC2026006', 'Sofía', 'Torres', 'Salinas', 'Calle Victoria 130, Col. Centro', 'sofia.torres6@correo.com', '5512345006', 'BAJA'),
(7, 'TIC2026007', 'Diego', 'Flores', 'Paredes', 'Av. Universidad 135, Col. Centro', 'diego.flores7@correo.com', '5512345007', 'ALTA'),
(8, 'TIC2026008', 'Valeria', 'Mendoza', 'Vega', 'Calle 5 de Mayo 140, Col. Centro', 'valeria.mendoza8@correo.com', '5512345008', 'ALTA'),
(9, 'TIC2026009', 'Fernando', 'Sánchez', 'Morales', 'Av. Revolución 145, Col. Centro', 'fernando.sanchez9@correo.com', '5512345009', 'ALTA'),
(10, 'TIC2026010', 'Daniela', 'Gómez', 'López', 'Calle Zaragoza 150, Col. Centro', 'daniela.gomez10@correo.com', '5512345010', 'ALTA'),
(11, 'TIC2026011', 'Alejandro', 'Díaz', 'Soto', 'Av. Chapultepec 155, Col. Centro', 'alejandro.diaz11@correo.com', '5512345011', 'ALTA'),
(12, 'TIC2026012', 'Camila', 'Reyes', 'Flores', 'Calle Reforma 160, Col. Centro', 'camila.reyes12@correo.com', '5512345012', 'ALTA'),
(13, 'TIC2026013', 'Javier', 'Morales', 'Castillo', 'Av. Juárez 165, Col. Centro', 'javier.morales13@correo.com', '5512345013', 'ALTA'),
(14, 'TIC2026014', 'Natalia', 'Vargas', 'Peña', 'Calle Hidalgo 170, Col. Centro', 'natalia.vargas14@correo.com', '5512345014', 'ALTA'),
(15, 'TIC2026015', 'Gabriel', 'Jiménez', 'Campos', 'Calle Morelos 175, Col. Centro', 'gabriel.jimenez15@correo.com', '5512345015', 'ALTA'),
(16, 'TIC2026016', 'Lucía', 'Herrera', 'Salinas', 'Av. Insurgentes 180, Col. Centro', 'lucia.herrera16@correo.com', '5512345016', 'ALTA'),
(17, 'TIC2026017', 'Mateo', 'Castro', 'Paredes', 'Calle Allende 185, Col. Centro', 'mateo.castro17@correo.com', '5512345017', 'ALTA'),
(18, 'TIC2026018', 'Valentina', 'Ortiz', 'Vega', 'Calle Victoria 190, Col. Centro', 'valentina.ortiz18@correo.com', '5512345018', 'BAJA'),
(19, 'TIC2026019', 'Adrian', 'Ruiz', 'Morales', 'Av. Universidad 195, Col. Centro', 'adrian.ruiz19@correo.com', '5512345019', 'ALTA'),
(20, 'TIC2026020', 'Elena', 'Álvarez', 'López', 'Calle 5 de Mayo 200, Col. Centro', 'elena.alvarez20@correo.com', '5512345020', 'ALTA'),
(21, 'TIC2026021', 'Ricardo', 'Hernández', 'Soto', 'Av. Revolución 205, Col. Centro', 'ricardo.hernandez21@correo.com', '5512345021', 'ALTA'),
(22, 'TIC2026022', 'Ximena', 'García', 'Flores', 'Calle Zaragoza 210, Col. Centro', 'ximena.garcia22@correo.com', '5512345022', 'ALTA'),
(23, 'TIC2026023', 'Gonzalo', 'Martínez', 'Castillo', 'Av. Chapultepec 215, Col. Centro', 'gonzalo.martinez23@correo.com', '5512345023', 'ALTA'),
(24, 'TIC2026024', 'Paola', 'López', 'Peña', 'Calle Reforma 220, Col. Centro', 'paola.lopez24@correo.com', '5512345024', 'ALTA');

-- ---------- 2. PROFESORES (8 Registros) ----------
INSERT INTO profesores (ID_PROFESORES, CLAVE, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CORREO, TELEFONO, ESTATUS) VALUES
(1, 'PROF001', 'Roberto', 'Sánchez', 'Peña', 'roberto.sanchez@escuela.mx', '5598760001', 'ALTA'),
(2, 'PROF002', 'Laura', 'Gómez', 'Ibarra', 'laura.gomez@escuela.mx', '5598760002', 'ALTA'),
(3, 'PROF003', 'Miguel', 'Díaz', 'Campos', 'miguel.diaz@escuela.mx', '5598760003', 'ALTA'),
(4, 'PROF004', 'Patricia', 'Reyes', 'Ortega', 'patricia.reyes@escuela.mx', '5598760004', 'ALTA'),
(5, 'PROF005', 'Ricardo', 'Morales', 'Salinas', 'ricardo.morales@escuela.mx', '5598760005', 'ALTA'),
(6, 'PROF006', 'Elena', 'Vargas', 'Cruz', 'elena.vargas@escuela.mx', '5598760006', 'ALTA'),
(7, 'PROF007', 'Héctor', 'Jiménez', 'Paredes', 'hector.jimenez@escuela.mx', '5598760007', 'ALTA'),
(8, 'PROF008', 'Gabriela', 'Herrera', 'Luna', 'gabriela.herrera@escuela.mx', '5598760008', 'ALTA');

-- ---------- 3. GRUPOS (6 Registros) ----------
INSERT INTO grupos (ID_GRUPOS, NOMBRE_GRUPO, TURNO, SEMESTRE, DESCRIPCION, ESTATUS) VALUES
(1, 'TIC-101', 'Matutino', '1', 'Primer semestre grupo A', 'ALTA'),
(2, 'TIC-102', 'Vespertino', '1', 'Primer semestre grupo B', 'ALTA'),
(3, 'TIC-201', 'Matutino', '2', 'Segundo semestre grupo A', 'ALTA'),
(4, 'TIC-202', 'Vespertino', '2', 'Segundo semestre grupo B', 'ALTA'),
(5, 'TIC-301', 'Matutino', '3', 'Tercer semestre grupo A', 'ALTA'),
(6, 'TIC-302', 'Vespertino', '3', 'Tercer semestre grupo B', 'ALTA');

-- ---------- 4. MATERIAS (8 Registros) ----------
INSERT INTO materias (ID_MATERIAS, CLAVE_MATERIA, NOMBRE_MATERIA, CREDITOS, DESCRIPCION, ESTATUS) VALUES
(1, 'MAT-001', 'Programación I', '8', 'Fundamentos de programación', 'ALTA'),
(2, 'MAT-002', 'Programación II', '8', 'PHP y bases de datos', 'ALTA'),
(3, 'MAT-003', 'Bases de Datos', '8', 'Modelado y SQL', 'ALTA'),
(4, 'MAT-004', 'Redes de Computadoras', '6', 'Fundamentos de redes', 'ALTA'),
(5, 'MAT-005', 'Ingeniería de Software', '6', 'Metodologías y documentación', 'ALTA'),
(6, 'MAT-006', 'Sistemas Operativos', '6', 'Administración de sistemas', 'ALTA'),
(7, 'MAT-007', 'Electrónica Digital', '6', 'Circuitos y microcontroladores', 'ALTA'),
(8, 'MAT-008', 'Fibra Óptica', '4', 'Telecomunicaciones por fibra', 'ALTA');

-- ---------- 5. UNIDADES (5 Unidades Evaluativas) ----------
INSERT INTO unidad (ID_UNIDAD, DESCRIPCION, ESTATUS) VALUES
(1, 'Unidad 1 - Introducción', 'ALTA'),
(2, 'Unidad 2 - Fundamentos', 'ALTA'),
(3, 'Unidad 3 - Desarrollo', 'ALTA'),
(4, 'Unidad 4 - Aplicación', 'ALTA'),
(5, 'Unidad 5 - Integración', 'ALTA');

-- ---------- 6. INSCRIPCIÓN A GRUPOS (grupo_alumno) ----------
INSERT INTO grupo_alumno (ID_GRUPOS, ID_ALUMNOS, ESTATUS) VALUES
(1, 1, 'ALTA'), (1, 2, 'ALTA'), (1, 3, 'ALTA'), (1, 4, 'ALTA'),
(2, 5, 'ALTA'), (2, 6, 'BAJA'), (2, 7, 'ALTA'), (2, 8, 'ALTA'),
(3, 9, 'ALTA'), (3, 10, 'ALTA'), (3, 11, 'ALTA'), (3, 12, 'ALTA'),
(4, 13, 'ALTA'), (4, 14, 'ALTA'), (4, 15, 'ALTA'), (4, 16, 'ALTA'),
(5, 17, 'ALTA'), (5, 18, 'BAJA'), (5, 19, 'ALTA'), (5, 20, 'ALTA'),
(6, 21, 'ALTA'), (6, 22, 'ALTA'), (6, 23, 'ALTA'), (6, 24, 'ALTA');

-- ---------- 7. ASIGNACIÓN DE MATERIAS Y DOCENTES (asignatura) ----------
INSERT INTO asignatura (ID_ASIGNATURA, ID_MATERIAS, ID_ALUMNOS, ID_PROFESORES) VALUES
(1, 1, 1, 1), (2, 3, 1, 3), (3, 4, 1, 4), (4, 7, 1, 7),
(5, 1, 2, 1), (6, 3, 2, 3), (7, 4, 2, 4), (8, 7, 2, 7),
(9, 1, 3, 1), (10, 3, 3, 3), (11, 4, 3, 4), (12, 7, 3, 7),
(13, 1, 4, 1), (14, 3, 4, 3), (15, 4, 4, 4), (16, 7, 4, 7),
(17, 1, 5, 1), (18, 3, 5, 3), (19, 4, 5, 4), (20, 7, 5, 7),
(21, 1, 6, 1), (22, 3, 6, 3), (23, 4, 6, 4), (24, 7, 6, 7),
(25, 1, 7, 1), (26, 3, 7, 3), (27, 4, 7, 4), (28, 7, 7, 7),
(29, 1, 8, 1), (30, 3, 8, 3), (31, 4, 8, 4), (32, 7, 8, 7),
(33, 2, 9, 2), (34, 3, 9, 3), (35, 5, 9, 5), (36, 6, 9, 6),
(37, 2, 10, 2), (38, 3, 10, 3), (39, 5, 10, 5), (40, 6, 10, 6),
(41, 2, 11, 2), (42, 3, 11, 3), (43, 5, 11, 5), (44, 6, 11, 6),
(45, 2, 12, 2), (46, 3, 12, 3), (47, 5, 12, 5), (48, 6, 12, 6),
(49, 2, 13, 2), (50, 3, 13, 3), (51, 5, 13, 5), (52, 6, 13, 6),
(53, 2, 14, 2), (54, 3, 14, 3), (55, 5, 14, 5), (56, 6, 14, 6),
(57, 2, 15, 2), (58, 3, 15, 3), (59, 5, 15, 5), (60, 6, 15, 6),
(61, 2, 16, 2), (62, 3, 16, 3), (63, 5, 16, 5), (64, 6, 16, 6),
(65, 2, 17, 2), (66, 5, 17, 5), (67, 6, 17, 6), (68, 8, 17, 8),
(69, 2, 18, 2), (70, 5, 18, 5), (71, 6, 18, 6), (72, 8, 18, 8),
(73, 2, 19, 2), (74, 5, 19, 5), (75, 6, 19, 6), (76, 8, 19, 8),
(77, 2, 20, 2), (78, 5, 20, 5), (79, 6, 20, 6), (80, 8, 20, 8),
(81, 2, 21, 2), (82, 5, 21, 5), (83, 6, 21, 6), (84, 8, 21, 8),
(85, 2, 22, 2), (86, 5, 22, 5), (87, 6, 22, 6), (88, 8, 22, 8),
(89, 2, 23, 2), (90, 5, 23, 5), (91, 6, 23, 6), (92, 8, 23, 8),
(93, 2, 24, 2), (94, 5, 24, 5), (95, 6, 24, 6), (96, 8, 24, 8);

-- ---------- 8. CALIFICACIONES POR ALUMNO Y MATERIA (calificaciones) ----------
INSERT INTO calificaciones (ID_ALUMNOS, ID_MATERIAS, CALIFICACION, ID_UNIDAD) VALUES
(1, 1, 9.5, 1), (1, 1, 7.5, 2), (1, 1, 10.0, 3), (1, 1, 9.5, 4), (1, 1, 9.5, 5),
(1, 3, 8.0, 1), (1, 3, 7.0, 2), (1, 3, 10.0, 3), (1, 3, 7.0, 4), (1, 3, 8.0, 5),
(1, 4, 9.5, 1), (1, 4, 6.5, 2), (1, 4, 8.5, 3), (1, 4, 10.0, 4), (1, 4, 9.5, 5),
(1, 7, 9.0, 1), (1, 7, 9.0, 2), (1, 7, 9.0, 3), (1, 7, 9.0, 4), (1, 7, 9.0, 5),
(2, 1, 8.5, 1), (2, 1, 8.5, 2), (2, 1, 8.0, 3), (2, 1, 8.0, 4), (2, 1, 8.5, 5),
(2, 3, 9.0, 1), (2, 3, 9.0, 2), (2, 3, 9.0, 3), (2, 3, 9.0, 4), (2, 3, 9.0, 5),
(2, 4, 10.0, 1), (2, 4, 7.0, 2), (2, 4, 8.0, 3), (2, 4, 7.0, 4), (2, 4, 10.0, 5),
(2, 7, 7.5, 1), (2, 7, 10.0, 2), (2, 7, 9.5, 3), (2, 7, 9.5, 4), (2, 7, 7.5, 5),
(3, 1, 6.5, 1), (3, 1, 8.5, 2), (3, 1, 10.0, 3), (3, 1, 9.5, 4), (3, 1, 6.5, 5),
(3, 3, 9.5, 1), (3, 3, 7.5, 2), (3, 3, 10.0, 3), (3, 3, 9.5, 4), (3, 3, 9.5, 5),
(3, 4, 9.0, 1), (3, 4, 9.0, 2), (3, 4, 9.0, 3), (3, 4, 9.0, 4), (3, 4, 9.0, 5),
(3, 7, 8.5, 1), (3, 7, 8.0, 2), (3, 7, 8.0, 3), (3, 7, 8.5, 4), (3, 7, 8.5, 5),
(4, 1, 7.0, 1), (4, 1, 8.0, 2), (4, 1, 7.0, 3), (4, 1, 10.0, 4), (4, 1, 7.0, 5),
(4, 3, 8.5, 1), (4, 3, 8.5, 2), (4, 3, 8.0, 3), (4, 3, 8.0, 4), (4, 3, 8.5, 5),
(4, 4, 10.0, 1), (4, 4, 9.5, 2), (4, 4, 9.5, 3), (4, 4, 7.5, 4), (4, 4, 10.0, 5),
(4, 7, 8.5, 1), (4, 7, 10.0, 2), (4, 7, 9.5, 3), (4, 7, 6.5, 4), (4, 7, 8.5, 5),
(5, 1, 9.0, 1), (5, 1, 9.0, 2), (5, 1, 9.0, 3), (5, 1, 9.0, 4), (5, 1, 9.0, 5),
(5, 3, 6.5, 1), (5, 3, 8.5, 2), (5, 3, 10.0, 3), (5, 3, 9.5, 4), (5, 3, 6.5, 5),
(5, 4, 8.0, 1), (5, 4, 8.0, 2), (5, 4, 8.5, 3), (5, 4, 8.5, 4), (5, 4, 8.0, 5),
(5, 7, 8.0, 1), (5, 7, 7.0, 2), (5, 7, 10.0, 3), (5, 7, 7.0, 4), (5, 7, 8.0, 5),
(6, 1, 9.5, 1), (6, 1, 9.5, 2), (6, 1, 7.5, 3), (6, 1, 10.0, 4), (6, 1, 9.5, 5),
(6, 3, 7.0, 1), (6, 3, 8.0, 2), (6, 3, 7.0, 3), (6, 3, 10.0, 4), (6, 3, 7.0, 5),
(6, 4, 10.0, 1), (6, 4, 9.5, 2), (6, 4, 6.5, 3), (6, 4, 8.5, 4), (6, 4, 10.0, 5),
(6, 7, 9.0, 1), (6, 7, 9.0, 2), (6, 7, 9.0, 3), (6, 7, 9.0, 4), (6, 7, 9.0, 5),
(7, 1, 8.0, 1), (7, 1, 8.5, 2), (7, 1, 8.5, 3), (7, 1, 8.0, 4), (7, 1, 8.0, 5),
(7, 3, 9.0, 1), (7, 3, 9.0, 2), (7, 3, 9.0, 3), (7, 3, 9.0, 4), (7, 3, 9.0, 5),
(7, 4, 7.0, 1), (7, 4, 10.0, 2), (7, 4, 7.0, 3), (7, 4, 8.0, 4), (7, 4, 7.0, 5),
(7, 7, 9.5, 1), (7, 7, 7.5, 2), (7, 7, 10.0, 3), (7, 7, 9.5, 4), (7, 7, 9.5, 5),
(8, 1, 9.5, 1), (8, 1, 6.5, 2), (8, 1, 8.5, 3), (8, 1, 10.0, 4), (8, 1, 9.5, 5),
(8, 3, 9.5, 1), (8, 3, 9.5, 2), (8, 3, 7.5, 3), (8, 3, 10.0, 4), (8, 3, 9.5, 5),
(8, 4, 9.0, 1), (8, 4, 9.0, 2), (8, 4, 9.0, 3), (8, 4, 9.0, 4), (8, 4, 9.0, 5),
(8, 7, 8.5, 1), (8, 7, 8.5, 2), (8, 7, 8.0, 3), (8, 7, 8.0, 4), (8, 7, 8.5, 5),
(9, 2, 8.5, 1), (9, 2, 10.0, 2), (9, 2, 9.5, 3), (9, 2, 6.5, 4), (9, 2, 8.5, 5),
(9, 3, 8.0, 1), (9, 3, 8.5, 2), (9, 3, 8.5, 3), (9, 3, 8.0, 4), (9, 3, 8.0, 5),
(9, 5, 9.0, 1), (9, 5, 9.0, 2), (9, 5, 9.0, 3), (9, 5, 9.0, 4), (9, 5, 9.0, 5),
(9, 6, 7.0, 1), (9, 6, 10.0, 2), (9, 6, 7.0, 3), (9, 6, 8.0, 4), (9, 6, 7.0, 5),
(10, 2, 8.0, 1), (10, 2, 7.0, 2), (10, 2, 10.0, 3), (10, 2, 7.0, 4), (10, 2, 8.0, 5),
(10, 3, 9.5, 1), (10, 3, 6.5, 2), (10, 3, 8.5, 3), (10, 3, 10.0, 4), (10, 3, 9.5, 5),
(10, 5, 9.5, 1), (10, 5, 9.5, 2), (10, 5, 7.5, 3), (10, 5, 10.0, 4), (10, 5, 9.5, 5),
(10, 6, 9.0, 1), (10, 6, 9.0, 2), (10, 6, 9.0, 3), (10, 6, 9.0, 4), (10, 6, 9.0, 5),
(11, 2, 9.0, 1), (11, 2, 9.0, 2), (11, 2, 9.0, 3), (11, 2, 9.0, 4), (11, 2, 9.0, 5),
(11, 3, 10.0, 1), (11, 3, 7.0, 2), (11, 3, 8.0, 3), (11, 3, 7.0, 4), (11, 3, 10.0, 5),
(11, 5, 8.0, 1), (11, 5, 8.5, 2), (11, 5, 8.5, 3), (11, 5, 8.0, 4), (11, 5, 8.0, 5),
(11, 6, 7.5, 1), (11, 6, 10.0, 2), (11, 6, 9.5, 3), (11, 6, 9.5, 4), (11, 6, 7.5, 5),
(12, 2, 9.5, 1), (12, 2, 7.5, 2), (12, 2, 10.0, 3), (12, 2, 9.5, 4), (12, 2, 9.5, 5),
(12, 3, 9.0, 1), (12, 3, 9.0, 2), (12, 3, 9.0, 3), (12, 3, 9.0, 4), (12, 3, 9.0, 5),
(12, 5, 9.5, 1), (12, 5, 6.5, 2), (12, 5, 8.5, 3), (12, 5, 10.0, 4), (12, 5, 9.5, 5),
(12, 6, 8.5, 1), (12, 6, 8.0, 2), (12, 6, 8.0, 3), (12, 6, 8.5, 4), (12, 6, 8.5, 5),
(13, 2, 8.5, 1), (13, 2, 8.5, 2), (13, 2, 8.0, 3), (13, 2, 8.0, 4), (13, 2, 8.5, 5),
(13, 3, 10.0, 1), (13, 3, 9.5, 2), (13, 3, 9.5, 3), (13, 3, 7.5, 4), (13, 3, 10.0, 5),
(13, 5, 10.0, 1), (13, 5, 7.0, 2), (13, 5, 8.0, 3), (13, 5, 7.0, 4), (13, 5, 10.0, 5),
(13, 6, 8.5, 1), (13, 6, 10.0, 2), (13, 6, 9.5, 3), (13, 6, 6.5, 4), (13, 6, 8.5, 5),
(14, 2, 6.5, 1), (14, 2, 8.5, 2), (14, 2, 10.0, 3), (14, 2, 9.5, 4), (14, 2, 6.5, 5),
(14, 3, 8.0, 1), (14, 3, 8.0, 2), (14, 3, 8.5, 3), (14, 3, 8.5, 4), (14, 3, 8.0, 5),
(14, 5, 9.0, 1), (14, 5, 9.0, 2), (14, 5, 9.0, 3), (14, 5, 9.0, 4), (14, 5, 9.0, 5),
(14, 6, 8.0, 1), (14, 6, 7.0, 2), (14, 6, 10.0, 3), (14, 6, 7.0, 4), (14, 6, 8.0, 5),
(15, 2, 7.0, 1), (15, 2, 8.0, 2), (15, 2, 7.0, 3), (15, 2, 10.0, 4), (15, 2, 7.0, 5),
(15, 3, 10.0, 1), (15, 3, 9.5, 2), (15, 3, 6.5, 3), (15, 3, 8.5, 4), (15, 3, 10.0, 5),
(15, 5, 10.0, 1), (15, 5, 9.5, 2), (15, 5, 9.5, 3), (15, 5, 7.5, 4), (15, 5, 10.0, 5),
(15, 6, 9.0, 1), (15, 6, 9.0, 2), (15, 6, 9.0, 3), (15, 6, 9.0, 4), (15, 6, 9.0, 5),
(16, 2, 9.0, 1), (16, 2, 9.0, 2), (16, 2, 9.0, 3), (16, 2, 9.0, 4), (16, 2, 9.0, 5),
(16, 3, 7.0, 1), (16, 3, 10.0, 2), (16, 3, 7.0, 3), (16, 3, 8.0, 4), (16, 3, 7.0, 5),
(16, 5, 8.0, 1), (16, 5, 8.0, 2), (16, 5, 8.5, 3), (16, 5, 8.5, 4), (16, 5, 8.0, 5),
(16, 6, 9.5, 1), (16, 6, 7.5, 2), (16, 6, 10.0, 3), (16, 6, 9.5, 4), (16, 6, 9.5, 5),
(17, 2, 9.5, 1), (17, 2, 9.5, 2), (17, 2, 7.5, 3), (17, 2, 10.0, 4), (17, 2, 9.5, 5),
(17, 5, 10.0, 1), (17, 5, 9.5, 2), (17, 5, 6.5, 3), (17, 5, 8.5, 4), (17, 5, 10.0, 5),
(17, 6, 8.5, 1), (17, 6, 8.5, 2), (17, 6, 8.0, 3), (17, 6, 8.0, 4), (17, 6, 8.5, 5),
(17, 8, 9.0, 1), (17, 8, 9.0, 2), (17, 8, 9.0, 3), (17, 8, 9.0, 4), (17, 8, 9.0, 5),
(18, 2, 8.0, 1), (18, 2, 8.5, 2), (18, 2, 8.5, 3), (18, 2, 8.0, 4), (18, 2, 8.0, 5),
(18, 5, 7.0, 1), (18, 5, 10.0, 2), (18, 5, 7.0, 3), (18, 5, 8.0, 4), (18, 5, 7.0, 5),
(18, 6, 6.5, 1), (18, 6, 8.5, 2), (18, 6, 10.0, 3), (18, 6, 9.5, 4), (18, 6, 6.5, 5),
(18, 8, 9.5, 1), (18, 8, 7.5, 2), (18, 8, 10.0, 3), (18, 8, 9.5, 4), (18, 8, 9.5, 5),
(19, 2, 9.5, 1), (19, 2, 6.5, 2), (19, 2, 8.5, 3), (19, 2, 10.0, 4), (19, 2, 9.5, 5),
(19, 5, 9.0, 1), (19, 5, 9.0, 2), (19, 5, 9.0, 3), (19, 5, 9.0, 4), (19, 5, 9.0, 5),
(19, 6, 7.0, 1), (19, 6, 8.0, 2), (19, 6, 7.0, 3), (19, 6, 10.0, 4), (19, 6, 7.0, 5),
(19, 8, 8.5, 1), (19, 8, 8.5, 2), (19, 8, 8.0, 3), (19, 8, 8.0, 4), (19, 8, 8.5, 5),
(20, 2, 10.0, 1), (20, 2, 7.0, 2), (20, 2, 8.0, 3), (20, 2, 7.0, 4), (20, 2, 10.0, 5),
(20, 5, 7.5, 1), (20, 5, 10.0, 2), (20, 5, 9.5, 3), (20, 5, 9.5, 4), (20, 5, 7.5, 5),
(20, 6, 9.0, 1), (20, 6, 9.0, 2), (20, 6, 9.0, 3), (20, 6, 9.0, 4), (20, 6, 9.0, 5),
(20, 8, 6.5, 1), (20, 8, 8.5, 2), (20, 8, 10.0, 3), (20, 8, 9.5, 4), (20, 8, 6.5, 5),
(21, 2, 9.0, 1), (21, 2, 9.0, 2), (21, 2, 9.0, 3), (21, 2, 9.0, 4), (21, 2, 9.0, 5),
(21, 5, 8.5, 1), (21, 5, 8.0, 2), (21, 5, 8.0, 3), (21, 5, 8.5, 4), (21, 5, 8.5, 5),
(21, 6, 9.5, 1), (21, 6, 9.5, 2), (21, 6, 7.5, 3), (21, 6, 10.0, 4), (21, 6, 9.5, 5),
(21, 8, 7.0, 1), (21, 8, 8.0, 2), (21, 8, 7.0, 3), (21, 8, 10.0, 4), (21, 8, 7.0, 5),
(22, 2, 10.0, 1), (22, 2, 9.5, 2), (22, 2, 9.5, 3), (22, 2, 7.5, 4), (22, 2, 10.0, 5),
(22, 5, 8.5, 1), (22, 5, 10.0, 2), (22, 5, 9.5, 3), (22, 5, 6.5, 4), (22, 5, 8.5, 5),
(22, 6, 8.0, 1), (22, 6, 8.5, 2), (22, 6, 8.5, 3), (22, 6, 8.0, 4), (22, 6, 8.0, 5),
(22, 8, 9.0, 1), (22, 8, 9.0, 2), (22, 8, 9.0, 3), (22, 8, 9.0, 4), (22, 8, 9.0, 5),
(23, 2, 8.0, 1), (23, 2, 8.0, 2), (23, 2, 8.5, 3), (23, 2, 8.5, 4), (23, 2, 8.0, 5),
(23, 5, 8.0, 1), (23, 5, 7.0, 2), (23, 5, 10.0, 3), (23, 5, 7.0, 4), (23, 5, 8.0, 5),
(23, 6, 9.5, 1), (23, 6, 6.5, 2), (23, 6, 8.5, 3), (23, 6, 10.0, 4), (23, 6, 9.5, 5),
(23, 8, 9.5, 1), (23, 8, 9.5, 2), (23, 8, 7.5, 3), (23, 8, 10.0, 4), (23, 8, 9.5, 5),
(24, 2, 10.0, 1), (24, 2, 9.5, 2), (24, 2, 6.5, 3), (24, 2, 8.5, 4), (24, 2, 10.0, 5),
(24, 5, 9.0, 1), (24, 5, 9.0, 2), (24, 5, 9.0, 3), (24, 5, 9.0, 4), (24, 5, 9.0, 5),
(24, 6, 10.0, 1), (24, 6, 7.0, 2), (24, 6, 8.0, 3), (24, 6, 7.0, 4), (24, 6, 10.0, 5),
(24, 8, 8.0, 1), (24, 8, 8.5, 2), (24, 8, 8.5, 3), (24, 8, 8.0, 4), (24, 8, 8.0, 5);
