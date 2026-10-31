<?php
// ======================================================
// 1. CARGAMOS LOS ARCHIVOS PHP NECESARIOS
// ======================================================
// require_once carga y ejecuta el archivo indicado.
// "once" significa que, aunque intentemos incluir el mismo
// archivo varias veces, PHP solamente lo cargará una vez.
// funciones.php contendrá las funciones de nuestra aplicación.
// Por ejemplo:
// function formatearPrecio($precio) {
//     ...
// }
require_once "funciones.php";

// datos.php contendrá los datos de nuestra aplicación.
// Por ejemplo, podría contener un array:
// $productos = [
//     ...
// ];
require_once "datos.php";

/*
 * ============================================================
 * ORDENACIÓN DE LOS PRODUCTOS
 * ============================================================
 * Queremos permitir que el usuario pueda decidir cómo se
 * muestran los productos:
 *      - por ID
 *      - por nombre
 *      - por precio
 * El criterio de ordenación se recibirá mediante GET.
 * Por ejemplo:
 *      index.php?orden=nombre
 *      index.php?orden=precio
 *      index.php?orden=id
 * Si visitamos:
 *      index.php?orden=precio
 * PHP tendrá disponible:
 *      $_GET["orden"] = "precio";
 * IMPORTANTE:
 * Los datos que llegan mediante GET proceden del usuario.
 * Por eso no accedemos directamente sin ningún control,
 * sino que utilizamos nuestra función leerCadena().
 */

/*
 * ------------------------------------------------------------
 * 1. LEER EL CRITERIO DE ORDENACIÓN
 * ------------------------------------------------------------
 * leerCadena() recibe:
 *      1. El array donde queremos buscar: $_GET
 *      2. La clave que queremos leer: "orden"
 * Si la URL es:
 *      index.php?orden=precio
 * entonces:
 *      $_GET["orden"] contiene "precio"
 * y después de esta instrucción:
 *      $orden contendrá "precio"
 * Si "orden" no existe, nuestra función leerCadena()
 * devuelve una cadena vacía "".
 */
$orden = leerCadena($_GET, "orden");

/*
 * ------------------------------------------------------------
 * 2. ESTABLECER UN VALOR PREDETERMINADO
 * ------------------------------------------------------------
 * Puede ocurrir que el usuario entre simplemente en:
 *      index.php
 * En ese caso no existe:
 *      ?orden=...
 * Por tanto, leerCadena() habrá devuelto:
 *      ""
 * Comprobamos si $orden contiene una cadena vacía.
 * Utilizamos === porque queremos una comparación estricta:
 *      valor + tipo
 */
// Cada vez que carguemos la página, se intentará leer el parámetro "orden" de la URL.
// Pero como leerCadena() devuelve una cadena vacía si el parámetro no existe, 
// podemos usarlo para comprobar si el parámetro "orden" existe o no.
if ($orden === "") {
    /*
     * Si el usuario no ha indicado ningún criterio,
     * utilizaremos el ID como orden predeterminado.
     */
    $orden = "id";
}

/*
 * ------------------------------------------------------------
 * 3. CREAR UNA COPIA DEL ARRAY DE PRODUCTOS
 * ------------------------------------------------------------
 * $productos contiene los datos originales de la aplicación.
 * En lugar de ordenar directamente:
 *      $productos
 * creamos otra variable:
 *      $productosOrdenados
 * que inicialmente contiene los mismos productos.
 * Así podemos trabajar con una colección destinada
 * específicamente a la ordenación sin modificar el array original.
 */
$productosOrdenados = $productos;

/*
 * ============================================================
 * 4. DECIDIR CÓMO ORDENAR
 * ============================================================
 * Comprobamos el contenido de $orden.
 * Tenemos tres posibilidades:
 *      "nombre"
 *      "precio"
 *      "id"
 * Dependiendo del valor recibido utilizaremos un criterio
 * diferente para ordenar el array.
 */

/*
 * ------------------------------------------------------------
 * CASO 1: ORDENAR POR NOMBRE
 * ------------------------------------------------------------
 * Entraremos aquí si la URL es:
 *      index.php?orden=nombre
 */
