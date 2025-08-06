<?php
session_start();
require_once '../config/conexion.php';  // Tu conexión PDO en $conn

require_once 'controllers/GruposController.php';
require_once 'controllers/EnfrentamientosController.php';

// Verificar rol admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    die("Acceso denegado");
}

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die("Método no permitido");
}

$accion = $_POST['accion'] ?? null;
if (!$accion) {
    http_response_code(400);
    die("Acción no especificada");
}

// Evento deporte para casi todas las acciones
$evento_deporte_id = $_POST['evento_deporte_id'] ?? null;
if (!$evento_deporte_id && !in_array($accion, ['actualizar_resultado', 'marcar_jugado'])) {
    http_response_code(400);
    die("Evento-Deporte no especificado");
}

// Función helper para redireccionar con mensajes
function redirigirConMensaje($url, $tipo, $mensaje) {
    $_SESSION[$tipo] = $mensaje;
    header("Location: $url");
    exit;
}

switch ($accion) {
    case 'generar_grupos':
        GruposController::generarGrupos($evento_deporte_id, $conn);
        redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'success', "Grupos generados correctamente.");
        break;

    case 'generar_enfrentamientos':
        $grupo_id = intval($_POST['grupo_id'] ?? 0);
        if (!$grupo_id) {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'error', "Grupo no especificado");
        }
        EnfrentamientosController::generarEnfrentamientos($grupo_id, $conn);
        redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'success', "Enfrentamientos generados correctamente.");
        break;

    case 'actualizar_resultado':
        $enfrentamiento_id = intval($_POST['enfrentamiento_id'] ?? 0);
        $goles_local = isset($_POST['goles_local']) ? intval($_POST['goles_local']) : -1;
        $goles_visitante = isset($_POST['goles_visitante']) ? intval($_POST['goles_visitante']) : -1;

        if (!$enfrentamiento_id || $goles_local < 0 || $goles_visitante < 0) {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'error', "Datos de resultado incompletos o inválidos.");
        }

        $ok = EnfrentamientosController::actualizarResultado($enfrentamiento_id, $goles_local, $goles_visitante, $conn);

        if ($ok) {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'success', "Resultado actualizado correctamente.");
        } else {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'error', "Error al actualizar resultado.");
        }
        break;

    case 'marcar_jugado':
        $enfrentamiento_id = intval($_POST['enfrentamiento_id'] ?? 0);
        if (!$enfrentamiento_id) {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'error', "Enfrentamiento no especificado.");
        }

        $ok = EnfrentamientosController::marcarJugado($enfrentamiento_id, $conn);
        if ($ok) {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'success', "Partido marcado como jugado.");
        } else {
            redirigirConMensaje("views/fase_grupo.php?evento_deporte_id=$evento_deporte_id", 'error', "No se pudo marcar el partido como jugado. Verifica que los goles estén ingresados.");
        }
        break;

    default:
        http_response_code(400);
        die("Acción no reconocida");
}