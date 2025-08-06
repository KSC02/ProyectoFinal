<?php
class Equipo {
  // Obtiene todos los equipos inscritos en un evento deportivo específico
  public static function obtenerPorEventoDeporte($evento_deporte_id, $conn) {
    $stmt = $conn->prepare("SELECT id, nombre_equipo FROM equipos WHERE evento_deporte_id = :edid ORDER BY nombre_equipo");
    $stmt->execute(['edid' => $evento_deporte_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}