if ($orden === "nombre") {
    // Ordenamos el array de productos por nombre.
    // La función usort() ordena un array usando una función de comparación. 
    // Esta función es de las llamadas "callback", es decir, que pasa como 
    // argumento a otra función que sólo existe en memoria dentro de la función 
    // que la llama.
    /*
         * El segundo argumento de usort() es una función.
         * En este caso estamos utilizando una FUNCIÓN ANÓNIMA.
         * Se llama "anónima" porque no tiene nombre.
         * No hacemos:
         *      function compararProductos(...)
         * sino directamente:
         *      function (...)
         * porque esta función solamente la necesitamos aquí.
         * usort() irá tomando DOS productos del array
         * y se los proporcionará a nuestra función.
         * Los llamamos:
         *      $a
         *      $b
         * Cada uno de ellos es un producto completo.
         * Por ejemplo, $a podría ser:
         *      [
         *          "id" => 1,
         *          "nombre" => "Teclado",
         *          "precio" => 7990
         *      ]
         * y $b podría ser:
         *      [
         *          "id" => 2,
         *          "nombre" => "Ratón",
         *          "precio" => 3990
         *      ]
         * Indicamos "array $a" y "array $b" porque esperamos
         * recibir dos arrays.
         * El ": int" indica que esta función debe devolver
         * un número entero.
                 * Para ordenar por nombre comparamos:
                 *      $a["nombre"]
                 * con:
                 *      $b["nombre"]
                 * Utilizamos el operador:
                 *      <=>
                 * conocido como "operador nave espacial"
                 * (spaceship operator).
                 * Este operador devuelve un valor:
                 *      negativo  → $a debe ir antes que $b
                 *      0         → ambos valores son iguales
                 *      positivo  → $a debe ir después que $b
                 * Ese resultado es precisamente lo que necesita
                 * usort() para decidir el orden.
         */
    //En resumen, usort() ordena el array $productosOrdenados según el nombre de los productos,
    // utilizando la función de comparación definida en la función anónima. La cual
    // enfrenta dos productos a la vez y devuelve un valor que indica su orden relativo.
    usort($productosOrdenados, function (array $a, array $b): int {
        return $a["nombre"] <=> $b["nombre"];
    });

//Si usamos "elseif" en lugar de "if", nos aseguramos de que sólo se ejecute uno de los bloques 
// de código, dependiendo del valor de $orden. Esto es importante porque no 
// queremos ordenar el array varias veces, sino sólo una vez según el criterio 
// especificado. Además, si por lo que sea entrase un parámetro "orden" que no 
// fuera ninguno de los esperados, no se ejecutaría ningún bloque de código y el 
// array permanecería sin ordenar.
} elseif ($orden === "precio") {
    // Ordenamos el array de productos por precio.
    usort($productosOrdenados, function (array $a, array $b): int {
        return $a["precio"] <=> $b["precio"];
    });

/*En el bloque de ordenación con usort, la cadena if–elseif hace que se 
aplique un sólo criterio según el valor de $orden: nombre, precio o id.
Si ahora se añade un else con otra llamada a usort, esa rama se ejecutará 
cuando $orden no sea ninguno de esos valores. Por ejemplo, usar ahí la 
ordenación por id hará que los valores desconocidos también ordenen los 
productos por id. Ahora mismo, esos valores dejan el array en su orden original.
Como $orden ya toma "id" cuando el parámetro está vacío, el else serviría 
principalmente como alternativa para valores no reconocidos, como ?orden=otro */
} else {
    $orden = "id";
    // Ordenamos el array de productos por id.
    usort($productosOrdenados, function (array $a, array $b) {
        return $a["id"] <=> $b["id"];
    });
}

/*
 * ============================================================
 * RESULTADO
 * ============================================================
 * Al terminar este bloque tenemos dos variables importantes:
 *      $orden
 *          Contiene el criterio finalmente utilizado:
 *              "id"
 *              "nombre"
 *              "precio"
 *      $productosOrdenados
 *          Contiene los productos ya ordenados según
 *          el criterio seleccionado.
 * Por tanto, cuando posteriormente generemos el HTML,
 * debemos recorrer:
 *      $productosOrdenados
 * y NO $productos.
 * Por ejemplo:
 *      foreach ($productosOrdenados as $producto) {
 *          ...
 *      }
 */

