
<?php


require_once __DIR__ . '/includes/config.php';


require_once __DIR__ . '/includes/productos.php';


cabecera('Tu carrito');


?>


<section class="intro">

    
    <p class="etiqueta">
        ESTADO EN EL SERVIDOR
    </p>

    
    <h1>Tu carrito</h1>

    
    <p>
        Recarga esta página o vuelve al catálogo:
        las cantidades se mantienen durante la sesión.
    </p>

</section>


<?php


?>

<?php if (empty($_SESSION['carrito'])): ?>


    <div class="panel">

        
        <p>
            Tu carrito está vacío.
        </p>

        
        <a class="boton" href="index.php">
            Ir al catálogo
        </a>

    </div>


<?php else: ?>


    <div class="panel">

        
        <table>

            
            <thead>

                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>

            </thead>

            
            <tbody>


            <?php


            ?>

            <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>


                <?php


                ?>

                <?php if (!isset($productos[$id])) continue; ?>


                <?php


                ?>

                <?php $producto = $productos[$id]; ?>


                <tr>

                    
                    <td>
                        <?= e($producto['nombre']) ?>
                    </td>


                    <td>
                        <?= euros($producto['precio']) ?>
                    </td>


                    <td>

                        
                        <form
                            action="actualizar.php"
                            method="post"
                            class="en-linea"
                        >

                            
                            <?= campoCsrf() ?>


                            <input
                                type="hidden"
                                name="producto_id"
                                value="<?= (int)$id ?>"
                            >


                            <input
                                type="number"
                                name="cantidad"
                                value="<?= (int)$cantidad ?>"
                                min="0"
                                max="99"
                                aria-label="Cantidad de <?= e($producto['nombre']) ?>"
                            >


                            <button type="submit">
                                Actualizar
                            </button>

                        </form>

                    </td>


                    <td>
                        <?= euros(
                            $producto['precio'] * (int)$cantidad
                        ) ?>
                    </td>


                    <td>

                        <form
                            action="eliminar.php"
                            method="post"
                        >

                            
                            <?= campoCsrf() ?>


                            <input
                                type="hidden"
                                name="producto_id"
                                value="<?= (int)$id ?>"
                            >


                            <button
                                type="submit"
                                class="peligro"
                            >
                                Eliminar
                            </button>

                        </form>

                    </td>

                </tr>


            <?php


            ?>

            <?php endforeach; ?>


            </tbody>

        
        </table>


        <p class="total">
            Total:
            <?= euros(totalCarrito($productos)) ?>
        </p>


        <form
            action="vaciar.php"
            method="post"
        >

            
            <?= campoCsrf() ?>


            <button
                type="submit"
                class="secundario"
            >
                Vaciar carrito
            </button>

        </form>

    </div>


<?php


?>

<?php endif; ?>


<?php


pie();
