<?php
class Enfrentamiento
{
    // Genera enfrentamientos para un grupo (todos contra todos)
    public static function generarPorGrupo($grupo_id, $conn)
    {
        // Obtener equipos del grupo
        $stmt = $conn->prepare("SELECT equipo_id FROM equipos_grupos WHERE grupo_id = :gid");
        $stmt->execute(['gid' => $grupo_id]);
        $equipos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Crear enfrentamientos: cada equipo contra todos los demás solo una vez
        for ($i = 0; $i < count($equipos); $i++) {
            for ($j = $i + 1; $j < count($equipos); $j++) {
                $stmt = $conn->prepare("INSERT INTO enfrentamientos (grupo_id, equipo_local_id, equipo_visitante_id, resultado) VALUES (:gid, :local, :visitante, 'pendiente')");
                $stmt->execute(['gid' => $grupo_id, 'local' => $equipos[$i], 'visitante' => $equipos[$j]]);
            }
        }
    }

    // Actualiza resultado de un enfrentamiento y recalcula tabla de posiciones
    public static function actualizarResultado($enfrentamiento_id, $goles_local, $goles_visitante, $conn)
    {
        // Obtener datos del enfrentamiento
        $stmt = $conn->prepare("SELECT grupo_id, equipo_local_id, equipo_visitante_id FROM enfrentamientos WHERE id = :id");
        $stmt->execute(['id' => $enfrentamiento_id]);
        $partido = $stmt->fetch();

        if (!$partido) return false;

        // Determinar resultado
        if ($goles_local > $goles_visitante) {
            $resultado = 'local';
        } elseif ($goles_local < $goles_visitante) {
            $resultado = 'visitante';
        } else {
            $resultado = 'empate';
        }

        // Actualizar enfrentamiento
        $stmt = $conn->prepare("UPDATE enfrentamientos SET goles_local = :gl, goles_visitante = :gv, resultado = :res WHERE id = :id");
        $stmt->execute([
            'gl' => $goles_local,
            'gv' => $goles_visitante,
            'res' => $resultado,
            'id' => $enfrentamiento_id
        ]);

        // Actualizar tabla posiciones
        TablaPosiciones::recalcularPorGrupo($partido['grupo_id'], $conn);

        return true;
    }

    // Marca un partido como jugado solo si tiene goles registrados
    public static function marcarJugado($enfrentamiento_id, $conn)
    {
        $stmt = $conn->prepare("SELECT goles_local, goles_visitante FROM enfrentamientos WHERE id = :id");
        $stmt->execute(['id' => $enfrentamiento_id]);
        $row = $stmt->fetch();

        if (!$row) return false;

        if (is_numeric($row['goles_local']) && is_numeric($row['goles_visitante'])) {
            $stmt = $conn->prepare("UPDATE enfrentamientos SET resultado = 'jugado' WHERE id = :id");
            $stmt->execute(['id' => $enfrentamiento_id]);
            return true;
        }
        return false;
    }

    public static function existenPorGrupo($grupo_id, $conn)
    {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM enfrentamientos WHERE grupo_id = :gid");
        $stmt->execute(['gid' => $grupo_id]);
        return $stmt->fetchColumn() > 0;
    }

    public static function obtenerEquiposDelGrupo($grupo_id, $conn)
    {
        $stmt = $conn->prepare("SELECT equipo_id FROM equipos_grupos WHERE grupo_id = :gid");
        $stmt->execute(['gid' => $grupo_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