/*
 * ============================================================
 * RESUMEN DEL FLUJO
 * ============================================================
 * Usuario pulsa:
 *      "Por precio"
 *              ↓
 * Navegador solicita:
 *      index.php?orden=precio
 *              ↓
 * PHP recibe:
 *      $_GET["orden"] = "precio"
 *              ↓
 * leerCadena()
 *              ↓
 *      $orden = "precio"
 *              ↓
 * if / elseif / else
 *              ↓
 * entra en:
 *      elseif ($orden === "precio")
 *              ↓
 * usort()
 *              ↓
 * compara:
 *      $a["precio"] <=> $b["precio"]
 *              ↓
 *      $productosOrdenados
 * queda ordenado de menor a mayor precio.
 */
?>

<!DOCTYPE html>
<!--
    A partir de aquí tenemos principalmente HTML.
    PHP se ejecuta EN EL SERVIDOR.
    El navegador NO recibe este código PHP.
    El navegador recibirá únicamente el HTML generado.
-->
<html lang="es">

<head>

    <!-- Codificación de caracteres -->
    <meta charset="UTF-8">

    <!-- Adaptación a dispositivos móviles -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título mostrado en la pestaña del navegador -->
    <title>DWES Store</title>

    <!-- Hoja de estilos CSS externa -->
    <link
        rel="stylesheet"
        href="estilos.css"
    >

</head>

<body>

<!-- ==================================================
     CABECERA DE LA PÁGINA
     ================================================== -->

<header class="cabecera">

    <div class="contenedor">

        <h1>DWES Store</h1>

        <p>Versión estática en HTML y CSS</p>

        <!--
            Menú de navegación.
            Cada enlace realizará una nueva petición
            HTTP al servidor.
        -->
        <nav class="navegacion">

            <a href="index.php">
                Inicio
            </a>

            <a href="buscar.php">
                Buscar
            </a>

            <a href="compra.php">
                Comprar
            </a>

        </nav>

    </div>

</header>

<!-- ==================================================
     CONTENIDO PRINCIPAL
     ================================================== -->

