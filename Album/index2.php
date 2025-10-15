<?php
$archivo_php = htmlentities($_SERVER['PHP_SELF']);
$directorio = 'albumes/';
$subdirectorios = array_diff(scandir($directorio), ['.', '..']);
$errores = [];
$mensaje = '';

function test_input($data){
  return htmlspecialchars(stripslashes(trim($data)));
}

/*-----------------------------------------
  CREAR NUEVO ÁLBUM
------------------------------------------*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre_nuevo_album'])) {
  $nombre_nuevo_album = test_input($_POST['nombre_nuevo_album']);
  $ruta_album = __DIR__ . '/' . $directorio . $nombre_nuevo_album;

  if ($nombre_nuevo_album == '') {
    $errores[] = "El campo <strong>nombre del álbum</strong> es obligatorio.";
  } elseif (is_dir($ruta_album)) {
    $errores[] = "El álbum <strong>$nombre_nuevo_album</strong> ya existe.";
  } else {
    if (mkdir($ruta_album, 0777, true)) {
      $mensaje = "<div class='alert alert-success text-center mt-4'>
                    Álbum <strong>$nombre_nuevo_album</strong> creado con éxito.
                  </div>";
    } else {
      $errores[] = "No se pudo crear el álbum.";
    }
  }
}

/*-----------------------------------------
  DESCARGAR IMAGEN
------------------------------------------*/
if (isset($_GET['accion']) && $_GET['accion'] === 'descargar' && isset($_GET['imagen'])) {
  $archivo = basename($_GET['imagen']);
  $album = dirname($_GET['imagen']);
  $ruta = $directorio . $album . '/' . $archivo;

  if (!file_exists($ruta)) die('<h2>El archivo no existe.</h2>');

  $info = getimagesize($ruta);
  if (!$info) die('<h2>No es una imagen válida.</h2>');

  header('Content-Description: File Transfer');
  header('Content-Type: ' . $info['mime']);
  header('Content-Disposition: attachment; filename="' . $archivo . '"');
  header('Content-Length: ' . filesize($ruta));
  readfile($ruta);
  exit;
}

/*-----------------------------------------
  CONFIRMAR ELIMINAR IMAGEN
------------------------------------------*/
if (isset($_GET['accion']) && $_GET['accion'] === 'borrar' && isset($_GET['img'])) {
  $archivo = $_GET['img'];
  $ruta = $directorio . $archivo;

  if (!file_exists($ruta)) {
    $accion = 'error';
    $mensaje = "<div class='alert alert-danger text-center mt-4'>Imagen no encontrada.</div>";
  } else {
    $accion = 'confirmar_borrado_imagen';
  }
}

/*-----------------------------------------
  CONFIRMAR ELIMINAR ÁLBUM
------------------------------------------*/
if (isset($_GET['accion']) && $_GET['accion'] === 'borrar_album' && isset($_GET['album'])) {
  $album = basename($_GET['album']);
  $ruta = $directorio . $album;

  if (!is_dir($ruta)) {
    $accion = 'error';
    $mensaje = "<div class='alert alert-danger text-center mt-4'>Álbum no encontrado.</div>";
  } else {
    $accion = 'confirmar_borrado_album';
  }
}

