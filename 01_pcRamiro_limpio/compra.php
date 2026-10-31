<?php

require_once "datos.php";
require_once "funciones.php";

$errores = [];

$compraRealizada = false;

$resultadoCompra = null;

$productoSeleccionado = null;

$nombre = "";
$email = "";

$unidades = 1;

$productoId = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = leerCadena($_POST, "nombre");
    if ($nombre === "") {
        $errores[] = "Debes de introducir un nombre";
    }

    $emailBruto = leerCadena($_POST, "email");
    $email = $emailBruto;

    $emailValidado = filter_var($emailBruto,FILTER_VALIDATE_EMAIL);
    if ($emailValidado === false) {
        $errores[] = "Correo electrónico con formato incorrecto";
    } else {
        $email = $emailValidado;
    }


    $productoIdValidado = filter_var($_POST["producto"] ?? "", FILTER_VALIDATE_INT);

    if ($productoIdValidado === false || $productoIdValidado < 1) {
        $errores[] = "Debes de seleccionar un producto válido";
    } else {
        $productoId = $productoIdValidado;

        $productoSeleccionado = buscarProductoPorId($productos, $productoId);
        if ($productoSeleccionado === null) {
            $errores[] = "El producto seleccionado no existe.";
        }
    }

    $unidadesValidadas = filter_var(
        $_POST["unidades"] ?? "",
        FILTER_VALIDATE_INT
    );

    if (
        $unidadesValidadas === false
        ||
        $unidadesValidadas < 1
    ) {

        $errores[] =
            "El número de unidades debe de ser un entero mayor que cero";

    } else {

        $unidades = $unidadesValidadas;

    }

    if (
        $productoSeleccionado !== null
        &&
        $unidades > $productoSeleccionado["stock"]
    ) {

        $errores[] = "No hay stock suficiente";

    }

    if ($errores === []) {

        $resultadoCompra = calcularCompra(
            $productoSeleccionado["precio"],
            $unidades
        );

        $compraRealizada = true;

    }

}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprar - DWES Store</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<header class="cabecera">

    <div class="contenedor">

        <h1>Realizar compra</h1>

        <nav class="navegacion">

            <a href="index.php">Inicio</a>

            <a href="buscar.php">Buscar</a>

        </nav>

    </div>

</header>

<main class="contenedor">

    <?php if ($compraRealizada): ?>

        <section class="panel">

            <h2>Compra calculada correctamente</h2>
            <table class="resumen">
                <tr><th>Cliente</th><td><?= escapar($nombre) ?></td></tr>
                <tr><th>Email</th><td><?= escapar($email) ?></td></tr>
                <tr><th>Producto</th><td><?= escapar($productoSeleccionado["nombre"]) ?></td></tr>
                <tr><th>Precio unitario</th><td><?= formatearPrecio($productoSeleccionado["precio"]) ?></td></tr>
                <tr><th>Unidades</th><td><?= $unidades ?></td></tr>
                <tr><th>Subtotal</th><td><?= formatearPrecio($resultadoCompra["subtotal"]) ?></td></tr>
                <?php if ($resultadoCompra["descuento"] > 0): ?>
                    <tr><th>Descuento</th><td><?= formatearPrecio($resultadoCompra["descuento"]) ?></td></tr>
                <?php endif; ?>
                <tr><th>Base</th><td><?= formatearPrecio($resultadoCompra["base"]) ?></td></tr>
                <tr><th>IVA</th><td><?= formatearPrecio($resultadoCompra["iva"]) ?></td></tr>
                <tr><th>Total</th><td><strong><?= formatearPrecio($resultadoCompra["total"]) ?></strong></td></tr>
            </table>
            <p><a class="boton" href="compra.php">Realizar otra compra</a></p>
        </section>

    <?php else: ?>

        <?php if ($errores !== []): ?>
            <section class="errores">
                <h2>Revisa los siguientes errores</h2>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= escapar($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

    <section class="panel">    

    <form action="compra.php" method="POST" class="formulario">

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= escapar($nombre) ?>">
        </div>

        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= escapar($email) ?>"
            >
        </div>

        <div class="campo">

            <label for="producto">Producto</label>

            <select id="producto" name="producto">

                <option value="">Selecciona un producto</option>

                <?php foreach ($productos as $producto): ?>
                    <?php
                    $seleccionado = $productoId === $producto["id"];
                    ?>

                    <option value="<?= $producto["id"] ?>" <?= $seleccionado ? "selected" : "" ?>>
                        <?= escapar($producto["nombre"]) ?> - <?= formatearPrecio($producto["precio"]) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="campo">
            <label for="unidades">Unidades</label>
            <input
                type="number"
                id="unidades"
                name="unidades"
                value="<?= $unidades ?>"
                min="1">
        </div>
        <p>A partir de <strong> <?= UNIDADES_DESCUENTO ?> </strong> unidades se aplica <strong> <?= DESCUENTO_CANTIDAD * 100 ?> %</strong> de descuento </p>

        <div>
            <button type="submit">
                Calcular compra
            </button>
        </div>

    </form>
   </section>

    <?php endif; ?>

</main>

</body>
</html>