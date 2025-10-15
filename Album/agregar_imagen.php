<?php
$directorio = 'albumes/';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $album = $_POST['album'] ?? '';
    $imagen = $_FILES['imagen'] ?? null;

    if (empty($album) || !$imagen) {
        echo "<div class='alert alert-danger text-center mt-4'>
                Debes seleccionar un álbum y una imagen.<br>
                <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
        exit;
    }

    // Ruta del álbum
    $carpeta_destino = $directorio . $album . '/';

    // Verificar que el álbum exista
    if (!is_dir($carpeta_destino)) {
        echo "<div class='alert alert-danger text-center mt-4'>
                El álbum seleccionado no existe.<br>
                <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
        exit;
    }

    // Nombre del archivo
    $nombre_archivo = basename($imagen['name']);
    $ruta_destino = $carpeta_destino . $nombre_archivo;

    // Verificar tipo de archivo
    $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $tipo_mime = mime_content_type($imagen['tmp_name']);
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));

    if (!in_array($extension, $extensiones_permitidas) || !str_starts_with($tipo_mime, 'image/')) {
        echo "<div class='alert alert-danger text-center mt-4'>
                Solo se permiten imágenes (JPG, PNG, GIF, WEBP).<br>
                <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
        exit;
    }

    // Verificar si ya existe
    if (file_exists($ruta_destino)) {
        echo "<div class='alert alert-danger text-center mt-4'>
                Ya existe una imagen con el nombre <strong>$nombre_archivo</strong> en este álbum.<br>
                <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
        exit;
    }

    // Subir archivo
    if (move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
        echo "<div class='alert alert-success text-center mt-4'>
                Imagen <strong>$nombre_archivo</strong> subida correctamente al álbum 
                <strong>$album</strong>.<br>
                <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver al álbum</a>
              </div>";
    } else {
        echo "<div class='alert alert-danger text-center mt-4'>
                No se pudo subir la imagen.<br>
                <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
    }
}
?>