/*-----------------------------------------
  PROCESAR CONFIRMACIONES DE ELIMINAR
------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
  if (isset($_POST['tipo']) && $_POST['tipo'] === 'imagen') {
    $ruta = $_POST['ruta'];
    if ($_POST['confirmar'] === 'sí' && file_exists($ruta)) {
      unlink($ruta);
      $mensaje = "<div class='alert alert-success text-center mt-4'>Imagen eliminada correctamente.</div>";
    } elseif ($_POST['confirmar'] === 'no') {
      $mensaje = "<div class='alert alert-secondary text-center mt-4'>Eliminación cancelada.</div>";
    }
  }

  if (isset($_POST['tipo']) && $_POST['tipo'] === 'album') {
    $ruta = $_POST['ruta'];
    if ($_POST['confirmar'] === 'sí' && is_dir($ruta)) {
      foreach (array_diff(scandir($ruta), ['.', '..']) as $img) unlink("$ruta/$img");
      rmdir($ruta);
      $mensaje = "<div class='alert alert-success text-center mt-4'>Álbum eliminado correctamente.</div>";
    } elseif ($_POST['confirmar'] === 'no') {
      $mensaje = "<div class='alert alert-secondary text-center mt-4'>Eliminación cancelada.</div>";
    }
  }

  //  Cerrar el formulario tras procesar la acción
  unset($accion, $album, $ruta);
}
/*-----------------------------------------
  RENOMBRAR ÁLBUM
------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['album_actual'], $_POST['nuevo_nombre'])) {
  $album_actual = trim($_POST['album_actual']);
  $nuevo_nombre = trim($_POST['nuevo_nombre']);
  $ruta_actual = $directorio . $album_actual;
  $ruta_nueva = $directorio . $nuevo_nombre;

  if ($album_actual === '' || $nuevo_nombre === '') {
    $mensaje = "<div class='alert alert-danger text-center'>Debes completar ambos campos.</div>";
  } elseif (!is_dir($ruta_actual)) {
    $mensaje = "<div class='alert alert-danger text-center'>El álbum seleccionado no existe.</div>";
  } elseif (is_dir($ruta_nueva)) {
    $mensaje = "<div class='alert alert-danger text-center'>Ya existe un álbum con el nombre <strong>$nuevo_nombre</strong>.</div>";
  } elseif (rename($ruta_actual, $ruta_nueva)) {
    $mensaje = "<div class='alert alert-success text-center'>Álbum <strong>$album_actual</strong> renombrado a <strong>$nuevo_nombre</strong> con éxito.</div>";
  } else {
    $mensaje = "<div class='alert alert-danger text-center'>Error al renombrar el álbum.</div>";
  }
}

/*-----------------------------------------
  LISTA DE IMÁGENES 
------------------------------------------*/
if (isset($_GET['album'])) {
  $album = $_GET['album'];
  $imagenes = array_diff(scandir($directorio . $album), ['.', '..']);
}
?>






<!------------------------ EMPIEZA HTML------------------------->
<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="" />
  <meta
    name="author"
    content="Mark Otto, Jacob Thornton, and Bootstrap contributors" />
  <meta name="generator" content="Astro v5.13.2" />
  <title>ALBUM DE MASCOTAS</title>
  <link
    rel="canonical"
    href="https://getbootstrap.com/docs/5.3/examples/album/" />
  <script src="./assets/js/color-modes.js"></script>
  <link href="./assets/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
  <meta name="theme-color" content="#712cf9" />
  <style>
    body {
      background-image: url('fondo1.jpg');
      background-size: cover;        
      background-repeat: no-repeat;  
      background-attachment: fixed;  
      background-position: center;   
    }

    
    main, header, footer {
      background-color: rgba(255, 255, 255, 0.85);
      border-radius: 12px;
      padding: 20px;
      margin: 10px auto;
    }
  </style>


    <style>
  .album-card {
    background-color: #ffffffcc;
    transition: all 0.2s ease-in-out;
  }

  .album-card:hover {
    background-color: #0d6efd;
    color: white;
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
  }

  .album-card:hover i {
    color: white !important;
  }

  .album-card span {
    font-size: 0.95rem;
  }
