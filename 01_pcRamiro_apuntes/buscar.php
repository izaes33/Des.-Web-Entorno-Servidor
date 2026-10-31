<?php

        require_once "datos.php"; 
        require_once "funciones.php"; 

        //En esta variable guardamos leerCadena, que es la función que hemos creado 
        // en funciones.php para leer el valor de "q" en el evento GET y devolverlo como un string.
        $busqueda = leerCadena($_GET, "q");

        // Inicializamos un vector de resultados donde guardaremos los resultados de la búsqueda.
        $resultados = [];
        
        // Comprueba que la búsqueda no esté vacía. Si el resultado de leerCadena() es un string vacío 
        // no se realiza la búsqueda, y sólamente si es distinto de un string vacío se realiza la búsqueda.
        // Esto evita que se realicen búsquedas innecesarias cuando no hay un término de búsqueda.
        // `!==` compara tanto el valor como el tipo.
        if ($busqueda !== "") {

            // Si hay un término de búsqueda, busca coincidencias
            // en los productos y guarda los resultados.
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

        <!--
            Este formulario es solamente HTML.
            Sin PHP no puede procesar realmente la búsqueda.
        -->
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
            <!-- Este <p> sólo se va a renderizar si no hay resultados, es decir, si
                ------------------------------------------------------------------- 
            $resultados es un array vacío. Esto es posible ya que php se mezcla con HTML
            ---------------------------------------------------------------------------- -->
            <p>No se han encontrado resultados.</p>

        <?php else: ?>

            <!-- Una función de PHP relacionada con vectores es count(), que devuelve el número 
            de elementos de un array. -->
            <p>Se han encontrado <?= count($resultados); ?> producto(s). </p>

        <!-- Esta etiqueta cierra el if que hemos abierto en la línea 49. 
            ( en java se usa {} para agrupar bloques de código) -->
        <?php endif; ?>

    </section>

    <!-- Si se han encontrado resultados, se renderiza la tabla con los resultados -->
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
    
    <!-- Minuto 38 -->

</main>

</body>
</html>