<?php
require_once __DIR__ . '/../models/Enfrentamiento.php';
require_once __DIR__ . '/../models/TablaPosiciones.php';

class EnfrentamientosController {

  // Genera enfrentamientos para un grupo si no existen
  public static function generarEnfrentamientos($grupo_id, $conn) {
    $existen = Enfrentamiento::existenPorGrupo($grupo_id, $conn);
    if ($existen) return;

    Enfrentamiento::generarPorGrupo($grupo_id, $conn);
    TablaPosiciones::inicializarPorGrupo($grupo_id, $conn);
  }

  // Actualiza resultado de un enfrentamiento y recalcula tabla posiciones
  public static function actualizarResultado($enfrentamiento_id, $goles_local, $goles_visitante, $conn) {
    return Enfrentamiento::actualizarResultado($enfrentamiento_id, $goles_local, $goles_visitante, $conn);
  }

  // Marca partido como jugado si tiene goles registrados
  public static function marcarJugado($enfrentamiento_id, $conn) {
    return Enfrentamiento::marcarJugado($enfrentamiento_id, $conn);
  }
}