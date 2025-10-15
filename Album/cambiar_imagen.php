<?php
$directorio = 'albumes/';

$album = $_POST['album_cambio'] ?? '';
$imagen_actual = $_POST['imagen_actual_cambio'] ?? '';
$nueva_imagen = $_FILES['nueva_imagen_cambio'] ?? null;

if (!$album || !$imagen_actual || !$nueva_imagen) {
    die('<div class="alert alert-danger text-center mt-4">
            Faltan datos del formulario.<br>
            <a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a>
         </div>');
}

$ruta_album = $directorio . $album . '/';
$ruta_imagen_actual = $ruta_album . $imagen_actual;

// Verificar que el álbum exista
if (!is_dir($ruta_album)) {
    die('<div class="alert alert-danger text-center mt-4">
            El álbum seleccionado no existe.<br>
            <a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a>
         </div>');
}

// Verificar que la imagen a reemplazar exista
if (!file_exists($ruta_imagen_actual)) {
    die('<div class="alert alert-danger text-center mt-4">
            La imagen seleccionada no existe en el álbum.<br>
            <a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a>
         </div>');
}

// Verificar si hubo error al subir el archivo
if ($nueva_imagen['error'] !== UPLOAD_ERR_OK) {
    die('<div class="alert alert-danger text-center mt-4">
            Error al subir la nueva imagen.<br>
            <a href="index2.php" class="btn btn-outline-secondary mt-3">Volver</a>
         </div>');
}

// Validar extensión
$extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$tipo_mime_permitido = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$nombre_nueva = basename($nueva_imagen['name']);
$extension = strtolower(pathinfo($nombre_nueva, PATHINFO_EXTENSION));
$tipo_mime = mime_content_type($nueva_imagen['tmp_name']);

if (!in_array($extension, $extensiones_permitidas) || !in_array($tipo_mime, $tipo_mime_permitido)) {
    die("<div class='alert alert-danger text-center mt-4'>
            Solo se permiten imágenes con formato JPG, JPEG, PNG, GIF o WEBP.<br>
            <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
         </div>");
}

$ruta_nueva = $ruta_album . $nombre_nueva;

// Evita sobrescribir si existe otra imagen con ese nombre
if (file_exists($ruta_nueva) && $nombre_nueva !== $imagen_actual) {
    die("<div class='alert alert-danger text-center mt-4'>
            Ya existe una imagen con el nombre <strong>$nombre_nueva</strong> en este álbum.<br>
            <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
         </div>");
}

// reemplaza
if (unlink($ruta_imagen_actual) && move_uploaded_file($nueva_imagen['tmp_name'], $ruta_nueva)) {
    echo "<div class='alert alert-success text-center mt-4'>
            Imagen <strong>" . htmlspecialchars($imagen_actual) . "</strong> reemplazada correctamente por 
            <strong>" . htmlspecialchars($nombre_nueva) . "</strong> en el álbum 
            <strong>" . htmlspecialchars($album) . "</strong>.
          </div>
          <div class='text-center mt-3'>
            <a href='index2.php' class='btn btn-outline-secondary'>Volver</a>
          </div>";
} else {
    echo "<div class='alert alert-danger text-center mt-4'>
            Error al reemplazar la imagen.<br>
            <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
          </div>";
}
?>