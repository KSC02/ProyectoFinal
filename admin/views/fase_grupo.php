<?php
session_start();
require '../../config/conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  die("Acceso denegado");
}

if (!isset($_GET['evento_deporte_id'])) {
  die("Evento-Deporte no especificado");
}
$evento_deporte_id = intval($_GET['evento_deporte_id']);

$stmt = $conn->prepare("SELECT e.nombre AS evento, d.nombre AS deporte FROM eventos_deportes ed JOIN eventos e ON ed.evento_id = e.id JOIN deportes d ON ed.deporte_id = d.id WHERE ed.id = :id");
$stmt->execute(['id' => $evento_deporte_id]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

$nombreEvento = $info['evento'] ?? 'Evento desconocido';
$nombreDeporte = $info['deporte'] ?? 'Deporte desconocido';

require_once '../models/Grupo.php';
require_once '../models/Equipo.php';
require_once '../models/Enfrentamiento.php';
require_once '../models/TablaPosiciones.php';

$grupos = Grupo::obtenerPorEventoDeporte($evento_deporte_id, $conn);
$equipos = Equipo::obtenerPorEventoDeporte($evento_deporte_id, $conn);

$flash_error = $_SESSION['error'] ?? null;
$flash_success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Fase de Grupos - <?= htmlspecialchars($nombreDeporte) ?> - <?= htmlspecialchars($nombreEvento) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function mostrarGrupo(id) {
      document.querySelectorAll('.grupo-contenido').forEach(el => el.classList.add('hidden'));
      document.querySelectorAll('.btn-grupo').forEach(btn => btn.classList.remove('bg-green-600', 'text-white'));
      document.getElementById(id).classList.remove('hidden');
      document.querySelector(`button[data-target="${id}"]`).classList.add('bg-green-600', 'text-white');
    }
  </script>
</head>
<body class="p-5 bg-gray-100">

<?php if ($flash_error): ?>
  <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4 border border-red-300 max-w-3xl mx-auto">
    <?= htmlspecialchars($flash_error) ?>
  </div>
<?php endif; ?>

<?php if ($flash_success): ?>
  <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 border border-green-300 max-w-3xl mx-auto">
    <?= htmlspecialchars($flash_success) ?>
  </div>
<?php endif; ?>

<h1 class="text-3xl font-extrabold mb-6 text-green-800 text-center">
  🏆 Fase de Grupos: <?= htmlspecialchars($nombreDeporte) ?> - <?= htmlspecialchars($nombreEvento) ?>
</h1>

<section class="mb-8 p-4 bg-white rounded shadow max-w-4xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">Equipos registrados</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php foreach ($equipos as $equipo): ?>
      <div class="p-2 bg-gray-200 text-center rounded"><?= htmlspecialchars($equipo['nombre_equipo']) ?></div>
    <?php endforeach; ?>
  </div>
</section>

<?php if (count($grupos) === 0): ?>
  <form method="POST" action="../actions.php" class="mb-8 text-center max-w-4xl mx-auto">
    <input type="hidden" name="accion" value="generar_grupos">
    <input type="hidden" name="evento_deporte_id" value="<?= $evento_deporte_id ?>">
    <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 text-lg font-semibold">
      Generar Grupos
    </button>
  </form>
<?php else: ?>
  <?php
  $gruposUnicos = [];
  foreach ($grupos as $grupo) {
    $gruposUnicos[$grupo['nombre']] = $grupo['id'];
  }
  ?>
  <div class="flex gap-2 mb-4 justify-center flex-wrap max-w-4xl mx-auto">
    <?php $i = 0; foreach ($gruposUnicos as $nombreGrupo => $grupoId): ?>
      <?php $gid = "grupo_" . $grupoId; ?>
      <button class="btn-grupo px-4 py-2 rounded border border-green-600 font-semibold hover:bg-green-600 hover:text-white <?= $i === 0 ? 'bg-green-600 text-white' : '' ?>" data-target="<?= $gid ?>" type="button" onclick="mostrarGrupo('<?= $gid ?>')">
        <?= htmlspecialchars($nombreGrupo) ?>
      </button>
      <?php $i++; ?>
    <?php endforeach; ?>
  </div>

  <?php $i = 0; foreach ($gruposUnicos as $nombreGrupo => $grupoId): ?>
    <?php $gid = "grupo_" . $grupoId; ?>

    <?php
      $stmt = $conn->prepare("SELECT e.nombre_equipo FROM equipos e JOIN equipos_grupos eg ON e.id = eg.equipo_id WHERE eg.grupo_id = :gid");
      $stmt->execute(['gid' => $grupoId]);
      $equiposGrupo = $stmt->fetchAll(PDO::FETCH_COLUMN);

      $stmt = $conn->prepare("SELECT ef.*, el.nombre_equipo AS local_nombre, ev.nombre_equipo AS visitante_nombre FROM enfrentamientos ef JOIN equipos el ON ef.equipo_local_id = el.id JOIN equipos ev ON ef.equipo_visitante_id = ev.id WHERE ef.grupo_id = :gid ORDER BY ef.id");
      $stmt->execute(['gid' => $grupoId]);
      $enfrentamientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

      TablaPosiciones::inicializarPorGrupo($grupoId, $conn);
      $tabla = TablaPosiciones::obtenerPorGrupo($grupoId, $conn);
    ?>

    <section id="<?= $gid ?>" class="grupo-contenido <?= $i === 0 ? '' : 'hidden' ?> p-6 bg-white rounded shadow mb-8 max-w-4xl mx-auto">
      <h2 class="text-2xl font-bold mb-3">Grupo: <?= htmlspecialchars($nombreGrupo) ?></h2>
      <p class="mb-4 font-semibold text-gray-700"><strong>Equipos:</strong> <?= implode(", ", $equiposGrupo) ?></p>

      <?php if (empty($enfrentamientos)): ?>
        <form method="POST" action="../actions.php" class="mb-4">
          <input type="hidden" name="accion" value="generar_enfrentamientos">
          <input type="hidden" name="grupo_id" value="<?= $grupoId ?>">
          <input type="hidden" name="evento_deporte_id" value="<?= $evento_deporte_id ?>">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Generar Enfrentamientos
          </button>
        </form>
      <?php else: ?>
        <h3 class="text-lg font-bold mb-2">Calendario de Enfrentamientos</h3>
        <table class="table-auto w-full mb-4 border text-center">
          <thead class="bg-gray-200">
            <tr>
              <th class="border px-2 py-1">Local</th>
              <th class="border px-2 py-1">Visitante</th>
              <th class="border px-2 py-1">Goles Local</th>
              <th class="border px-2 py-1">Goles Visitante</th>
              <th class="border px-2 py-1">Resultado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($enfrentamientos as $ef): ?>
              <tr>
                <td class="border px-2 py-1"><?= htmlspecialchars($ef['local_nombre']) ?></td>
                <td class="border px-2 py-1"><?= htmlspecialchars($ef['visitante_nombre']) ?></td>
                <td class="border px-2 py-1"><?= $ef['goles_local'] !== null ? $ef['goles_local'] : '-' ?></td>
                <td class="border px-2 py-1"><?= $ef['goles_visitante'] !== null ? $ef['goles_visitante'] : '-' ?></td>
                <td class="border px-2 py-1"><?= ucfirst($ef['resultado']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <h3 class="text-lg font-bold mb-2">Tabla de Posiciones</h3>
        <table class="table-auto w-full border text-center">
          <thead class="bg-gray-200">
            <tr>
              <th class="border px-2 py-1">Equipo</th>
              <th class="border px-2 py-1">PJ</th>
              <th class="border px-2 py-1">G</th>
              <th class="border px-2 py-1">E</th>
              <th class="border px-2 py-1">P</th>
              <th class="border px-2 py-1">GF</th>
              <th class="border px-2 py-1">GC</th>
              <th class="border px-2 py-1">DG</th>
              <th class="border px-2 py-1">Pts</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tabla as $fila): ?>
              <tr>
                <td class="border px-2 py-1"><?= htmlspecialchars($fila['nombre_equipo']) ?></td>
                <td class="border px-2 py-1"><?= $fila['partidos_jugados'] ?></td>
                <td class="border px-2 py-1"><?= $fila['ganados'] ?></td>
                <td class="border px-2 py-1"><?= $fila['empatados'] ?></td>
                <td class="border px-2 py-1"><?= $fila['perdidos'] ?></td>
                <td class="border px-2 py-1"><?= $fila['goles_favor'] ?></td>
                <td class="border px-2 py-1"><?= $fila['goles_contra'] ?></td>
                <td class="border px-2 py-1"><?= $fila['diferencia_goles'] ?></td>
                <td class="border px-2 py-1 font-bold"><?= $fila['puntos'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
    <?php $i++; ?>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>