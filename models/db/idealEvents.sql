-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS sistema_eventos;
USE sistema_eventos;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuario (
                                       id_usuario INT AUTO_INCREMENT PRIMARY KEY,
                                       tipo_documento ENUM('Cédula', 'Tarjeta de Identidad') NOT NULL,
    documento VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('Masculino', 'Femenino') NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de eventos
CREATE TABLE IF NOT EXISTS evento (
                                      id_evento INT PRIMARY KEY AUTO_INCREMENT,
                                      titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    ubicacion VARCHAR(200) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    imagen_nombre VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INT,
    FOREIGN KEY (creado_por) REFERENCES usuario(id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pago (
                                    id_pago INT PRIMARY KEY AUTO_INCREMENT,
                                    id_usuario INT NOT NULL,
                                    id_evento INT NOT NULL,
                                    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    monto DECIMAL(10,2) NOT NULL,
    estado_pago ENUM('pendiente', 'completado', 'rechazado') DEFAULT 'pendiente',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_evento) REFERENCES evento(id_evento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de inscripciones
CREATE TABLE IF NOT EXISTS inscripcion (
                                           id_inscripcion INT PRIMARY KEY AUTO_INCREMENT,
                                           id_usuario INT NOT NULL,
                                           id_evento INT NOT NULL,
                                           fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                           FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_evento) REFERENCES evento(id_evento),
    UNIQUE KEY unique_usuario_evento (id_usuario, id_evento)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos iniciales
-- Usuario administrador (password: admin123)
INSERT INTO usuario (
    tipo_documento, documento, nombre, apellido,
    fecha_nacimiento, genero, email, password, rol
) VALUES (
             'Cédula', '123456789', 'Admin', 'Sistema',
             '1990-01-01', 'Masculino', 'admin@example.com',
             '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
         );

-- Evento de ejemplo
INSERT INTO evento (
    titulo, descripcion, fecha, hora, ubicacion,
    categoria, precio, creado_por
) VALUES (
             'Concierto de Prueba', 'Este es un evento de prueba para el sistema',
             '2023-12-15', '20:00:00', 'Teatro Principal',
             'Concierto', 25.50, 1
         );

-- Insertar 25 usuarios (3 admin y 22 clientes)
INSERT INTO usuario (tipo_documento, documento, nombre, apellido, fecha_nacimiento, genero, email, password, rol) VALUES
-- Administradores (3)
('Cédula', '100000001', 'María', 'Gómez', '1985-05-15', 'Femenino', 'maria.admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Cédula', '100000002', 'Carlos', 'López', '1988-08-20', 'Masculino', 'carlos.admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Tarjeta de Identidad', '100000003', 'Ana', 'Rodríguez', '1992-03-10', 'Femenino', 'ana.admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),

-- Clientes (22)
('Cédula', '200000001', 'Juan', 'Pérez', '1990-01-01', 'Masculino', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000002', 'Laura', 'Martínez', '1995-02-15', 'Femenino', 'laura@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000003', 'Pedro', 'Sánchez', '1987-07-22', 'Masculino', 'pedro@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000004', 'Sofía', 'Hernández', '1993-04-18', 'Femenino', 'sofia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000005', 'Diego', 'García', '1998-11-30', 'Masculino', 'diego@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000006', 'Valeria', 'Díaz', '1991-09-12', 'Femenino', 'valeria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000007', 'Andrés', 'Moreno', '1989-06-25', 'Masculino', 'andres@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000008', 'Camila', 'Alvarez', '1997-03-08', 'Femenino', 'camila@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000009', 'Javier', 'Romero', '1994-12-15', 'Masculino', 'javier@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000010', 'Isabella', 'Torres', '1996-08-22', 'Femenino', 'isabella@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000011', 'Ricardo', 'Jiménez', '1990-05-17', 'Masculino', 'ricardo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000012', 'Gabriela', 'Ruiz', '1993-10-29', 'Femenino', 'gabriela@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000013', 'Fernando', 'Vargas', '1988-07-14', 'Masculino', 'fernando@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000014', 'Daniela', 'Mendoza', '1995-02-03', 'Femenino', 'daniela@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000015', 'Alejandro', 'Castro', '1991-11-19', 'Masculino', 'alejandro@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000016', 'Natalia', 'Ortiz', '1994-04-26', 'Femenino', 'natalia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000017', 'Roberto', 'Gutiérrez', '1997-09-08', 'Masculino', 'roberto@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000018', 'Patricia', 'Silva', '1992-12-15', 'Femenino', 'patricia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000019', 'Hugo', 'Rojas', '1989-06-22', 'Masculino', 'hugo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Tarjeta de Identidad', '200000020', 'Lucía', 'Peña', '1996-03-17', 'Femenino', 'lucia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000021', 'Oscar', 'Flores', '1993-08-29', 'Masculino', 'oscar@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente'),
('Cédula', '200000022', 'Mariana', 'Espinoza', '1990-05-14', 'Femenino', 'mariana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');

-- Insertar 25 eventos
INSERT INTO evento (titulo, descripcion, fecha, hora, ubicacion, categoria, precio, creado_por, imagen_nombre) VALUES
                                                                                                                   ('Concierto de Rock', 'Concierto de las mejores bandas de rock nacional', '2023-11-15', '20:00:00', 'Estadio Nacional', 'Concierto', 50.00, 1, 'rock-concert.jpg'),
                                                                                                                   ('Feria Tecnológica', 'Exhibición de las últimas innovaciones tecnológicas', '2023-11-20', '10:00:00', 'Centro de Convenciones', 'Tecnología', 15.00, 2, 'tech-fair.jpg'),
                                                                                                                   ('Taller de Marketing Digital', 'Aprende estrategias efectivas de marketing', '2023-11-25', '15:30:00', 'Hotel Intercontinental', 'Educación', 30.00, 3, 'marketing-workshop.jpg'),
                                                                                                                   ('Exposición de Arte Moderno', 'Obras de artistas contemporáneos destacados', '2023-12-01', '11:00:00', 'Museo de Arte Moderno', 'Arte', 20.00, 1, 'modern-art.jpg'),
                                                                                                                   ('Festival Gastronómico', 'Degustación de platos de diferentes culturas', '2023-12-05', '12:00:00', 'Parque Central', 'Gastronomía', 35.00, 2, 'food-festival.jpg'),
                                                                                                                   ('Conferencia de Inteligencia Artificial', 'Charlas sobre el futuro de la IA', '2023-12-10', '09:00:00', 'Universidad Nacional', 'Tecnología', 25.00, 3, 'ai-conference.jpg'),
                                                                                                                   ('Obra de Teatro Clásico', 'Presentación de Hamlet por compañía nacional', '2023-12-15', '19:30:00', 'Teatro Municipal', 'Teatro', 40.00, 1, 'hamlet.jpg'),
                                                                                                                   ('Maratón Ciudad', 'Carrera atlética de 10km por la ciudad', '2023-12-20', '07:00:00', 'Plaza Principal', 'Deportes', 10.00, 2, 'marathon.jpg'),
                                                                                                                   ('Taller de Fotografía', 'Aprende técnicas profesionales de fotografía', '2023-12-25', '14:00:00', 'Centro Cultural', 'Educación', 45.00, 3, 'photography.jpg'),
                                                                                                                   ('Feria del Libro', 'Presentación y venta de libros de diversos géneros', '2024-01-05', '10:00:00', 'Biblioteca Nacional', 'Literatura', 5.00, 1, 'book-fair.jpg'),
                                                                                                                   ('Concierto Sinfónico', 'Orquesta Filarmónica interpretando a Mozart', '2024-01-10', '20:30:00', 'Auditorio Nacional', 'Concierto', 60.00, 2, 'symphony.jpg'),
                                                                                                                   ('Expo Mascotas', 'Evento para amantes de las mascotas', '2024-01-15', '11:00:00', 'Parque de Exposiciones', 'Mascotas', 15.00, 3, 'pets-expo.jpg'),
                                                                                                                   ('Charla de Emprendimiento', 'Experiencias de emprendedores exitosos', '2024-01-20', '16:00:00', 'Incubadora de Negocios', 'Negocios', 20.00, 1, 'entrepreneurship.jpg'),
                                                                                                                   ('Festival de Jazz', 'Presentación de bandas de jazz internacionales', '2024-01-25', '21:00:00', 'Club de Jazz', 'Concierto', 55.00, 2, 'jazz-festival.jpg'),
                                                                                                                   ('Taller de Cocina Italiana', 'Aprende a preparar auténtica pasta italiana', '2024-02-01', '17:00:00', 'Escuela de Gastronomía', 'Gastronomía', 50.00, 3, 'cooking-class.jpg'),
                                                                                                                   ('Exposición de Robótica', 'Demostraciones de robots y tecnología avanzada', '2024-02-05', '10:00:00', 'Centro de Innovación', 'Tecnología', 12.00, 1, 'robotics.jpg'),
                                                                                                                   ('Concierto de Pop', 'Artistas pop nacionales e internacionales', '2024-02-10', '19:00:00', 'Arena Multiusos', 'Concierto', 65.00, 2, 'pop-concert.jpg'),
                                                                                                                   ('Seminario de Salud Mental', 'Charlas sobre bienestar emocional', '2024-02-15', '09:00:00', 'Hospital Central', 'Salud', 30.00, 3, 'mental-health.jpg'),
                                                                                                                   ('Feria de Artesanías', 'Productos artesanales de todo el país', '2024-02-20', '11:00:00', 'Plaza de Artesanos', 'Artesanía', 0.00, 1, 'handicrafts.jpg'),
                                                                                                                   ('Taller de Programación', 'Introducción al desarrollo de software', '2024-02-25', '14:00:00', 'Centro de Computación', 'Tecnología', 40.00, 2, 'coding.jpg'),
                                                                                                                   ('Festival de Cine', 'Proyección de películas independientes', '2024-03-01', '18:00:00', 'Cine Teatro', 'Cine', 25.00, 3, 'film-festival.jpg'),
                                                                                                                   ('Conferencia de Sostenibilidad', 'Charlas sobre desarrollo sostenible', '2024-03-05', '10:00:00', 'Centro de Convenciones', 'Medio Ambiente', 15.00, 1, 'sustainability.jpg'),
                                                                                                                   ('Exhibición de Danza', 'Compañías de danza contemporánea', '2024-03-10', '20:00:00', 'Teatro Nacional', 'Danza', 35.00, 2, 'dance.jpg'),
                                                                                                                   ('Feria de Empleo', 'Oportunidades laborales con empresas líderes', '2024-03-15', '09:00:00', 'Centro de Exposiciones', 'Empleo', 0.00, 3, 'job-fair.jpg'),
                                                                                                                   ('Taller de Redes Sociales', 'Estrategias para crecer en redes sociales', '2024-03-20', '15:00:00', 'Coworking Space', 'Marketing', 30.00, 1, 'social-media.jpg');

-- Insertar 25 pagos
INSERT INTO pago (id_usuario, id_evento, monto, estado_pago, fecha_pago) VALUES
                                                                             (4, 1, 50.00, 'completado', '2023-11-10 14:30:00'),
                                                                             (5, 2, 15.00, 'completado', '2023-11-15 10:15:00'),
                                                                             (6, 3, 30.00, 'completado', '2023-11-20 16:45:00'),
                                                                             (7, 4, 20.00, 'completado', '2023-11-25 11:20:00'),
                                                                             (8, 5, 35.00, 'completado', '2023-11-30 12:10:00'),
                                                                             (9, 6, 25.00, 'completado', '2023-12-05 09:30:00'),
                                                                             (10, 7, 40.00, 'completado', '2023-12-10 18:15:00'),
                                                                             (11, 8, 10.00, 'completado', '2023-12-15 08:00:00'),
                                                                             (12, 9, 45.00, 'completado', '2023-12-20 14:50:00'),
                                                                             (13, 10, 5.00, 'completado', '2023-12-25 10:05:00'),
                                                                             (14, 11, 60.00, 'completado', '2024-01-01 19:30:00'),
                                                                             (15, 12, 15.00, 'completado', '2024-01-05 11:45:00'),
                                                                             (16, 13, 20.00, 'completado', '2024-01-10 15:20:00'),
                                                                             (17, 14, 55.00, 'completado', '2024-01-15 20:10:00'),
                                                                             (18, 15, 50.00, 'completado', '2024-01-20 16:30:00'),
                                                                             (19, 16, 12.00, 'pendiente', '2024-01-25 10:15:00'),
                                                                             (20, 17, 65.00, 'pendiente', '2024-01-30 18:45:00'),
                                                                             (21, 18, 30.00, 'pendiente', '2024-02-05 09:10:00'),
                                                                             (22, 19, 0.00, 'completado', '2024-02-10 11:30:00'),
                                                                             (4, 20, 40.00, 'completado', '2024-02-15 13:45:00'),
                                                                             (5, 21, 25.00, 'completado', '2024-02-20 17:20:00'),
                                                                             (6, 22, 15.00, 'completado', '2024-02-25 10:00:00'),
                                                                             (7, 23, 35.00, 'completado', '2024-03-01 19:15:00'),
                                                                             (8, 24, 0.00, 'completado', '2024-03-05 09:30:00'),
                                                                             (9, 25, 30.00, 'pendiente', '2024-03-10 14:20:00');

-- Insertar 25 inscripciones
INSERT INTO inscripcion (id_usuario, id_evento, fecha_inscripcion) VALUES
                                                                       (4, 1, '2023-11-05 10:00:00'),
                                                                       (5, 2, '2023-11-10 11:30:00'),
                                                                       (6, 3, '2023-11-15 14:15:00'),
                                                                       (7, 4, '2023-11-20 09:45:00'),
                                                                       (8, 5, '2023-11-25 12:30:00'),
                                                                       (9, 6, '2023-11-30 08:20:00'),
                                                                       (10, 7, '2023-12-05 18:00:00'),
                                                                       (11, 8, '2023-12-10 06:45:00'),
                                                                       (12, 9, '2023-12-15 13:10:00'),
                                                                       (13, 10, '2023-12-20 10:30:00'),
                                                                       (14, 11, '2023-12-25 19:15:00'),
                                                                       (15, 12, '2024-01-01 11:00:00'),
                                                                       (16, 13, '2024-01-05 15:45:00'),
                                                                       (17, 14, '2024-01-10 20:30:00'),
                                                                       (18, 15, '2024-01-15 16:20:00'),
                                                                       (19, 16, '2024-01-20 10:10:00'),
                                                                       (20, 17, '2024-01-25 17:45:00'),
                                                                       (21, 18, '2024-02-01 08:30:00'),
                                                                       (22, 19, '2024-02-05 11:15:00'),
                                                                       (4, 20, '2024-02-10 14:00:00'),
                                                                       (5, 21, '2024-02-15 18:30:00'),
                                                                       (6, 22, '2024-02-20 09:15:00'),
                                                                       (7, 23, '2024-02-25 19:45:00'),
                                                                       (8, 24, '2024-03-01 08:00:00'),
                                                                       (9, 25, '2024-03-05 13:20:00');