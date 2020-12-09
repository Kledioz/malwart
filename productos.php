<?php

include_once "connections/conn.php";
include_once "clases/producto.php";

$busqueda = "";

if (isset($_GET['busqueda'])) {
  $busqueda = "WHERE nombre LIKE '%" . mysqli_escape_string($mysqli, $_GET['busqueda']) . "%'";
}

$productos = [];

$query = "SELECT * FROM productos " . $busqueda;
if ($result = $mysqli->query($query)) {
  while ($res = mysqli_fetch_array($result)) {
    $prd = new Producto();
    $prd->id = $res['id'];
    $prd->nombre = $res['nombre'];
    $prd->precio = $res['precio'];
    $prd->existencia = $res['existencia'];
    $prd->departamento = $res['departamento'];
    $prd->descripcion = $res['descripcion'];
    $prd->imagen = $res['imagen'];
    array_push($productos, $prd);
  }
  $result->free_result();
}
$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">

<!-- Head -->
<?php
$selectedPage = "Productos";
include "head.html"
?>

<body>
  <!-- Navigation -->
  <?php
  include "navbar.php";
  ?>

  <!-- Page Content -->
  <div class="container mt-5 mb-5">

    <div>
      <h1 class="display-4 text-center">Productos</h1>

      <form method="get" class="form-inline mb-2">
        <input class="form-control mr-sm-2" name="busqueda" type="search" placeholder="Buscar un producto">
        <input class="btn btn-outline-success my-2 my-sm-0" type="submit" />
      </form>

      <div class="row">

        <?php
        foreach ($productos as $prod) { 
          $url = "verProducto.php?id=".$prod->id
          ?>
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
              <a href="<?php echo $url ?>"><img class="card-img-top" src="
                  <?php echo 'data:image/jpeg;base64,' . base64_encode($prod->imagen) ?>
                  " alt=""></a>
              <div class="card-body">
                <h4 class="card-title">
                  <a href="<?php echo $url ?>"><?php echo $prod->nombre ?></a>
                </h4>
                <h5>$<?php echo $prod->precio ?></h5>
                <p class="card-text"><?php echo $prod->descripcion ?></p>
              </div>
              <div class="card-footer">
                <small class="text-muted">&#9733; &#9733; &#9733; &#9733; &#9734;</small>
              </div>
            </div>
          </div>
        <?php
        } ?>

      </div>
    </div>

  </div>

  <!-- Footer -->
  <?php
  include "footer.html"
  ?>

</body>

</html>