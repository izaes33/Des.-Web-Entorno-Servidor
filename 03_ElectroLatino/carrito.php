<?php

/**============================================================
 * ARCHIVO: carrito.php
 * PROYECTO: MiniTienda PHP
 * ============================================================
 * OBJETIVO:
 * Mostrar al usuario los productos que tiene almacenados en su carrito de compra.
 * Los datos del carrito se recuperan de la sesión mediante la variable superglobal $_SESSION.
 * 
 * En este archivo vamos a trabajar:
 *      1. Inclusión de archivos PHP.
 *      2. Recuperación de datos de sesión.
 *      3. Generación dinámica de HTML.
 *      4. Condicionales if / else.
 *      5. Recorrido de arrays con foreach.
 *      6. Formularios HTML con método POST.
 *      7. Campos ocultos.
 *      8. Protección mediante tokens CSRF.
 *      9. Cálculo de subtotales y total.
 *      10. Reutilización de funciones.
 * 
 * FUNCIONAMIENTO:
 * Cuando el usuario entra en carrito.php:
 *  - PHP recupera su sesión.
 *  - Consulta los productos del carrito.
 *  - Comprueba si el carrito está vacío.
 *  - Si está vacío, muestra un mensaje.
 *  - Si contiene productos, genera una tabla.
 *  - Permite actualizar cantidades.
 *  - Permite eliminar productos.
 *  - Permite vaciar el carrito completo.
 * 
 * IMPORTANTE:
 * Este archivo muestra información.
 * Las modificaciones se realizan en otros archivos PHP mediante formularios POST. */


/**============================================================
 * 1. CARGAR LA CONFIGURACIÓN
 * ============================================================
 * __DIR__ contiene la ruta del directorio donde se encuentra este archivo.
 * El operador punto (.) concatena cadenas. Por tanto, construimos la ruta absoluta
 * hacia includes/config.php.
 * Este archivo debe encargarse de iniciar o recuperar la sesión PHP.
 * También debe cargar las funciones comunes e inicializar el carrito cuando sea necesario.
 * Utilizamos require_once para evitar incluir varias veces el mismo archivo. */
require_once __DIR__ . '/includes/config.php';


/**============================================================
 * 2. CARGAR EL CATÁLOGO
 * ============================================================
 * Incorporamos el archivo productos.php.
 * Contiene el array $productos, donde se guarda la información de los artículos disponibles.
 * NECESITAMOS EL CATÁLOGO Porque en la sesión guardamos principalmente identificadores y cantidades.
 * Por ejemplo:
 *      $_SESSION['carrito'] = [
 *            1 => 2,
 *            2 => 1
 *      ];
 * El catálogo permite obtener el nombre y el precio correspondiente a cada ID.
 * De esta forma no necesitamos guardar los precios en el carrito. */
require_once __DIR__ . '/includes/productos.php';


/**============================================================
 * 3. MOSTRAR LA CABECERA DE LA PÁGINA
 * ============================================================ */
cabecera('Tu carrito');


/* Cerramos temporalmente PHP. La etiqueta ?> permite salir del modo PHP
 * y escribir HTML directamente. PHP enviará ese HTML al navegador. */
?>

<!--============================================================
    4. SECCIÓN INTRODUCTORIA
    ============================================================
    <section> representa una sección temática dentro del documento HTML.
    class="intro" permite aplicar estilos CSS específicos a esta sección.
    Esta parte es contenido estático: siempre se muestra independientemente
        de que el carrito esté vacío o lleno. -->
<section class="intro">

    <!--Texto de identificación de la sección. La clase etiqueta permite aplicarle
        un estilo visual desde CSS. -->
    <p class="etiqueta">ESTADO EN EL SERVIDOR</p>

    <h1>Tu carrito</h1>

    <!-- Explicación didáctica sobre las sesiones:
        Cuando el usuario recarga esta página, se realiza una nueva petición HTTP.
        PHP recupera los datos asociados a la sesión y puede volver a mostrar los productos seleccionados.
        HTTP no recuerda automáticamente las peticiones anteriores. Las sesiones permiten mantener
        información asociada a un usuario entre distintas peticiones.-->
    <p>Recarga esta página o vuelve al catálogo: las cantidades se mantienen durante la sesión.</p>