</style>




  <style>
    .bd-placeholder-img {
      font-size: 1.125rem;
      text-anchor: middle;
      -webkit-user-select: none;
      -moz-user-select: none;
      user-select: none;
    }

    @media (min-width: 768px) {
      .bd-placeholder-img-lg {
        font-size: 3.5rem;
      }
    }

    .b-example-divider {
      width: 100%;
      height: 3rem;
      background-color: #0000001a;
      border: solid rgba(0, 0, 0, 0.15);
      border-width: 1px 0;
      box-shadow:
        inset 0 0.5em 1.5em #0000001a,
        inset 0 0.125em 0.5em #00000026;
    }

    .b-example-vr {
      flex-shrink: 0;
      width: 1.5rem;
      height: 100vh;
    }

    .bi {
      vertical-align: -0.125em;
      fill: currentColor;
    }

    .nav-scroller {
      position: relative;
      z-index: 2;
      height: 2.75rem;
      overflow-y: hidden;
    }

    .nav-scroller .nav {
      display: flex;
      flex-wrap: nowrap;
      padding-bottom: 1rem;
      margin-top: -1px;
      overflow-x: auto;
      text-align: center;
      white-space: nowrap;
      -webkit-overflow-scrolling: touch;
    }

    .btn-bd-primary {
      --bd-violet-bg: #712cf9;
      --bd-violet-rgb: 112.520718, 44.062154, 249.437846;
      --bs-btn-font-weight: 600;
      --bs-btn-color: var(--bs-white);
      --bs-btn-bg: var(--bd-violet-bg);
      --bs-btn-border-color: var(--bd-violet-bg);
      --bs-btn-hover-color: var(--bs-white);
      --bs-btn-hover-bg: #6528e0;
      --bs-btn-hover-border-color: #6528e0;
      --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
      --bs-btn-active-color: var(--bs-btn-hover-color);
      --bs-btn-active-bg: #5a23c8;
      --bs-btn-active-border-color: #5a23c8;
    }

    .bd-mode-toggle {
      z-index: 1500;
    }

    .bd-mode-toggle .bi {
      width: 1em;
      height: 1em;
    }

    .bd-mode-toggle .dropdown-menu .active .bi {
      display: block !important;
    }
  </style>
</head>

