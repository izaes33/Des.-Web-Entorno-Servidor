<?php

        require_once "datos.php"; 
        require_once "funciones.php"; 

        $busqueda = leerCadena($_GET, "q");

        $resultados = [];
        
        if ($busqueda !== "") {

            $resultados = buscarProductos($productos, $busqueda);
        }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar - DWES Store</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

<header class="cabecera">
    <div class="contenedor">
        <h1>Buscar productos</h1>

        <nav class="navegacion">
            <a href="index.php">Inicio</a>
            <a href="compra.php">Comprar</a>
        </nav>
    </div>
</header>

<main class="contenedor">

    <section class="panel">

        <form class="formulario" action="buscar.php" method="GET">

            <div class="campo">
                <label for="q">Nombre del producto</label>

                <input
                    type="text"
                    id="q"
                    name="q"
                    placeholder="Ej.: teclado"
                >
            </div>

            <div>
                <button type="submit">
                    Buscar
                </button>
            </div>

        </form>

    </section>

    <section class="panel">
        <h2>Resultados para "<?= escapar($busqueda); ?>"</h2>

        <?php if ($resultados === []): ?>
            <p>No se han encontrado resultados.</p>

        <?php else: ?>

            <p>Se han encontrado <?= count($resultados); ?> producto(s). </p>

        <?php endif; ?>

    </section>

    <?php if ($resultados !== []): ?>
        
        <section class="grid-productos">

            <?php foreach ($resultados as $producto): ?>
                <article class="producto">
                    <h3><?= escapar($producto["nombre"]); ?></h3>
                    <p><?= escapar($producto["categoria"]); ?> €</p>
                    <p class="precio"><?= formatearPrecio($producto["precio"]); ?></p>
                </article>
            <?php endforeach; ?>

    </section>

    <?php endif; ?>

</main>

</body>
</html>