</section>


<?php
/**============================================================
 * 5. COMPROBAR SI EL CARRITO ESTÁ VACÍO
 * ============================================================
 * Volvemos a PHP para ejecutar una condición con la sintaxis alternativa: if (condicion): ... endif;
 * la cual resulta especialmente útil cuando mezclamos bloques PHP con grandes fragmentos HTML.
 * IMPORTANTE:
 * La sesión debe haberse iniciado previamente y config.php debe garantizar que $_SESSION['carrito'] sea un array.
 * 
 * BUENA PRÁCTICA AL ESTRUCTURAR UN CONDICIONAL IF / ELSE
 * En lenguajes como PHP, Java y JavaScript, para ser más eficientes y mantener
 * el código limpio, se suele seguir esta convención:
 * 
 *    - Lo más simple en el if y lo más complejo en el else:
 *          Cuando tenemos dos caminos posibles, por convención y eficiencia se suele
 *          evaluar primero el caso cuya salida sea más sencilla y rápida dentro del
 *          if, dejando la lógica más pesada o extensa para el else.
 * 
 *    - Aplicación práctica (el carrito de la compra):
 *           * Caso simple (if): Comprobar que NO hay productos en el carrito. Si está
 *                vacío, la salida es inmediata: basta con mostrar un mensaje de "El
 *                carrito está vacío", un botón que devuelva al index y terminar.
 *           * Caso complejo (else): Si sí hay productos, entonces ejecutamos la parte
 *                costosa de recorrer el carrito y renderizar todos los elementos.
 * 
 *    - Uso de empty() frente a !empty():
 *           Siguiendo esta regla, en lugar de comprobar si el carrito tiene cosas con !empty(), 
 *           se evalúa directamente si está vacío con: if (empty($_SESSION['carrito'])):
 *           La función empty() comprueba de una vez si la variable de sesión existe y si tiene valores.
 * 
 * En nuestro caso, empty($_SESSION['carrito']) devuelve true si el carrito no contiene ningún producto:
 *      $_SESSION['carrito'] = [];         -> empty(...) devuelve true.
 *      $_SESSION['carrito'] = [1 => 2];   -> empty(...) devuelve false. */
?>

<?php if (empty($_SESSION['carrito'])): ?>

    <!--========================================================
        6. MOSTRAR EL CARRITO VACÍO
        ========================================================
        Este bloque HTML solamente se genera cuando la condición del if es verdadera,
        es decir, cuando no hay productos almacenados en el carrito. -->
    <!--Contenedor visual. class="panel" permite aplicar los estilos definidos en CSS. -->
    <div class="panel">
        <p>Tu carrito está vacío.</p>
        <!--Enlace hacia el catálogo. <a> crea un hipervínculo, href="index.php" indica la página de destino
            y class="boton" le da apariencia de botón mediante CSS. Al pulsarlo, realiza una petición GET a index.php. -->
        <a class="boton" href="index.php">Ir al catálogo</a>
    </div>