<body>
  <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
    <symbol id="check2" viewBox="0 0 16 16">
      <path
        d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"></path>
    </symbol>
    <symbol id="circle-half" viewBox="0 0 16 16">
      <path
        d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"></path>
    </symbol>
    <symbol id="moon-stars-fill" viewBox="0 0 16 16">
      <path
        d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"></path>
      <path
        d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"></path>
    </symbol>
    <symbol id="sun-fill" viewBox="0 0 16 16">
      <path
        d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"></path>
    </symbol>
  </svg>
  <div
    class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
    <button
      class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
      id="bd-theme"
      type="button"
      aria-expanded="false"
      data-bs-toggle="dropdown"
      aria-label="Toggle theme (auto)">
      <svg class="bi my-1 theme-icon-active" aria-hidden="true">
        <use href="#circle-half"></use>
      </svg>
      <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
    </button>
    <ul
      class="dropdown-menu dropdown-menu-end shadow"
      aria-labelledby="bd-theme-text">
      <li>
        <button
          type="button"
          class="dropdown-item d-flex align-items-center"
          data-bs-theme-value="light"
          aria-pressed="false">
          <svg class="bi me-2 opacity-50" aria-hidden="true">
            <use href="#sun-fill"></use>
          </svg>
          Light
          <svg class="bi ms-auto d-none" aria-hidden="true">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
      <li>
        <button
          type="button"
          class="dropdown-item d-flex align-items-center"
          data-bs-theme-value="dark"
          aria-pressed="false">
          <svg class="bi me-2 opacity-50" aria-hidden="true">
            <use href="#moon-stars-fill"></use>
          </svg>
          Dark
          <svg class="bi ms-auto d-none" aria-hidden="true">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
      <li>
        <button
          type="button"
          class="dropdown-item d-flex align-items-center active"
          data-bs-theme-value="auto"
          aria-pressed="true">
          <svg class="bi me-2 opacity-50" aria-hidden="true">
            <use href="#circle-half"></use>
          </svg>
          Auto
          <svg class="bi ms-auto d-none" aria-hidden="true">
            <use href="#check2"></use>
          </svg>
        </button>
      </li>
    </ul>
  </div>
  <header data-bs-theme="dark">
    <div class="collapse text-bg-dark" id="navbarHeader">
      <div class="container">
        <div class="row">
          <div class="col-sm-8 col-md-7 py-4">
            <h4>About</h4>
            <p class="text-body-secondary">
              Bienvenido a nuestro Álbum de Mascotas, un espacio creado para celebrar la alegría, la ternura y las travesuras de nuestros mejores amigos peludos, alados o con aletas.
              Aquí podrás encontrar fotografías de perros juguetones, gatos curiosos, aves coloridas, peces tranquilos y muchas otras mascotas que llenan nuestros días de amor y compañía.
            </p>
          </div>
          <div class="col-sm-4 offset-md-1 py-4">
            <h4>Contact</h4>
            <ul class="list-unstyled">
                <li class="mb-2">
                  <a href="https://x.com/home" class="text-white text-decoration-none">
                    <i class="bi bi-twitter-x me-2"></i> Follow on X
                  </a>
                </li>
                <li class="mb-2">
                  <a href="https://www.facebook.com/" class="text-white text-decoration-none">
                    <i class="bi bi-facebook me-2"></i> Like on Facebook
                  </a>
                </li>
                <li class="mb-2">
                  <a href="mailto:tucorreo@example.com" class="text-white text-decoration-none">
                    <i class="bi bi-envelope-fill me-2"></i> Email me
                  </a>
                </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="navbar navbar-dark bg-dark shadow-sm">
      <div class="container">
        <a href="#" class="navbar-brand d-flex align-items-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            aria-hidden="true"
            class="me-2"
            viewBox="0 0 24 24">
            <path
              d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
            <circle cx="12" cy="13" r="4"></circle>
          </svg>
          <strong>Album de mascotas 🐾</strong>
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarHeader"
          aria-controls="navbarHeader"
          aria-expanded="false"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>
  </header>

  <?php if (!empty($mensaje)): ?>
    <div class="container mt-4">
      <?= $mensaje ?>
    </div>
  <?php endif; ?>

  <?php if (!isset($accion)): ?>

  <main>
    <section class="py-5 text-center container">
  <div class="row py-lg-5">
    <div class="col-lg-10 mx-auto">

      <h1 class="fw-bold text-primary mb-4">
        <i class="bi bi-camera2 me-2"></i>
        Bienvenido a tu álbum de mascotas 🐾
      </h1>
      <p class="lead text-secondary mb-5">
        Aquí puedes explorar tus álbumes, agregar nuevas imágenes o gestionar tus recuerdos favoritos.
      </p>

      <!--------BOTONES PRINCIPALES ---------------->

      <div class="d-flex justify-content-center flex-wrap gap-3 mb-5">
        <!------------ Botón Ver Álbumes -------------------->
        <button class="btn btn-outline-primary btn-lg" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#albumList" 
                aria-expanded="false" 
                aria-controls="albumList">
          <i class="bi bi-eye me-2"></i> Ver álbumes
        </button>

        <!------- Botón Ver Acciones ----------------->
        <button class="btn btn-outline-dark btn-lg" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#actionButtons" 
                aria-expanded="false" 
                aria-controls="actionButtons">
          <i class="bi bi-tools me-2"></i> Ver acciones
        </button>
      </div>

      <!---------------------------GALERÍA DE ÁLBUMES ---------------------------->
      <div class="collapse" id="albumList">
        <div class="row justify-content-start g-3 mt-3">
          <?php foreach ($subdirectorios as $subdirectorio): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
              <a href="<?= $archivo_php ?>?album=<?= $subdirectorio ?>" class="text-decoration-none">
                <div class="album-card p-3 border rounded-4 shadow-sm h-100 d-flex flex-column align-items-center justify-content-center">
                  <i class="bi bi-folder-fill fs-2 text-primary mb-2"></i>
                  <span class="fw-semibold text-dark"><?= ucfirst($subdirectorio) ?></span>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!------------- BOTONES DE ACCIÓN--------------------- -->
      <div class="collapse" id="actionButtons">
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">

          <a href="<?= $archivo_php ?>?nuevo_album" class="btn btn-outline-primary btn-lg rounded-4 shadow-sm px-4">
            <i class="bi bi-folder-plus me-2"></i> Nuevo Álbum
          </a>

          <a href="<?= $archivo_php ?>?agregar_imagen" class="btn btn-outline-success btn-lg rounded-4 shadow-sm px-4">
            <i class="bi bi-cloud-upload-fill me-2"></i> Agregar Imagen
          </a>

          <a href="<?= $archivo_php ?>?renombrar_album" class="btn btn-outline-warning btn-lg rounded-4 shadow-sm px-4">
            <i class="bi bi-pencil-square me-2"></i> Renombrar Álbum
          </a>

          <a href="<?= $archivo_php ?>?cambiar_imagen" class="btn btn-outline-info btn-lg rounded-4 shadow-sm px-4">
            <i class="bi bi-image me-2"></i> Cambiar Imagen
          </a>

          <a href="<?= $archivo_php ?>?mover_imagen" class="btn btn-outline-secondary btn-lg rounded-4 shadow-sm px-4">
            <i class="bi bi-arrows-move me-2"></i> Mover Imagen
          </a>

        </div>
      </div>

    </div>
  </div>
