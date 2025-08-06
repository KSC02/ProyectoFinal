-- Migrations will appear here as you chat with AI

create table deportes (
  id bigint primary key generated always as identity,
  nombre text not null,
  reglas text
);

create table enfrentamientos (
  id bigint primary key generated always as identity,
  grupo_id bigint,
  equipo_local_id bigint,
  equipo_visitante_id bigint,
  goles_local int,
  goles_visitantes int,
  resultado text
);

create table equipos (
  id bigint primary key generated always as identity,
  nombre_equipo text not null,
  facultad text,
  evento_deporte_id bigint
);

create table equipos_grupos (
  id bigint primary key generated always as identity,
  grupo_id bigint,
  equipo_id bigint
);

create table eventos (
  id bigint primary key generated always as identity,
  nombre text not null,
  descripcion text,
  fecha_inicio date,
  fecha_fin date
);

create table eventos_deportes (
  id bigint primary key generated always as identity,
  evento_id bigint,
  deporte_id bigint
);

create table grupos (
  id bigint primary key generated always as identity,
  nombre text not null,
  evento_deporte_id bigint
);

create table grupos_equipos (
  id bigint primary key generated always as identity,
  grupo_id bigint,
  equipo_id bigint
);

create table integrantes (
  id bigint primary key generated always as identity,
  equipo_id bigint,
  nombre text not null,
  cedula text,
  telefono text
);

create table tabla_posiciones (
  id bigint primary key generated always as identity,
  grupo_id bigint,
  equipo_id bigint,
  partidos_jugados int,
  ganados int,
  empatados int,
  perdidos int,
  goles_favor int,
  goles_contra int,
  diferencia_goles int,
  puntos int
);

create table usuarios (
  id bigint primary key generated always as identity,
  nombre_usuario text not null,
  "contraseña" text not null,
  rol_usuario text
);

create table usuarios_equipos (
  id bigint primary key generated always as identity,
  usuario_id bigint,
  equipo_id bigint,
  rol text
);

CREATE TABLE noticias (
    id SERIAL PRIMARY KEY,
    titulo TEXT NOT NULL,
    descripcion TEXT NOT NULL,
    categoria VARCHAR(50),
    imagen TEXT,
    destacada BOOLEAN DEFAULT FALSE,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

alter table enfrentamientos
add constraint fk_grupo_id foreign key (grupo_id) references grupos (id),
add constraint fk_equipo_local_id foreign key (equipo_local_id) references equipos (id),
add constraint fk_equipo_visitante_id foreign key (equipo_visitante_id) references equipos (id);

alter table equipos
add constraint fk_evento_deporte_id foreign key (evento_deporte_id) references eventos_deportes (id);

alter table equipos_grupos
add constraint fk_grupo_id foreign key (grupo_id) references grupos (id),
add constraint fk_equipo_id foreign key (equipo_id) references equipos (id);

alter table eventos_deportes
add constraint fk_evento_id foreign key (evento_id) references eventos (id),
add constraint fk_deporte_id foreign key (deporte_id) references deportes (id);

alter table grupos
add constraint fk_evento_deporte_id foreign key (evento_deporte_id) references eventos_deportes (id);

alter table grupos_equipos
add constraint fk_grupo_id foreign key (grupo_id) references grupos (id),
add constraint fk_equipo_id foreign key (equipo_id) references equipos (id);

alter table integrantes
add constraint fk_equipo_id foreign key (equipo_id) references equipos (id);

alter table tabla_posiciones
add constraint fk_grupo_id foreign key (grupo_id) references grupos (id),
add constraint fk_equipo_id foreign key (equipo_id) references equipos (id);

alter table usuarios_equipos
add constraint fk_usuario_id foreign key (usuario_id) references usuarios (id),
add constraint fk_equipo_id foreign key (equipo_id) references equipos (id);

ALTER TABLE usuarios
ADD CONSTRAINT rol_usuario_check CHECK (rol_usuario IN ('admin', 'jugador'));

-- Activar extensión pgcrypto si no está activa
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Insertar usuario admin con contraseña hasheada
INSERT INTO usuarios (nombre_usuario, contraseña, rol_usuario)
VALUES ('admin', crypt('ksoledispa02', gen_salt('bf')), 'admin');

-- Insertar algunos equipos
INSERT INTO equipos (nombre) VALUES 
('Panteras Negras'), 
('Pumas del Sur'), 
('Tigres FC'), 
('Rápidos y Furiosos'),
('Guerreros del Sol'), 
('Cóndores de Fuego'),
('Águilas Andinas'), 
('Dragones de Acero');