<?php else: ?>

    <!--========================================================
        7. MOSTRAR EL CARRITO CON PRODUCTOS
        ========================================================
        El bloque else se ejecuta cuando la condición del if es falsa (cuando el carrito contiene
        al menos una entrada). Aquí construiremos una tabla HTML con los productos seleccionados. -->

    <div class="panel">

        <!--====================================================
            8. TABLA DEL CARRITO
            ====================================================
            <table> representa una tabla de datos organizada en filas y columnas.
            Cada fila representará un producto. -->
        <table>

            <!--<thead> contiene la cabecera de la tabla, <tr> una fila y <th> una celda de encabezado. -->
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <!--<tbody> contiene las filas con los datos de los productos generadas dinámicamente con PHP. -->
            <tbody>

                <?php
                /**==================================================
                 * 9. RECORRER EL CARRITO CON FOREACH
                 * ==================================================
                 * foreach ($array as $clave => $valor) permite recorrer los elementos de un array asociativo.
                 * En $_SESSION['carrito'], $id es la clave (identificador del producto) y $cantidad es el valor (unidades).
                 * EJEMPLO:
                 *      $_SESSION['carrito'] = [
                 *            1 => 2,
                 *            3 => 4
                 *      ];
                 *  - Primera iteración: $id = 1, $cantidad = 2
                 *  - Segunda iteración: $id = 3, $cantidad = 4
                 * En cada iteración generaremos una fila HTML usando foreach (...): y cerrando con endforeach. */
                ?>

                <?php foreach ($_SESSION['carrito'] as $id => $cantidad): ?>

                    <?php
                    /**==============================================
                     * 10. COMPROBAR QUE EL PRODUCTO EXISTE
                     * ==============================================
                     * Comprobamos que el producto del carrito sigue existiendo en el catálogo.
                     * !isset($productos[$id]) será true si el producto no existe.
                     * continue termina la iteración actual del bucle y pasa a la siguiente sin generar fila HTML.
                     * IMPORTANTE: continue no elimina el producto de la sesión, solo evita mostrarlo. */
                    ?>
                    <?php if (!isset($productos[$id])) continue; ?>


                    <?php
                    /**==============================================
                     * 11. RECUPERAR LOS DATOS DEL PRODUCTO
                     * ==============================================
                     * Recuperamos los datos originales definidos en el servidor desde el catálogo:
                     *      $producto = $productos[$id];
                     * Así podemos acceder cómodamente a $producto['nombre'] y $producto['precio']. */
                    ?>
                    <?php $producto = $productos[$id]; ?>


                    <!--============================================
                    12. GENERAR UNA FILA DEL CARRITO
                    ============================================
                    Cada iteración del foreach genera un elemento <tr> (una fila por cada producto). -->
                    <tr>

                        <!--========================================
                        13. MOSTRAR EL NOMBRE DEL PRODUCTO
                        ========================================
                        <td> representa una celda de datos y < ?= ... ?> es la forma abreviada de echo.
                        $producto['nombre'] obtiene el nombre del catálogo y e() escapa el texto con
                        htmlspecialchars() para evitar que se interpreten caracteres como etiquetas HTML. -->
                        <td><?= e($producto['nombre']) ?></td>


                        <!--========================================
                        14. MOSTRAR EL PRECIO UNITARIO
                        ========================================
                        euros() utiliza number_format() para presentar el importe numérico (ej. 1234.5)
                        formateado al usuario (1.234,50 €). Los cálculos se hacen con los números originales. -->
                        <td><?= euros($producto['precio']) ?></td>


                        <!--========================================
                        15. FORMULARIO PARA ACTUALIZAR CANTIDAD
                        ========================================
                        Cada producto tiene su propio formulario para modificar el número de unidades. -->
                        <td>

                            <!--action="actualizar.php" indica el archivo que procesará el formulario por method="post".
                            class="en-linea" organiza visualmente el formulario mediante CSS. -->
                            <form action="actualizar.php" method="post" class="en-linea">

                                <!--=================================
                                16. INCORPORAR EL TOKEN CSRF
                                =================================
                                campoCsrf() genera un input oculto con el token almacenado en la sesión:
                                <input type="hidden" name="csrf_token" value="TOKEN">
                                Al enviarse, actualizar.php comprobará que coincida para evitar ataques CSRF. -->
                                <?= campoCsrf() ?>


                                <!--=================================
                                17. IDENTIFICADOR DEL PRODUCTO
                                =================================
                                Input oculto (type="hidden") que envía el ID convertido a entero con (int)$id.
                                IMPORTANTE: Como el usuario puede modificarlo en DevTools, actualizar.php debe validarlo. -->
                                <input type="hidden" name="producto_id" value="<?= (int)$id ?>">


                                <!--=================================
                                18. CAMPO PARA MODIFICAR CANTIDAD
                                =================================
                                type="number" espera una entrada numérica con la cantidad actual en value.
                                min="0" y max="99" limitan el rango en el navegador (no sustituyen la validación en servidor)
                                y aria-label proporciona un nombre accesible identificando el artículo. -->
                                <input
                                    type="number"
                                    name="cantidad"
                                    value="<?= (int)$cantidad ?>"
                                    min="0"
                                    max="99"
                                    aria-label="Cantidad de <?= e($producto['nombre']) ?>">


                                <!--=================================
                                19. BOTÓN DE ACTUALIZACIÓN
                                =================================
                                type="submit" envía csrf_token, producto_id y cantidad hacia actualizar.php
                                para validar los datos y modificar $_SESSION['carrito']. -->
                                <button type="submit">Actualizar</button>

                            </form>

                        </td>


                        <!--========================================
                        20. CALCULAR EL SUBTOTAL
                        ========================================
                        Se calcula multiplicando precio unitario * cantidad convertida a entero:
                        $producto['precio'] * (int)$cantidad, y se formatea el resultado con euros(). -->
                        <td><?= euros($producto['precio'] * (int)$cantidad) ?></td>


                        <!--========================================
                        21. FORMULARIO PARA ELIMINAR EL PRODUCTO
                        ========================================
                        Formulario independiente para eliminar un producto concreto enviando una petición POST a eliminar.php. -->
                        <td>

                            <form action="eliminar.php" method="post">

                                <!--Token CSRF para proteger la operación de eliminación. -->
                                <?= campoCsrf() ?>

                                <!--Enviamos el ID del producto a eliminar dentro de $_SESSION['carrito']. -->
                                <input type="hidden" name="producto_id" value="<?= (int)$id ?>">

                                <!--Botón de envío. class="peligro" aplica un estilo visual para acciones destructivas. -->
                                <button type="submit" class="peligro">Eliminar</button>

                            </form>

                        </td>

                    </tr>

                    <?php
                    /**==================================================
                     * 22. FINALIZAR EL BUCLE FOREACH
                     * ================================================== */
                    ?>
                <?php endforeach; ?>

            </tbody>
        </table>


        <!--====================================================
            23. MOSTRAR EL TOTAL DEL CARRITO
            ====================================================
            (Se formatea donde se renderiza, no donde se calcula). La función euros() se aplica al 
            resultado de totalCarrito($productos) y viene de includes/funciones.php, al igual que
            totalCarrito($productos), la cual recorre $_SESSION['carrito'], multiplica precio * cantidad de cada
            artículo y devuelve la suma total (float), que finalmente formateamos con euros(). -->
        <p class="total">Total: <?= euros(totalCarrito($productos)) ?></p>


        <!--====================================================
            24. FORMULARIO PARA VACIAR EL CARRITO
            ====================================================
            Permite eliminar todas las entradas del carrito enviando una petición POST a vaciar.php. -->
        <form action="vaciar.php" method="post">

            <!--Token CSRF para proteger esta operación. -->
            <?= campoCsrf() ?>

            <!--Botón de envío con estilo class="secundario" que procesará el vaciado en vaciar.php. -->
            <button type="submit" class="secundario">Vaciar carrito</button>

        </form>

    </div>


    <?php
    /**============================================================
     * 25. FINALIZAR EL CONDICIONAL
     * ============================================================
     * endif cierra la estructura if / else (solo se genera una de las dos alternativas por petición). */
    ?>
<?php endif; ?>


<?php
/**============================================================
 * 26. MOSTRAR EL PIE DE PÁGINA
 * ============================================================
 * pie() complementa a cabecera() cerrando <main>, generando el <footer> y cerrando <body> y <html>. */
pie();
