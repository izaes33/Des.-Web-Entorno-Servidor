<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/productos.php';
cabecera('Catálogo');
?>
<section class="intro">
    <p class="etiqueta">UNIDAD 4 · DWES</p>
    <h1>Catálogo de productos</h1>
    <p>¿Qué ocurre si intentamos añadir productos sin mantener el estado entre peticiones?</p>
</section>
<div class="productos">
    <?php foreach ($productos as $id => $producto): ?>
        <article class="tarjeta">
            <div class="ilustracion" aria-hidden="true"><?= e($producto['icono']) ?></div>
            <h2><?= e($producto['nombre']) ?></h2>
            <p class="precio"><?= euros($producto['precio']) ?></p>
            <!-- POST solicita un cambio de estado: todavía no se almacena. -->
            <form action="agregar.php" method="post">
                <input type="hidden" name="producto_id" value="<?= (int)$id ?>">
                <button type="submit">Añadir al carrito</button>
            </form>
        </article>
    <?php endforeach; ?>
</div>
<?php pie(); ?>