</section>
     
    <!------------------------- CÓDIGO PARA RECORRER LOS NOMBRES DE CADA ALBUM -------------------------------------->     
    <?php if (isset($_GET['album'])) : ?>
      <?php $album = $_GET['album'] ?>
      <h2 class="text-center fw-bold text-primary mb-4"><i class="bi bi-images me-2"></i>Diferentes imágenes del álbum <?= ucfirst($album) ?></h2>
      <div class="album py-5 bg-body-tertiary">
        <div class="container">
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">


    <!---------------------------------------CÓDIGO PARA RECORRER LAS IMÁGENES------------------------------------->
<?php foreach ($imagenes as $imagen) : ?>
  <div class="col">
    <div class="card shadow-sm">
      <img src="<?= $directorio . $album . '/' . $imagen ?>"
        class="card-img-top"
        width="100%"
        height="225"
        alt="Imagen del álbum">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div class="btn-group">
            <!-- BOTÓN DE CAMBIAR NOMBRE -->
            <a href="renombrar_imagen.php?imagen=<?= urlencode($album . '/' . $imagen) ?>"
               class="btn btn-sm btn-outline-warning">
               <i class="bi bi-pencil"></i> Rename
            </a>

            <!-- BOTÓN DE DESCARGA -->
            <a href="<?= $archivo_php ?>?accion=descargar&imagen=<?= urlencode($album . '/' . $imagen) ?>"
               class="btn btn-sm btn-outline-secondary">
               Descargar
            </a>

            <!-- BOTÓN DE BORRAR -->
            <a href="index2.php?accion=borrar&img=<?= urlencode($album . '/' . $imagen) ?>"
               class="btn btn-sm btn-outline-danger">
               Borrar
            </a>
          </div>
          <small class="text-body-secondary"><?= htmlspecialchars($imagen) ?></small>
        </div>
      </div>
    </div>
  </div>
<?php endforeach; ?>



<div class="text-center mt-4">
  <a href="<?= $archivo_php ?>" class="btn btn-outline-primary">
    <i class="bi bi-arrow-left"></i> Volver
  </a>

  <a href="index2.php?accion=borrar_album&album=<?= urlencode($album) ?>"
     class="btn btn-danger ms-2">
     <i class="bi bi-trash"></i> Borrar álbum
  </a>
