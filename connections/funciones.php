<?php
function registrarUsuario($email, $pass)
{
    global $mysqli;
    $errores = [];
    $query = "SELECT id FROM usuarios WHERE correo LIKE ? LIMIT 1;";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows != 0) {
            $errores[] = "Ya existe una cuenta con el mismo correo electronico";
        }
    }
    $stmt->close();

    if (count($errores) == 0) {
        $query = "INSERT INTO usuarios (correo,contraseña) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        if ($mysqli->error) {
            echo $mysqli->error;
        }
        $stmt->bind_param('ss', $email, $pass);
        $stmt->execute();
    }
    $stmt->close();
    return $errores;
}

function loginUsuario($email, $pass)
{
    include_once "clases/usuario.php";
    global $mysqli;
    $errores = [];
    $query = "SELECT id,contraseña FROM usuarios WHERE correo LIKE ? LIMIT 1";
    $stmt = $mysqli->prepare($query);
    if ($mysqli->error) {
        echo $mysqli->error;
    }
    $stmt->bind_param('s', $email);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows != 0) {
            if ($row = $result->fetch_assoc()) {
                if ($pass != $row['contraseña']) {
                    $errores[] = "Contraseña incorrecta";
                } else {
                    $errores = $row['id'];
                }
            }
        } else {
            $errores[] = "Cuenta inexistente";
        }
    }
    $stmt->close();
    return $errores;
}

function editUsuario($idUsuario, $nombre, $apellido, $contraseña, $direccion, $telefono)
{
    global $mysqli;
    $query = sprintf(
        "UPDATE usuarios SET nombre = '%s',apellido = '%s', contraseña = '%s',direccion = '%s' ,telefono = '%s' WHERE id ={$idUsuario}",
        mysqli_escape_string($mysqli, $nombre),
        mysqli_escape_string($mysqli, $apellido),
        mysqli_escape_string($mysqli, $contraseña),
        mysqli_escape_string($mysqli, $direccion),
        mysqli_escape_string($mysqli, $telefono)
    );
    $res = $mysqli->query($query);
    return $res;
}

function cargarUsuarioSesion()
{
    include_once "clases/usuario.php";
    include_once "clases/carrito_item.php";
    global $mysqli;
    if (!isset($_SESSION)) {
        session_start();
    }
    $sesionUsuario = null;
    if (isset($_SESSION['id_user'])) {
        $sesionUsuario = cargarUsuario($_SESSION['id_user']);
    }
    return $sesionUsuario;
}

function cargarUsuarios()
{
    include_once "clases/usuario.php";
    global $mysqli;
    $usuarios = [];
    $query = "SELECT * FROM usuarios";
    if ($result = $mysqli->query($query)) {
        while ($res = mysqli_fetch_array($result)) {
            $usr = new Usuario();
            $usr->id = $res['id'];
            $usr->correo = $res['correo'];
            $usr->contraseña = $res['contraseña'];
            $usr->nombre = $res['nombre'];
            $usr->apellido = $res['apellido'];
            $usr->direccion = $res['direccion'];
            $usr->telefono = $res['telefono'];
            $usr->rol = $res['rol'];
            array_push($usuarios, $usr);
        }
        $result->free_result();
    }
    return $usuarios;
}

