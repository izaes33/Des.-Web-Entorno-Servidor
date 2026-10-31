<?php
require_once "funciones.php";
require_once "datos.php";

$orden = leerCadena($_GET, "orden");

if ($orden === "") {
    $orden = "id";
}

$productosOrdenados = $productos;

if ($orden === "nombre") {
    usort($productosOrdenados, function (array $a, array $b): int {
        return $a["nombre"] <=> $b["nombre"];
    });
} elseif ($orden === "precio") {
    usort($productosOrdenados, function (array $a, array $b): int {
        return $a["precio"] <=> $b["precio"];
    });
} else {
    $orden = "id";
    usort($productosOrdenados, function (array $a, array $b): int {
        return $a["id"] <=> $b["id"];
    });
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DWES Store</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<header class="cabecera">
    <div class="contenedor">
        <h1>DWES Store</h1>
        <p>Versión estática en HTML y CSS</p>
        <nav class="navegacion">
            <a href="index.php">Inicio</a>
            <a href="buscar.php">Buscar</a>
            <a href="compra.php">Comprar</a>
        </nav>
    </div>
</header>

<main class="contenedor">
    <section class="panel">
        <h2>Catálogo</h2>
        <p>Orden actual: <?= escapar($orden) ?></p>
        <nav class="navegacion">
            <a href="index.php?orden=id">Por ID</a>
            <a href="index.php?orden=nombre">Por Nombre</a>
            <a href="index.php?orden=precio">Por Precio</a>
        </nav>
    </section>

    <section class="grid-productos">
        <?php foreach ($productosOrdenados as $producto): ?>
            <article class="producto">
                <h2><?= escapar($producto["nombre"]) ?></h2>

                <p>Categoria: <?= escapar($producto["categoria"]) ?></p>

                <p class="precio"><?= formatearPrecio($producto["precio"]) ?></p>

                <p>Stock: <?= $producto["stock"] ?></p>

                <p class="estado <?= obtenerClaseEstado($producto["stock"]) ?>">
                    Estado: <?= obtenerEstadoStock($producto["stock"]) ?>
                </p>

                <a class="boton" href="producto.php?id=<?= $producto["id"] ?>">Ver Producto</a>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<footer class="pie">
    <div class="contenedor">
        Proyecto de Desarrollo Web en Entorno Servidor
    </div>
</footer>

</body>

</html>