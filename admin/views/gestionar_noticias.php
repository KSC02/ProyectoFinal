<?php
session_start();
require '../../config/conexion.php';
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT * FROM noticias ORDER BY fecha DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Noticias</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<nav class="bg-green-700 text-white px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">⚙️ Admin - Noticias & Publicidad </h1>
    <a href="../config/logout.php" class="bg-white text-green-700 px-3 py-1 rounded">Cerrar sesión</a>
</nav>

<body class="bg-gray-100 min-h-screen">

  <div class="max-w-6xl mx-auto py-8">
    <h1 class="text-3xl font-bold text-center text-green-700 mb-6">Gestión de Noticias</h1>

    <!-- Formulario para crear noticia -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-8">
      <form action="../models/agregar_noticia.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label class="block font-semibold mb-1">Título:</label>
          <input type="text" name="titulo" required class="w-full px-4 py-2 border rounded-lg" />
        </div>

        <div>
          <label class="block font-semibold mb-1">Descripción corta:</label>
          <textarea name="descripcion" required class="w-full px-4 py-2 border rounded-lg resize-none" rows="3"></textarea>
        </div>

        <div>
          <label class="block font-semibold mb-1">Categoría (Facultad):</label>
          <select name="categoria" required class="w-full px-4 py-2 border rounded-lg">
            <option value="FCI">Facultad de Ciencias Informáticas</option>
            <option value="FCA">Facultad de Ciencias Administrativas</option>
            <option value="FCE">Facultad de Ciencias de la Educación</option>
            <!-- Agrega más según tus necesidades -->
          </select>
        </div>

        <div>
          <label class="block font-semibold mb-1">Imagen:</label>
          <input type="file" name="imagen" accept="image/*" required class="w-full border p-2 rounded-lg" />
        </div>

        <div class="flex items-center gap-3">
          <input type="checkbox" name="destacada" value="1" class="w-4 h-4" />
          <label>¿Marcar como noticia destacada?</label>
        </div>

        <div class="text-right">
          <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
            Publicar noticia
          </button>
        </div>
      </form>
    </div>

    <!-- Lista de noticias existentes -->
    <div class="space-y-6">
      <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="bg-white p-4 rounded-lg shadow flex flex-col md:flex-row gap-4">
          <img src="../uploads/<?php echo $row['imagen']; ?>" alt="Imagen noticia" class="w-full md:w-48 h-32 object-cover rounded-lg" />
          <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-800"><?php echo $row['titulo']; ?></h2>
            <p class="text-sm text-gray-500"><?php echo date("d/m/Y", strtotime($row['fecha'])); ?> - <?php echo $row['categoria']; ?></p>
            <p class="text-gray-700 mt-2"><?php echo $row['descripcion']; ?></p>
            <?php if ($row['destacada']): ?>
              <span class="inline-block mt-2 text-xs bg-yellow-300 text-yellow-800 px-2 py-1 rounded-full">
                <i class="fas fa-star"></i> Destacada
              </span>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

</body>
</html>