function cargarUsuario($id)
{
    include_once "clases/usuario.php";
    include_once "clases/carrito_item.php";
    global $mysqli;
    $usuario = null;
    $query = sprintf(
        "SELECT * FROM usuarios WHERE id= '%s' LIMIT 1",
        mysqli_escape_string($mysqli, trim($id))
    );
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows != 0) {
            $res = mysqli_fetch_array($result);
            $usuario = new Usuario();
            $usuario->id = $res['id'];
            $usuario->correo = $res['correo'];
            $usuario->contraseña = $res['contraseña'];
            $usuario->nombre = $res['nombre'];
            $usuario->apellido = $res['apellido'];
            $usuario->direccion = $res['direccion'];
            $usuario->telefono = $res['telefono'];
            $usuario->rol = $res['rol'];
        }
        $result->free_result();
    }

    //Cargar carrito si cargo el usuario
    if ($usuario != null) {
        $query = "SELECT * FROM carritos WHERE id_usuario = {$usuario->id};";
        $carrito = [];
        if ($result = $mysqli->query($query)) {
            while ($res = mysqli_fetch_array($result)) {
                $carrito_item = new CarritoItem();
                $carrito_item->id = $res['id'];
                $carrito_item->producto = cargarProducto($res['id_producto'],true);
                $carrito_item->cantidad = $res['cantidad'];
                array_push($carrito, $carrito_item);
            }
            $result->free_result();
        }
        //Insertar array de los productos en el cesto al usuario
        $usuario->carrito = $carrito;
    }
    return $usuario;
}

function borrarUsuario($id_usuario)
{
    global $mysqli;
    $query = "DELETE FROM usuarios WHERE id=?;";
    $stmt = $mysqli->prepare($query);
    if ($mysqli->error) {
        echo $mysqli->error;
    }
    $stmt->bind_param('i', $id_usuario);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

function cargarProductos($busqueda)
{
    include_once "clases/usuario.php";
    include_once "clases/producto.php";
    global $mysqli;
    $queryBusqueda = "";
    if (isset($busqueda)) {
        $queryBusqueda = "WHERE nombre LIKE '%" . mysqli_escape_string($mysqli, $busqueda) . "%'";
    }
    $productos = [];
    $query = "SELECT * FROM productos " . $queryBusqueda;
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
    return $productos;
}

function cargarProducto($id, $cargarImagen)
{
    include_once "clases/producto.php";
    global $mysqli;
    $prd = null;
    $query = sprintf(
        "SELECT * FROM productos WHERE id= '%s' LIMIT 1",
        mysqli_escape_string($mysqli, trim($id))
    );
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows != 0) {
            $res = mysqli_fetch_array($result);
            $prd = new Producto();
            $prd->id = $res['id'];
            $prd->nombre = $res['nombre'];
            $prd->precio = $res['precio'];
            $prd->existencia = $res['existencia'];
            $prd->departamento = $res['departamento'];
            $prd->descripcion = $res['descripcion'];
            if ($cargarImagen == true) {
                $prd->imagen = $res['imagen'];
            }
        }
        $result->free_result();
    }
    return $prd;
}

function cargarReviews($idProducto)
{
    include_once "clases/review.php";
    global $mysqli;
    $reviews = [];
    $query = sprintf(
        "SELECT r.id,r.usuario_id,u.nombre,producto_id,calificacion,comentario 
        FROM reviews AS r 
        INNER JOIN usuarios AS u 
        ON r.usuario_id = u.id
        WHERE r.producto_id = %s;",
        mysqli_escape_string($mysqli, trim($idProducto))
    );
    if ($result = $mysqli->query($query)) {
        while ($res = mysqli_fetch_array($result)) {
            $rev = new Review();
            $rev->id = $res['id'];
            $rev->usuario_id = $res['usuario_id'];
            $rev->usuario_nombre = $res['nombre'];
            $rev->producto_id = $res['producto_id'];
            $rev->calificacion = $res['calificacion'];
            $rev->comentario = $res['comentario'];
            array_push($reviews, $rev);
        }
    }
    $result->free_result();
    return $reviews;
}

function enviarReseña($idUsuario, $idProducto, $calificacion, $comentario)
{
    global $mysqli;
    $query = "INSERT INTO reviews VALUES (NULL, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iiis', $idUsuario, $idProducto, $calificacion, $comentario);
    $stmt->execute();
    $stmt->close();
    return true;
}