</div>
                
          <?php endif ?>         
          </div>
        </div>
      </div>
  </main>
  

  <!--------------------------------- FORMULARIOS ---------------------------------> 
  
                    
  <!-- FORMULARIO CREAR NUEVO ÁLBUM -->                  
  <?php if (isset($_GET['nuevo_album'])): ?>
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">
              <h3 class="card-title text-center text-primary mb-4">
                Crear nuevo álbum 📸
              </h3>

              <form action="" method="POST" class="text-center">
                <div class="mb-3">
                  <label for="nombre_nuevo_album" class="form-label fw-semibold">
                    Nombre del nuevo álbum
                  </label>
                  <input
                    type="text"
                    id="nombre_nuevo_album"
                    name="nombre_nuevo_album"
                    class="form-control text-center border-primary shadow-sm"
                    placeholder="Ej. Vacaciones 2025"
                    required>
                </div>

                <button type="submit" class="btn btn-success px-4">
                  <i class="bi bi-folder-plus"></i> Crear álbum
                </button>

                <a href="<?= $archivo_php ?>" class="btn btn-outline-secondary ms-2">
                  <i class="bi bi-arrow-left"></i> Volver
                </a><br>
                
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif ?>

<!-- FORMULARIO DE AGREGAR NUEVA IMAGEN AL ÁLBUM --> 
  
  <?php if (isset($_GET['agregar_imagen'])): ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="card-title text-center text-success mb-4">
              Agregar Imagen al Álbum 🐾
            </h3>

            <form action="agregar_imagen.php" method="POST" enctype="multipart/form-data" class="text-center">
              <div class="mb-3">
                <label class="form-label fw-semibold">Selecciona un álbum:</label>
                <select name="album" class="form-select mb-3" required>
                  <option value="">--Selecciona--</option>
                  <?php foreach ($subdirectorios as $subdirectorio): ?>
                    <?php if ($subdirectorio != '.' && $subdirectorio != '..'): ?>
                      <option value="<?= htmlspecialchars($subdirectorio) ?>">
                        <?= ucfirst($subdirectorio) ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Selecciona una imagen:</label>
                <input type="file" name="imagen" accept="image/*" class="form-control mb-3" required>
              </div>

              <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-cloud-upload"></i> Subir Imagen
              </button>

              <a href="<?= $archivo_php ?>" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Volver
              </a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- FORMULARIO DE CAMBIAR NOMBRE DE ÁLBUM -->     

<?php if (isset($_GET['renombrar_album'])): ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="card-title text-center text-warning mb-4">
              Cambiar nombre de un álbum 📁
            </h3>

            <form action="" method="POST" class="text-center">
              <div class="mb-3">
                <label class="form-label fw-semibold">Selecciona el álbum que deseas renombrar:</label>
                <select name="album_actual" class="form-select mb-3" required>
                  <option value="">--Selecciona--</option>
                  <?php foreach ($subdirectorios as $subdirectorio): ?>
                    <?php if ($subdirectorio != '.' && $subdirectorio != '..'): ?>
                      <option value="<?= htmlspecialchars($subdirectorio) ?>">
                        <?= ucfirst($subdirectorio) ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nuevo nombre del álbum:</label>
                <input type="text" name="nuevo_nombre" class="form-control mb-3" placeholder="Ej. Mis perros 2025" required>
              </div>

              <button type="submit" class="btn btn-warning px-4">
                <i class="bi bi-pencil-square"></i> Cambiar nombre
              </button>

              <a href="<?= $archivo_php ?>" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Volver
              </a>             

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!----------- FORMULARIO DE REEMPLAZAR IMAGEN ----------->    

