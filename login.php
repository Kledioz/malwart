<?php
session_start();
$error = [];
if (isset($_SESSION)) {
  if (isset($_SESSION['id_user'])) {
    // header('Location: pane.php');
  }
}

if (isset($_POST['send-login'])) {
  include_once "connections/conn.php";
  include_once "connections/funciones.php";

  // Validacion de correo
  if (isset($_POST['correo'])) {
    if ($_POST['correo'] == '') {
      $error[] = "No se ingreso correo";
    } else {
      if (!filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL)) {
        $error[] = "El e-mail no es valido";
      }
    }
  } else {
    $error[] = "No se ingreso correo";
  }

  // Validacion de contraseña
  if ((!isset($_POST['pass'])) || $_POST['pass'] == '') {
    $error[] = "No se ingreso la contraseña";
  }

  if (count($error) == 0) {
    $email = trim($_POST['correo']);
    $pass = trim($_POST['pass']);

    $id_o_error = cargarUsuario($email, $pass);

    if (!is_array($id_o_error)) {
      $_SESSION['id_user'] = $id_o_error;
      header('Location: productos.php');
    } else {
      $error = array_merge($error, $id_o_error);
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<!-- Head -->
<?php
$selectedPage = "Cuentas - Ingreso";
include "head.html"
?>

<body>
  <!-- Navigation -->
  <?php
  include "navbar.php";
  ?>

  <!-- Page Content -->
  <div class="container mt-5 mb-5 col-sm-11 col-md-8 col-lg-4 col-xl-4">
    </br></br>
    <div class="shadow card" style="border-top: 10px solid #007BFF">
      <div class="card-body">
        <h4 class="card-title text-center">Inicio de sesión</h4>
        <hr>
        <?php
        if (count($error) != 0) {
        ?>
          <div class="alert alert-danger" role="alert">
            <strong>Porfavor, lea los siguientes errores:</strong>
            <ul>
              <?php
              foreach ($error as $mensaje) {
              ?>
                <li><?php echo $mensaje ?></li>
              <?php
              }
              ?>
            </ul>
          </div>
        <?php
        }
        ?>
        <form method="POST">
          <label><i class="fa fa-envelope"></i> Correo electronico</label>
          <input name="correo" type="email" class="form-control" placeholder="tucorreo@gmail.com">
          <br>
          <label><i class="fa fa-asterisk"></i> Contraseña</label>
          <input name="pass" type="password" class="form-control" placeholder="*******">
          <br>
          <button name="send-login" type="submit" class="btn btn-primary btn-lg ml-auto d-block">Iniciar sesión</button>
        </form>
        <hr>
        <p>No tienes cuenta?</p>
        <a class="btn btn-info" href="signup.php" role="button">Crear cuenta</a>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php
  include "footer.html"
  ?>

</body>

</html>