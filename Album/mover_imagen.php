<?php
$directorio = 'albumes/';

$album_origen = $_POST['album_origen_mover'] ?? '';
$album_destino = $_POST['album_destino_mover'] ?? '';
$imagen = $_POST['imagen_mover'] ?? '';

if (!$album_origen || !$album_destino || !$imagen) {
    die('<div class="alert alert-danger text-center mt-4">Faltan datos del formulario.<br><a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a></div>');
}

$ruta_origen = $directorio . $album_origen . '/' . $imagen;
$ruta_destino = $directorio . $album_destino . '/' . $imagen;

if (str_contains($album_origen, '..') || str_contains($album_destino, '..') || str_contains($imagen, '..')) {
    die('<div class="alert alert-danger text-center mt-4">Ruta no válida.<br><a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a></div>');
}

if (!file_exists($ruta_origen)) {
    die('<div class="alert alert-danger text-center mt-4">La imagen no existe en el álbum de origen.<br><a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a></div>');
}

if (file_exists($ruta_destino)) {
    die('<div class="alert alert-warning text-center mt-4">Ya existe una imagen con ese nombre en el álbum destino.<br><a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a></div>');
}

if (rename($ruta_origen, $ruta_destino)) {
    echo "<div class='alert alert-success text-center mt-4'>
            Imagen <strong>" . htmlspecialchars($imagen) . "</strong> movida de 
            <strong>" . htmlspecialchars($album_origen) . "</strong> a 
            <strong>" . htmlspecialchars($album_destino) . "</strong> correctamente.
          </div>
          <div class='text-center mt-3'>
            <a href='index2.php' class='btn btn-outline-secondary'>Volver</a>
          </div>";
} else {
    echo "<div class='alert alert-danger text-center mt-4'>
            Error al mover la imagen.<br>
            <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
          </div>";
}
?>