<main class="contenedor">

    <!-- Título de la sección -->
    <section class="panel">

        <h2>Catálogo</h2>
        <p>Orden actual: <?php echo $orden; ?></p>
        <nav class="navegacion">
            <!--
                Cada enlace realiza una nueva petición HTTP al servidor.
                El parámetro "orden" indica el criterio de ordenación. 
                GET y POST son métodos de envío de datos al servidor.
                El GET lo inicia el navegador cuando haces clic en uno de esos enlaces. 
                Por ejemplo, al pulsar «Por Nombre», el navegador solicita 
                index.php?orden=nombre al servidor mediante HTTP GET.
                En el servidor, PHP recibe el parámetro de la URL en $_GET. 
                En este archivo, y se lee aquí:
                $orden = leerCadena($_GET, "orden"); en index.php
                Así, si se pulsa «Por Nombre», $_GET["orden"] vale "nombre" y 
                el programa lo usa para ordenar los productos. Los enlaces que 
                generan esas solicitudes están en index.php. -->
            <a href="index.php?orden=id">Por ID</a>
            <a href="index.php?orden=nombre">Por Nombre</a>
            <a href="index.php?orden=precio">Por Precio</a>
            <!--Ocurre por el comportamiento normal de un enlace HTML (<a>). 
                Cuando haces clic, el navegador sigue la dirección escrita en 
                href y pide esa página al servidor. En este caso, la dirección 
                incluye ?orden=nombre, por ejemplo:
            Como un enlace navega a una URL, el navegador realiza esa petición 
                usando GET. La parte después de ? es la cadena de consulta: orden=nombre. 
                PHP la recibe y la pone en $_GET["orden"].
            El flujo es:
                Haces clic en «Por Nombre».
                El navegador solicita index.php?orden=nombre mediante GET.
                El servidor ejecuta de nuevo index.php.
                El código lee $_GET["orden"] y ordena los productos por nombre.
                El enlace «Por ID» es el predeterminado.
                Si no se indica ningún parámetro, PHP lo interpreta como:
                index.php?orden=idy ordena los productos por ID. -->
        </nav>
    </section>

    <!-- ==================================================
         CATÁLOGO DE PRODUCTOS
         ================================================== -->

    <section class="grid-productos">

        <?php
        // Recorremos el array $productos.
        // En cada iteración, $producto contendrá
        // uno de los productos del array.
        // Si tenemos:
        // $productos = [
        //     ["nombre" => "Teclado", ...],
        //     ["nombre" => "Ratón", ...],
        //     ["nombre" => "Monitor", ...]
        // ];
        // foreach realizará 3 iteraciones.
        // Primera:
        // $producto → Teclado
        // Segunda:
        // $producto → Ratón
        // Tercera:
        // $producto → Monitor

        foreach ($productosOrdenados as $producto) {
        ?>

            <!--
                Este <article> se generará una vez
                por cada producto existente en el array.
            -->
            <article class="producto">

                <!-- ==============================
                     NOMBRE DEL PRODUCTO
                     ============================== -->

                <h2>

                    <?=
                        // <?= es una forma abreviada de:
                        // <?php echo ...;
                        // Mostramos el nombre del producto.
                        $producto["nombre"]
                    ?>

                </h2>

                <!-- ==============================
                     CATEGORÍA
                     ============================== -->

                <p>

                    Categoria:

                    <?=
                        // Accedemos al valor asociado
                        // a la clave "categoria".
                        $producto["categoria"]
                    ?>

                </p>

                <!-- ==============================
                     PRECIO
                     ============================== -->

                <p class="precio">

                    <?=
                        // Llamamos a nuestra función
                        // formatearPrecio().
                        // Le pasamos como argumento
                        // el precio del producto.
                        // Por ejemplo:
                        // 29.9
                        // podría convertirse en:
                        // 29,90 €
                        formatearPrecio(
                            $producto["precio"]
                        )
                    ?>

                </p>

                <!-- ==============================
                     STOCK
                     Cualquier tienda online o plugin como prestashop
                     o wooComerce va a hacer que no se pueda realizar una venta si no 
                     hay stock
                     ============================== -->

                <p>

                    Stock:

                    <?=
                        // Mostramos el stock disponible.
                        $producto["stock"]
                    ?>

                </p>

                <!--
                    "estado" es la clase fija que da formato de etiqueta al párrafo.
                    obtenerClaseEstado() añade otra clase según el stock: "agotado"
                    si es 0, "aviso" si es menor o igual que 5, o "disponible"
                    si supera 5. En estilos.css, .estado define la forma, el fondo
                    y el espaciado de la etiqueta; la otra clase determina el color
                    del texto.
                    El texto del estado lo genera obtenerEstadoStock(): "Agotado",
                    "Últimas unidades" o "Disponible". El número de unidades se
                    muestra por separado en el párrafo anterior.
                -->
                <p class="estado <?= obtenerClaseEstado($producto["stock"]); ?>">

                    Estado:

                    <?=
                        // Mostramos el texto descriptivo que corresponde al stock.
                        obtenerEstadoStock($producto["stock"]);
                    ?>

                </p>

                <!-- ==============================
                     ENLACE A LA PÁGINA DE PRODUCTO
                     ============================== -->
                <!-- Vamos a hacer que cuando se pulse el enlace, la página haga un GET 
                 a producto.php y le pase como parámetro el id del producto. -->
                <a class="boton" href="producto.php?id= <?= $producto["id"] ?> " >Ver Producto</a>

            </article>

        <?php
        // Cerramos el bloque correspondiente
        // al foreach.
        }
        ?>

    </section>

</main>

<!-- ==================================================
     PIE DE PÁGINA
     ================================================== -->

<footer class="pie">

    <div class="contenedor">

        Proyecto de Desarrollo Web en Entorno Servidor

    </div>

</footer>

</body>

</html>