function añadirProductoCarrito($usuario, $id_producto, $cantidad)
{
    global $mysqli;
    include_once "clases/carrito_item.php";

    //Comprobar si ya esta producto para modificar cantidad
    $carrito_item_encontrado = null;
    foreach ($usuario->carrito as $carrito_item) {
        if ($carrito_item->producto->id == $id_producto) {
            if ($carrito_item->cantidad == $cantidad) {
                return true; //Si se encontro y tiene la misma cantidad, no hacer nada
            } else {
                $carrito_item_encontrado = $carrito_item; //Modificar cantidad
            }
            break;
        }
    }

    $query = null;
    if ($carrito_item_encontrado == null) {
        //No encontrado - añadir carrito
        $query = "INSERT INTO carritos VALUES (NULL, {$usuario->id}, {$id_producto},{$cantidad})";
        // echo "añadir";
    } else {
        //Encontrado - cambiar precio
        $query = "UPDATE carritos SET cantidad={$cantidad} WHERE id_usuario={$usuario->id} AND id_producto={$id_producto}";
        // echo "cambiar";
    }
    return $mysqli->query($query);
}

function borrarProductoCarrito($usuario, $id_producto)
{
    global $mysqli;
    include_once "clases/carrito_item.php";
    $query = "DELETE FROM carritos WHERE id_producto='{$id_producto}' AND id_usuario='{$usuario->id}';";
    $res = $mysqli->query($query);
    return $res;
}

function cargarTicket($ticket_id)
{
    include_once "connections/conn.php";
    include_once "clases/ticket.php";
    include_once "clases/ticket_producto.php";
    global $mysqli;
    $tick = null;
    $query = sprintf(
        "SELECT * FROM tickets WHERE id= '%s' LIMIT 1",
        mysqli_escape_string($mysqli, trim($ticket_id))
    );
    if ($result = $mysqli->query($query)) {
        if ($result->num_rows != 0) {
            $res = mysqli_fetch_array($result);
            $tick = new Ticket();
            $tick->id = $res['id'];
            $tick->id_cliente = $res['id_cliente'];
            $tick->fecha = $res['fecha'];
        }
        $result->free_result();
    }

    if ($tick != null) {
        $ticket_productos = [];
        $query = sprintf(
            "SELECT * FROM ticket_productos WHERE id_ticket = '%s'",
            mysqli_escape_string($mysqli, trim($ticket_id))
        );
        if ($result = $mysqli->query($query)) {
            while ($res = mysqli_fetch_array($result)) {
                $ticpro = new TicketProducto();
                $ticpro->id = $res['id'];
                $ticpro->producto = cargarProducto($res['id_producto'], false);
                $ticpro->cantidad = $res['cantidad'];
                array_push($ticket_productos, $ticpro);
            }
        }
        $tick->ticket_productos = $ticket_productos;
    }
    return $tick;
}

function cargarTickets()
{
    include_once "connections/conn.php";
    include_once "clases/ticket.php";
    include_once "clases/ticket_producto.php";
    global $mysqli;
    $tickets = null;
    $query = "SELECT * FROM tickets";
    if ($result = $mysqli->query($query)) {
        $tickets = [];
        while ($res = mysqli_fetch_array($result)) {
            $ticket_productos = [];
            $tick = new Ticket();
            $tick->id = $res['id'];
            $tick->cliente = cargarUsuario($res['id_cliente']);
            $tick->fecha = $res['fecha'];
            $query2 = "SELECT * FROM ticket_productos WHERE id_ticket = " . $tick->id;
            if ($result2 = $mysqli->query($query2)) {
                while ($res2 = mysqli_fetch_array($result2)) {
                    $ticpro = new TicketProducto();
                    $ticpro->id = $res2['id'];
                    $ticpro->producto = cargarProducto($res2['id_producto'], false);
                    $ticpro->cantidad = $res2['cantidad'];
                    array_push($ticket_productos, $ticpro);
                }
            }
            $result2->free_result();
            $tick->ticket_productos = $ticket_productos;
            array_push($tickets, $tick);
        }
        $result->free_result();
    }
    return $tickets;
}
