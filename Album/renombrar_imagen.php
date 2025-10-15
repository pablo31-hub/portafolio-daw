<?php
$directorio = 'albumes/';

// Verificar que se envió la imagen por GET
if (!isset($_GET['imagen'])) {
    echo "<div class='alert alert-danger text-center mt-4'>
            No se ha especificado ninguna imagen.<br>
            <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
          </div>";
    exit;
}

$imagen = $_GET['imagen'];
$ruta_imagen = $directorio . $imagen;

if (!file_exists($ruta_imagen)) {
    echo "<div class='alert alert-danger text-center mt-4'>
            La imagen no existe.<br>
            <a href='index2.php' class='btn btn-outline-secondary mt-3'>Volver</a>
          </div>";
    exit;
}

// Separa el álbum y el nombre del archivo
list($album, $archivo) = explode('/', $imagen, 2);

// Procesa el formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['nuevo_nombre']);

    if ($nuevo_nombre === '') {
        echo "<div class='alert alert-danger text-center mt-4'>
                Debes ingresar un nuevo nombre.<br>
                <a href='renombrar_imagen.php?imagen=" . urlencode($imagen) . "' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
        exit;
    }

    // Mantiene la extensión original
    $extension = pathinfo($archivo, PATHINFO_EXTENSION);
    $nuevo_archivo = $directorio . $album . '/' . $nuevo_nombre . '.' . $extension;

    // Verifica si ya existe un archivo con ese nombre
    if (file_exists($nuevo_archivo)) {
        echo "<div class='alert alert-danger text-center mt-4'>
                Ya existe una imagen con el nombre <strong>$nuevo_nombre.$extension</strong>.<br>
                <a href='renombrar_imagen.php?imagen=" . urlencode($imagen) . "' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
        exit;
    }

    // Intenta renombrar
    if (rename($ruta_imagen, $nuevo_archivo)) {
        echo "<div class='alert alert-success text-center mt-4'>
                Imagen renombrada correctamente a <strong>$nuevo_nombre.$extension</strong>.<br>
                <a href='index2.php?album=$album' class='btn btn-outline-secondary mt-3'>Volver al álbum</a>
              </div>";
    } else {
        echo "<div class='alert alert-danger text-center mt-4'>
                Error al renombrar la imagen.<br>
                <a href='renombrar_imagen.php?imagen=" . urlencode($imagen) . "' class='btn btn-outline-secondary mt-3'>Volver</a>
              </div>";
    }

    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Renombrar imagen</title>
  <link href="./assets/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4 text-center">
            <h3 class="card-title text-warning mb-4">Renombrar imagen ✏️</h3>

            <img src="<?= htmlspecialchars($ruta_imagen) ?>" alt="Imagen actual" class="img-fluid rounded mb-3">

            <p><strong>Álbum:</strong> <?= htmlspecialchars($album) ?></p>
            <p><strong>Imagen actual:</strong> <?= htmlspecialchars($archivo) ?></p>

            <form method="POST" class="mt-3">
              <div class="mb-3">
                <label class="form-label fw-semibold">Nuevo nombre (sin extensión):</label>
                <input type="text" name="nuevo_nombre" class="form-control text-center" placeholder="Ej. mi_foto_nueva" required>
              </div>

              <button type="submit" class="btn btn-warning px-4">
                <i class="bi bi-pencil"></i> Renombrar
              </button>

              <a href="index2.php?album=<?= urlencode($album) ?>" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Volver
              </a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
