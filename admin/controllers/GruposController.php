<?php
require_once __DIR__ . '/../models/Grupo.php';
require_once __DIR__ . '/../models/Equipo.php';

class GruposController {
  public static function generarGrupos($evento_deporte_id, $conn) {
    $equipos = Equipo::obtenerPorEventoDeporte($evento_deporte_id, $conn);
    $numPorGrupo = 4;
    $numGrupos = ceil(count($equipos) / $numPorGrupo);

    for ($i = 0; $i < $numGrupos; $i++) {
      $nombreGrupo = "Grupo " . chr(65 + $i);
      $grupo_id = Grupo::crear($nombreGrupo, $evento_deporte_id, $conn);

      $equiposGrupo = array_slice($equipos, $i * $numPorGrupo, $numPorGrupo);
      foreach ($equiposGrupo as $equipo) {
        Grupo::asignarEquipo($grupo_id, $equipo['id'], $conn);
      }
    }
  }

  public static function obtenerGrupos($evento_deporte_id, $conn) {
    return Grupo::obtenerPorEventoDeporte($evento_deporte_id, $conn);
  }
}