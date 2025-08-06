<?php

class Grupo {
  public static function obtenerPorEventoDeporte($evento_deporte_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM grupos WHERE evento_deporte_id = :edid ORDER BY nombre");
    $stmt->execute(['edid' => $evento_deporte_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function crear($nombre, $evento_deporte_id, $conn) {
    $stmt = $conn->prepare("INSERT INTO grupos (nombre, evento_deporte_id) VALUES (:nombre, :edid)");
    $stmt->execute(['nombre' => $nombre, 'edid' => $evento_deporte_id]);
    return $conn->lastInsertId();
  }

  public static function asignarEquipo($grupo_id, $equipo_id, $conn) {
    $stmt = $conn->prepare("INSERT INTO equipos_grupos (grupo_id, equipo_id) VALUES (:gid, :eid)");
    $stmt->execute(['gid' => $grupo_id, 'eid' => $equipo_id]);
  }
}