<?php if (isset($_GET['cambiar_imagen'])): ?>
  <?php
  $albumCambioSeleccionado = $_POST['album_cambio'] ?? '';
  $imagenesAlbumCambio = [];

  if (!empty($albumCambioSeleccionado)) {
    $rutaAlbumCambio = $directorio . $albumCambioSeleccionado;
    if (is_dir($rutaAlbumCambio)) {
      $imagenesAlbumCambio = array_diff(scandir($rutaAlbumCambio), ['.', '..']);
    }
  }
  ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="card-title text-center text-info mb-4">
              Reemplazar una imagen 🔁
            </h3>

            <form action="?cambiar_imagen" method="POST" enctype="multipart/form-data" class="text-center">
              
              <!-- SELECCIONA EL ÁLBUM -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Selecciona el álbum:</label>
                <select name="album_cambio" class="form-select mb-3" required onchange="this.form.submit()">
                  <option value="">--Selecciona--</option>
                  <?php foreach ($subdirectorios as $subdirectorio): ?>
                    <?php if ($subdirectorio != '.' && $subdirectorio != '..'): ?>
                      <option value="<?= htmlspecialchars($subdirectorio) ?>" 
                        <?= $albumCambioSeleccionado === $subdirectorio ? 'selected' : '' ?>>
                        <?= ucfirst($subdirectorio) ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- MUESTRA LAS IMÁGENES DE ESE ALBUM -->
              <?php if (!empty($albumCambioSeleccionado)): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Selecciona la imagen a reemplazar:</label>
                  <select name="imagen_actual_cambio" class="form-select mb-3" required>
                    <option value="">--Selecciona una imagen--</option>
                    <?php foreach ($imagenesAlbumCambio as $img): ?>
                      <option value="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars($img) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Selecciona la nueva imagen:</label>
                  <input type="file" name="nueva_imagen_cambio" accept="image/*" class="form-control mb-3" required>
                </div>

                <button type="submit" formaction="cambiar_imagen.php" class="btn btn-info px-4">
                  <i class="bi bi-arrow-repeat"></i> Reemplazar Imagen
                </button>
              <?php endif; ?>

              <a href="<?= $archivo_php ?>" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Volver
              </a>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>



<!------------- FORMULARIO DE MOVER IMAGEN ------------>

<?php if (isset($_GET['mover_imagen'])): ?>
  
  <?php
  $albumMoverSeleccionado = $_POST['album_origen_mover'] ?? '';
  $imagenesMover = [];

  if (!empty($albumMoverSeleccionado)) {
    $rutaAlbumMover = $directorio . $albumMoverSeleccionado;
    if (is_dir($rutaAlbumMover)) {
      $imagenesMover = array_diff(scandir($rutaAlbumMover), ['.', '..']);
    }
  }
  ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="card-title text-center text-secondary mb-4">
              Mover imagen entre álbumes ↔️
            </h3>

            <form action="?mover_imagen" method="POST" enctype="multipart/form-data" class="text-center">
              
              <!-- SELECCIONA EL ÁLBUM ORIGEN -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Selecciona el álbum origen:</label>
                <select name="album_origen_mover" class="form-select mb-3" required onchange="this.form.submit()">
                  <option value="">--Selecciona--</option>
                  <?php foreach ($subdirectorios as $subdirectorio): ?>
                    <?php if ($subdirectorio != '.' && $subdirectorio != '..'): ?>
                      <option value="<?= htmlspecialchars($subdirectorio) ?>" 
                        <?= $albumMoverSeleccionado === $subdirectorio ? 'selected' : '' ?>>
                        <?= ucfirst($subdirectorio) ?>
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- MOSTRAR LAS IMÁGENES DEL ÁLBUM ORIGEN -->
              <?php if (!empty($albumMoverSeleccionado)): ?>
                <div class="mb-3">
                  <label class="form-label fw-semibold">Selecciona la imagen a mover:</label>
                  <select name="imagen_mover" class="form-select mb-3" required>
                    <option value="">--Selecciona una imagen--</option>
                    <?php foreach ($imagenesMover as $img): ?>
                      <option value="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars($img) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <!-- SELECCIONA EL ÁLBUM DESTINO -->
                <div class="mb-3">
                  <label class="form-label fw-semibold">Selecciona el álbum destino:</label>
                  <select name="album_destino_mover" class="form-select mb-3" required>
                    <option value="">--Selecciona--</option>
                    <?php foreach ($subdirectorios as $subdirectorio): ?>
                      <?php if ($subdirectorio != '.' && $subdirectorio != '..' && $subdirectorio != $albumMoverSeleccionado): ?>
                        <option value="<?= htmlspecialchars($subdirectorio) ?>">
                          <?= ucfirst($subdirectorio) ?>
                        </option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </select>
                </div>

                <button type="submit" formaction="mover_imagen.php" class="btn btn-secondary px-4">
                  <i class="bi bi-arrows-move"></i> Mover Imagen
                </button>
              <?php endif; ?>

              <a href="<?= $archivo_php ?>" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left"></i> Volver
              </a>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php endif; ?>
