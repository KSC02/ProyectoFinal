<?php
require '../../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $destacada = isset($_POST['destacada']) ? true : false;

    // Manejo de imagen
    $imagen_nombre = $_FILES['imagen']['name'];
    $imagen_temp = $_FILES['imagen']['tmp_name'];
    $ruta_destino = "../uploads/" . basename($imagen_nombre);

    if (move_uploaded_file($imagen_temp, $ruta_destino)) {
        $sql = "INSERT INTO noticias (titulo, descripcion, categoria, imagen, destacada)
                VALUES (:titulo, :descripcion, :categoria, :imagen, :destacada)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':categoria' => $categoria,
            ':imagen' => $imagen_nombre,
            ':destacada' => $destacada
        ]);
        header("Location: ../views/gestionar_noticias.php");
        exit;
    } else {
        echo "Error al subir la imagen.";
    }
} else {
    echo "Acceso no permitido.";
}