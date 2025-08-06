<?php
class TablaPosiciones
{
    // Inicializa la tabla de posiciones para los equipos de un grupo
    public static function inicializarPorGrupo($grupo_id, $conn)
    {
        $stmt = $conn->prepare("SELECT equipo_id FROM equipos_grupos WHERE grupo_id = :gid");
        $stmt->execute(['gid' => $grupo_id]);
        $equipos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($equipos as $equipo_id) {
            // Solo insertar si no existe
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM tabla_posiciones WHERE grupo_id = :gid AND equipo_id = :eid");
            $stmt_check->execute(['gid' => $grupo_id, 'eid' => $equipo_id]);
            if ($stmt_check->fetchColumn() == 0) {
                $stmt_insert = $conn->prepare("INSERT INTO tabla_posiciones (grupo_id, equipo_id, partidos_jugados, ganados, empatados, perdidos, goles_favor, goles_contra, diferencia_goles, puntos) VALUES (:gid, :eid, 0, 0, 0, 0, 0, 0, 0, 0)");
                $stmt_insert->execute(['gid' => $grupo_id, 'eid' => $equipo_id]);
            }
        }
    }

    // Recalcula toda la tabla de posiciones para un grupo según los resultados
    public static function recalcularPorGrupo($grupo_id, $conn)
    {
        // Resetear tabla posiciones
        $stmt = $conn->prepare("UPDATE tabla_posiciones SET partidos_jugados=0, ganados=0, empatados=0, perdidos=0, goles_favor=0, goles_contra=0, diferencia_goles=0, puntos=0 WHERE grupo_id = :gid");
        $stmt->execute(['gid' => $grupo_id]);

        // Obtener todos los enfrentamientos con resultados válidos
        $stmt = $conn->prepare("SELECT * FROM enfrentamientos WHERE grupo_id = :gid AND resultado IN ('local', 'visitante', 'empate', 'jugado')");
        $stmt->execute(['gid' => $grupo_id]);
        $partidos = $stmt->fetchAll();

        foreach ($partidos as $p) {
            // Obtener posiciones actuales local y visitante
            $stmt_local = $conn->prepare("SELECT * FROM tabla_posiciones WHERE grupo_id = :gid AND equipo_id = :eid");
            $stmt_local->execute(['gid' => $grupo_id, 'eid' => $p['equipo_local_id']]);
            $local = $stmt_local->fetch();

            $stmt_visitante = $conn->prepare("SELECT * FROM tabla_posiciones WHERE grupo_id = :gid AND equipo_id = :eid");
            $stmt_visitante->execute(['gid' => $grupo_id, 'eid' => $p['equipo_visitante_id']]);
            $visitante = $stmt_visitante->fetch();

            // Actualizar partidos jugados y goles
            $stmt_update = $conn->prepare("UPDATE tabla_posiciones SET partidos_jugados = partidos_jugados + 1, goles_favor = goles_favor + :gf, goles_contra = goles_contra + :gc WHERE grupo_id = :gid AND equipo_id = :eid");

            // Local
            $stmt_update->execute(['gf' => $p['goles_local'], 'gc' => $p['goles_visitante'], 'gid' => $grupo_id, 'eid' => $p['equipo_local_id']]);
            // Visitante
            $stmt_update->execute(['gf' => $p['goles_visitante'], 'gc' => $p['goles_local'], 'gid' => $grupo_id, 'eid' => $p['equipo_visitante_id']]);

            // Actualizar ganados/empatados/perdidos y puntos para local
            $ganados = $local['ganados'];
            $empatados = $local['empatados'];
            $perdidos = $local['perdidos'];
            $puntos = $local['puntos'];

            // Y para visitante
            $v_ganados = $visitante['ganados'];
            $v_empatados = $visitante['empatados'];
            $v_perdidos = $visitante['perdidos'];
            $v_puntos = $visitante['puntos'];

            if ($p['resultado'] === 'local') {
                $ganados++;
                $puntos += 3;
                $v_perdidos++;
            } elseif ($p['resultado'] === 'visitante') {
                $perdidos++;
                $v_ganados++;
                $v_puntos += 3;
            } elseif ($p['resultado'] === 'empate') {
                $empatados++;
                $puntos++;
                $v_empatados++;
                $v_puntos++;
            }

            // Actualizar tabla posiciones local
            $stmt_up_local = $conn->prepare("UPDATE tabla_posiciones SET ganados = :g, empatados = :e, perdidos = :p, puntos = :pts WHERE grupo_id = :gid AND equipo_id = :eid");
            $stmt_up_local->execute(['g' => $ganados, 'e' => $empatados, 'p' => $perdidos, 'pts' => $puntos, 'gid' => $grupo_id, 'eid' => $p['equipo_local_id']]);

            // Actualizar tabla posiciones visitante
            $stmt_up_visitante = $conn->prepare("UPDATE tabla_posiciones SET ganados = :g, empatados = :e, perdidos = :p, puntos = :pts WHERE grupo_id = :gid AND equipo_id = :eid");
            $stmt_up_visitante->execute(['g' => $v_ganados, 'e' => $v_empatados, 'p' => $v_perdidos, 'pts' => $v_puntos, 'gid' => $grupo_id, 'eid' => $p['equipo_visitante_id']]);
        }

        // Finalmente, actualizar diferencia de goles para todo el grupo
        $stmt_diff = $conn->prepare("UPDATE tabla_posiciones SET diferencia_goles = goles_favor - goles_contra WHERE grupo_id = :gid");
        $stmt_diff->execute(['gid' => $grupo_id]);
    }

    // Obtener tabla de posiciones con nombre de equipos ordenada por puntos y goles
    public static function obtenerPorGrupo($grupo_id, $conn)
    {
        $stmt = $conn->prepare("
            SELECT tp.*, e.nombre_equipo 
            FROM tabla_posiciones tp
            JOIN equipos e ON tp.equipo_id = e.id
            WHERE tp.grupo_id = :gid
            ORDER BY tp.puntos DESC, tp.diferencia_goles DESC, tp.goles_favor DESC
        ");
        $stmt->execute(['gid' => $grupo_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