<!-----------------------CONFIRMAR ELIMINAR IMAGEN -------------------->
<?php if (isset($accion) && $accion === 'confirmar_borrado_imagen'): ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body text-center p-5">
            <h3 class="text-danger mb-3">
              <i class="bi bi-exclamation-triangle-fill"></i> Confirmar eliminación
            </h3>
            <p>¿Estás seguro de que quieres eliminar la imagen:
              <strong><?= htmlspecialchars(basename($archivo)) ?></strong>?
            </p>
            <img src="<?= htmlspecialchars($ruta) ?>" alt="Vista previa"
                 class="img-fluid rounded shadow-sm my-3" style="max-width: 300px;">

            <form method="post" action="" class="mt-3">
              <input type="hidden" name="tipo" value="imagen">
              <input type="hidden" name="ruta" value="<?= htmlspecialchars($ruta) ?>">
              <button type="submit" name="confirmar" value="sí" class="btn btn-danger px-4 me-2">
                <i class="bi bi-trash-fill"></i> Sí, eliminar
              </button>
              <button type="submit" name="confirmar" value="no" class="btn btn-secondary px-4">
                <i class="bi bi-x-circle"></i> No, cancelar
              </button>
            </form>

            <a href="index2.php" class="btn btn-link mt-4 text-decoration-none">
              <i class="bi bi-arrow-left"></i> Volver al álbum
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<!------------------------- CONFIRMAR ELIMINAR ÁLBUM ------------------------------->
<?php if (isset($accion) && $accion === 'confirmar_borrado_album'): ?>
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body text-center p-5">
            <h3 class="text-danger mb-3">
              <i class="bi bi-trash3-fill"></i> Confirmar eliminación de álbum
            </h3>
            <p>¿Estás seguro de que quieres eliminar el álbum:
              <strong><?= htmlspecialchars($album) ?></strong>?
            </p>
            <p class="text-muted"><em>Esto eliminará también todas las imágenes dentro del álbum.</em></p>

            <form method="post" action="" class="mt-3">
              <input type="hidden" name="tipo" value="album">
              <input type="hidden" name="ruta" value="<?= htmlspecialchars($ruta) ?>">
              <button type="submit" name="confirmar" value="sí" class="btn btn-danger px-4 me-2">
                <i class="bi bi-trash"></i> Sí, eliminar álbum
              </button>
              <button type="submit" name="confirmar" value="no" class="btn btn-secondary px-4">
                <i class="bi bi-x-circle"></i> No, cancelar
              </button>
            </form>

            <a href="index2.php" class="btn btn-link mt-4 text-decoration-none">
              <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

  <footer class="text-body-secondary py-5">
    <div class="container">
      <p class="float-end mb-1"><a href="#">Back to top</a></p>
      <p class="mb-1">
      </p>

    </div>
  </footer>
  <script
    src="./assets/dist/js/bootstrap.bundle.min.js"
    class="astro-vvvwv3sm"></script>

</body>

</html>