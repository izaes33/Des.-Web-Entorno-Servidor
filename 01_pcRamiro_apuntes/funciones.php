<?php

// ======================================================
// FUNCIÓN PARA FORMATEAR UN PRECIO
// ======================================================
// La función recibe un precio expresado en CÉNTIMOS.
// Ejemplo:
// 2999 céntimos → 29,99 €
// int $centimos
//     Indica que el parámetro debe ser un número entero.
// : string
//     Indica que la función devolverá un string.
function formatearPrecio(int $centimos): string
{
    // Convertimos los céntimos a euros.
    // Ejemplo:
    // 2999 / 100 = 29.99

    $euros = $centimos / 100;

    // number_format() es una función nativa de php que permite dar formato a un número.
    // En este caso:
    // number_format(
    //     $euros,  // número que queremos formatear
    //     2,       // número de decimales
    //     ",",     // separador decimal
    //     "."      // separador de miles
    // )
    // Ejemplo:
    // 123456 céntimos
    //       ↓
    // 1234.56 euros
    //       ↓
    // "1.234,56"
    // Finalmente concatenamos " €".

    return number_format(
        $euros,
        2,
        ",",
        "."
    ) . " €";
}

/*
|--------------------------------------------------------------------------
| FUNCIÓN: obtenerEstadoStock()
|--------------------------------------------------------------------------
| OBJETIVO:
| Esta función recibe el número de unidades disponibles de un producto
| y devuelve un texto que describe su estado.
| Ejemplos:
|     obtenerEstadoStock(0)   → "Agotado"
|     obtenerEstadoStock(3)   → "Últimas unidades"
|     obtenerEstadoStock(5)   → "Últimas unidades"
|     obtenerEstadoStock(10)  → "Disponible"
*/

function obtenerEstadoStock(int $stock): string
{
    /*
     * int $stock
     * ----------
     * La función recibe un parámetro llamado $stock.
     * Hemos indicado "int", por lo que esperamos trabajar con
     * un número entero.
     * Por ejemplo:
     *     obtenerEstadoStock(7);
     * Dentro de la función:
     *     $stock = 7
     * : string
     * --------
     * Después de los paréntesis aparece ": string".
     * Esto indica que la función debe DEVOLVER una cadena de texto.
     * Por ejemplo:
     *     "Agotado"
     *     "Últimas unidades"
     *     "Disponible"
     */

    /*
     * PRIMER CASO:
     * Comprobamos si el stock es exactamente 0.
     * === es el operador de comparación estricta.
     * Compara tanto el VALOR como el TIPO.
     * En este caso estamos preguntando:
     *     ¿$stock es exactamente el entero 0?
     */

    if ($stock === 0) {

        /*
         * Si la condición anterior es verdadera,
         * devolvemos el texto "Agotado".
         * IMPORTANTE:
         * return no solamente devuelve un valor.
         * También TERMINA inmediatamente la ejecución de la función.
         * Por tanto, si $stock vale 0:
         *     return "Agotado";
         * y PHP ya no continúa ejecutando las siguientes
         * instrucciones de esta función.
         */

        return "Agotado";
    }

    /*
     * Si hemos llegado hasta aquí significa que el stock NO era 0.
     * Ahora comprobamos si quedan 5 unidades o menos.
     * <= significa "menor o igual que".
     * Algunos ejemplos:
     *     1 <= 5  → true
     *     3 <= 5  → true
     *     5 <= 5  → true
     *     6 <= 5  → false
     */

    if ($stock <= 5) {

        /*
         * Si quedan entre 1 y 5 unidades,
         * consideramos que quedan pocas unidades.
         */

        return "Últimas unidades";
    }

    /*
     * Si hemos llegado a esta línea significa que:
     *     - $stock NO es 0.
     *     - $stock NO es menor o igual que 5.
     * Por tanto, necesariamente tenemos más de 5 unidades.
     * No necesitamos escribir:
     *     if ($stock > 5)
     * porque los casos anteriores ya han sido descartados.
     */

    return "Disponible";
}

/*
|--------------------------------------------------------------------------
| FUNCIÓN: obtenerClaseEstado()
|--------------------------------------------------------------------------
| OBJETIVO:
| Esta función utiliza exactamente la misma lógica de stock que la
| función anterior, pero tiene una finalidad diferente.
| obtenerEstadoStock() devuelve el TEXTO que verá el usuario:
|     "Agotado"
|     "Últimas unidades"
|     "Disponible"
| obtenerClaseEstado() devuelve el NOMBRE DE UNA CLASE CSS:
|     "agotado"
|     "aviso"
|     "disponible"
| Esto nos permitirá generar HTML dinámicamente.
| Por ejemplo:
|     <span class="estado aviso">
|         Últimas unidades
|     </span>
*/

function obtenerClaseEstado(int $stock): string
{
    /*
     * Recibimos nuevamente el stock como un entero.
     * La función devuelve un string porque los nombres
     * de las clases CSS son cadenas de texto.
     */

    /*
     * Si no queda ninguna unidad...
     */

    if ($stock === 0) {

        /*
         * Devolvemos el nombre de la clase CSS:
         *     agotado
         * Esta clase existe en estilos.css:
         *     .agotado {
         *         color: var(--color-error);
         *     }
         */

        return "agotado";
    }

    /*
     * Si quedan 5 unidades o menos...
     * Como el caso 0 ya terminó anteriormente con return,
     * aquí realmente estaremos trabajando con valores
     * comprendidos entre 1 y 5.
     */

    if ($stock <= 5) {

        /*
         * Devolvemos la clase CSS "aviso".
         */

        return "aviso";
    }

    /*
     * Si no se cumplió ninguna condición anterior,
     * quedan más de 5 unidades.
     * Devolvemos la clase CSS "disponible".
     */

    return "disponible";
}

/*
|--------------------------------------------------------------------------
| FUNCIÓN: escapar()
|--------------------------------------------------------------------------
| OBJETIVO:
| Preparar una cadena de texto para mostrarla de forma segura
| dentro del HTML.
| Es especialmente importante cuando los datos pueden proceder
| de formularios, parámetros GET, bases de datos, etc.
| La idea fundamental es evitar que determinados caracteres sean
| interpretados por el navegador como código HTML.--------------
| -----------------------------------------------
*/

function escapar(string $texto): string
{
    /*
     * La función recibe:
     *     string $texto
     * Es decir, una cadena de texto.
     * Y devuelve:
     *     : string
     * otra cadena de texto.
     */

    /*
     * htmlspecialchars() es una función incorporada en PHP.
     *-----------------------------------------------------
     * Convierte caracteres que tienen un significado especial
     * en HTML en entidades HTML.-----------------------------
     *--------------------------
     * Por ejemplo, si tenemos:
     *     $texto = "<h1>Hola</h1>";
     * sin escapar, el navegador interpretaría <h1> como
     * una etiqueta HTML.
     * Al utilizar htmlspecialchars(), los símbolos < y >
     * se convierten en una representación segura.-------
     * ------------------------------------------
     * De esta manera el navegador mostrará:
     *     <h1>Hola</h1>
     * como TEXTO, en lugar de interpretarlo como una etiqueta.
     */

    return htmlspecialchars(

        /*
         * Primer argumento:
         * El texto que queremos escapar.
         */

        $texto,

        /*
         * Segundo argumento:
         * -----------------
         * Son opciones que modifican el comportamiento
         * de htmlspecialchars().----------------------
         * -------------------
         * ENT_QUOTES:
         * ----------
         * Indica que también queremos convertir tanto las
         * comillas dobles como las comillas simples.-----
         * -----------------------------------------
         * ENT_SUBSTITUTE:
         * --------------
         * Si PHP encuentra una secuencia de caracteres inválida
         * para la codificación utilizada, la sustituye por un
         * carácter de reemplazo.-----------------------------
         * ---------------------
         * El símbolo | permite combinar ambas opciones.
         * IMPORTANTE:
         * ----------
         * Aquí | NO es lo mismo que ||.
         * ----------------------------
         * || es el OR lógico que utilizamos normalmente
         * en condiciones.------------------------------
         * --------------
         * | permite aquí combinar flags/opciones.
         * --------------------------------------
         */

        ENT_QUOTES | ENT_SUBSTITUTE,

        /*
         * Tercer argumento:
         * ----------------
         * Indicamos la codificación de caracteres utilizada.
         * -------------------------------------------------
         * UTF-8 permite trabajar correctamente con caracteres
         * como:
         *     á é í ó ú
         *     ñ
         *     €
         * Además coincide con lo que normalmente tenemos
         * en nuestro HTML:
         *     <meta charset="UTF-8">
         */

        "UTF-8"
    );
}

/*
|--------------------------------------------------------------------------
| FUNCIÓN: buscarProductoPorId()
|--------------------------------------------------------------------------
| OBJETIVO:
| Buscar un producto concreto dentro del array $productos
| utilizando su identificador.
| Ejemplo:
| Tenemos:
|     $productos = [
|         [
|             "id" => 1,
|             "nombre" => "Teclado"
|         ],
|         [
|             "id" => 2,
|             "nombre" => "Ratón"
|         ]
|     ];
| Y hacemos:
|     $producto = buscarProductoPorId($productos, 2);
| La función recorrerá los productos hasta encontrar:
|     "id" => 2
| y devolverá el array correspondiente al Ratón.
*/

function buscarProductoPorId(array $productos, int $id): ?array
{
    /*
     * Esta función recibe DOS parámetros:
     * 1. array $productos
     *    El array que contiene todos nuestros productos.
     * 2. int $id
     *    El identificador del producto que queremos encontrar.
     * El tipo de retorno es:
     * ---------------------
     *     ?array
     *     ------
     * El signo ? delante de array significa:
     * -------------------------------------
     *     array O null
     *     ------------
     * Es decir, la función puede devolver:
     * -----------------------------------
     *     - un array si encuentra el producto;
     *       ---------------------------------
     *     - null si no encuentra ningún producto con ese ID.
     *       -----------------------------------------------
     * */

    /*
     * foreach permite recorrer todos los elementos
     * contenidos en $productos.
     * En cada vuelta:
     *     $producto
     * contiene UNO de los productos.
     * Si tenemos cinco productos, el foreach podrá
     * realizar hasta cinco vueltas.
     */

    foreach ($productos as $producto) {

        /*
         * En cada vuelta comprobamos el ID del producto actual.
         * Por ejemplo:
         *     $producto["id"]
         * podría valer:
         *     1
         *     2
         *     3
         *     ...
         * Lo comparamos con el ID que estamos buscando.
         * Utilizamos === para realizar una comparación estricta.
         */

        if ($producto["id"] === $id) {

            /*
             * ¡Producto encontrado!
             * Devolvemos el array completo del producto.
             * Es decir, devolvemos el producto dentro de su array asociativo.
             * IMPORTANTE:
             * return termina inmediatamente la función.
             * Por tanto, una vez encontrado el producto,
             * no necesitamos seguir recorriendo el array.
             */

            return $producto;
        }
    }

    /*
     * Esta línea solamente se ejecutará si el foreach
     * ha terminado completamente SIN encontrar ningún
     * producto cuyo ID coincida con $id.
     * En ese caso devolvemos:
     *     null
     * null representa aquí:
     *     "No se ha encontrado ningún producto".
     */

    return null;
}

function normalizarTexto(string $texto): string
{
        $texto = trim($texto); /* trim quita espacios al principio 
        y al final */

        /*
         * strtolower: Convierte todos los caracteres a minúsculas.
         * Funciona con todas las versiones de PHP, pero no es compatible con UTF-8.
         * Por ejemplo, si tenemos un texto con acentos, como "áéíóú", strtolower no 
         * los convertirá correctamente a minúsculas.
         * Para solucionar esto, podemos utilizar también mb_strtolower, 
         * que es compatible con UTF-8, de modo que si no funciona una, funciona otra.
         * Y para ello utilizamos la función function_exists() para comprobar si
         * --------------------------------------------------------------------- 
         * mb_strtolower está disponible en la versión de PHP que estamos utilizando.
         * -------------------------------------------------------------------------
         */

        /*
         * mb_strtolower: Sólo funciona a partir de PHP 5.5.
         * Funciona tanto con textos en UTF-8 como en 
         * multi-byte (incorporación de caracteres de otros idiomas 
         * a UTF-8). 
         */

        // UTF-8 (strin) es una codificación de caracteres que permite representar una 
        // gama de caracteres de diferentes idiomas y símbolos. Es especialmente útil 
        // para trabajar con textos que contienen caracteres especiales, acentos, 
        // símbolos y caracteres de diferentes alfabetos.

        if (function_exists("mb_strtolower")){
            return mb_strtolower($texto, "UTF-8");
        }

        return strtolower($texto);   

        /* 
         * Todo el código que aparece desde el if podría reemplazarse por:
         * return function_exists("mb_strtolower") ? mb_strtolower($texto, "UTF-8") : strtolower($texto); 
         *   (OPERADOR TERNARIO) 
         */

}

function buscarProductos ( 
    array $productos,  
    string $busqueda
    ): array {

    $resultados = [];

    $busqueda = normalizarTexto($busqueda);

    if ($busqueda === ""){
        return $resultados;
    }

    foreach ($productos as $producto){
        $nombre = normalizarTexto($producto["nombre"]);

        /* 
         * No podemos realizar una comparación estricta con === porque la búsqueda puede ser parcial.
         * Por ejemplo, si el usuario busca "teclado", queremos que se encuentre el producto 
         * "Teclado mecánico" aunque no sea una coincidencia exacta.
         * Por eso utilizamos str_contains, que busca una cadena dentro de otra y devuelve
         * -------------------------------------------------------------------------------
         * true si la cadena encuentra coincidencias aunque sean parciales y false en caso contrario.
         * -----------------------------------------------------------------------------------------
         */

        if (str_contains($nombre, $busqueda)){

            // En PHP, esto significa que estamos agregando el producto actual al final del array $resultados.
            // Si el producto que estamos recorriendo en el bucle coincide con la búsqueda, lo añadimos al array de resultados.
            $resultados[] = $producto;
        }

    }

    return $resultados;
}

// Lo que queremos hacer es que el campo que viaja, en este caso GET["q"], se lea y se devuelva 
// como un string para pasárselo a la función buscarProductos() y que nos devuelva un array con 
// los productos que coincidan con la búsqueda.
// En el caso de que GET["q"] no esté definido, la función devolverá un string vacío, NO UN NULL, para que la función 
// buscarProductos() no devuelva un error. (En consultas SQL, existe una función llamada COALESCE() que hace exactamente esto, 
// devuelve el primer valor que no sea NULL, y si todos los valores son NULL, devuelve un valor por defecto. En este caso, 
// el valor por defecto sería un string vacío).
// ( El objetivo de esta función va a ser capturar el valor de "q" y devolver un string para 
// pasárselo a la función buscarProductos() y que nos devuelva un array con los productos que coincidan con la búsqueda).
function leerCadena(

        // Recibe un array asociativo y una clave específica.
        // Va a suponer el evento o eventos que desencadenan la función, ( Un GET y posiblemente otros eventos)
        // por ejemplo un formulario que se envía.
        array $origen,

        // La clave que queremos leer dentro del array asociativo. En este caso, se va a corresponder 
        // con el imput con id "p"
        string $clave

// El tipo de retorno de la función es un string.
): string   {

    // El operador de fusión de null (??) se utiliza para comprobar si una variable está definida y no es null.
    // -------------------------------------------------------------------------------------------------------
    // Si la variable está definida y no es null, devuelve su valor. Si no, devuelve un valor por defecto.
    // En este caso, si la clave existe en el array asociativo $origen, devuelve su valor. Si no, devuelve un string vacío.
    $valor = $origen[$clave] ?? "";

    if (!is_string($valor)) {
        // Si el valor no es un string, lo convertimos a string.
        return "";
    }

    return $valor;
    /* Comprobamos si la clave existe en el array asociativo.
    if (isset($origen[$clave])) {
        // Si la clave existe, devolvemos su valor como un string.
        return (string) $origen[$clave];
    }
    // Si la clave no existe, devolvemos un string vacío.
    return ""; */
}

/*
 * ============================================================================
 * EL PARÁMETRO $origen Y EL ATRIBUTO method
 * ============================================================================
 * - Se define la función: leerCadena(array $origen, string $clave): string
 *   Ese $origen representa el array superglobal ($_GET o $_POST) de donde PHP
 *   recogerá los datos enviados tras pulsar el botón type="submit" del
 *   formulario HTML (<form action="buscar.php" method="GET">).
 * - Aunque existen otros métodos HTTP, en los formularios web los dos
 *   fundamentales son GET y POST.
 * FUNCIONAMIENTO DEL MÉTODO GET:
 * - Cómo viajan los datos: Los datos se envían de forma visible añadiéndose
 *   al final de la URL del navegador tras un signo de interrogación (?) en
 *   formato clave=valor. Por ejemplo, si el <input> tiene el atributo name="q"
 *   y el usuario escribe "te", la URL pasará a ser: buscar.php?q=te
 * - Cuándo se usa: Se emplea para consultas o búsquedas donde solo se solicita
 *   información al servidor sin modificar ni guardar nada en la base de datos.
 * FUNCIONAMIENTO DEL MÉTODO POST:
 * - Cómo viajan los datos: La información no se muestra en la URL, sino que
 *   viaja oculta dentro del cuerpo (body) de la petición HTTP. En PHP se
 *   recibe en la variable $_POST, que funciona como un array asociativo de
 *   pares clave-valor (por ejemplo: $_POST = ["nombre" => "Ana", "email" => "..."]).
 * - Cuándo se usa: Se utiliza cuando el envío va a producir cambios en el
 *   servidor (como registrar un pedido o insertar registros en una base de
 *   datos) o cuando se manejan datos sensibles o privados (como formularios
 *   con datos personales o pasarelas de pago, las cuales además van protegidas
 *   mediante certificados y encriptación).
 * ============================================================================
 */

function calcularCompra(
        int $precio,
        int $unidades
) : array {

        $subtotal = $precio *  $unidades;

        $descuento = 0;

        if ($unidades >= UNIDADES_DESCUENTO){

            $descuento = (int) round($subtotal * DESCUENTO_CANTIDAD); 
        }

        $base = $subtotal - $descuento;

        $iva = (int) round ($base * IVA_GENERAL);

        $total = $base + $iva;

        return [
            "subtotal" => $subtotal,
            "descuento" => $descuento,
            "base" => $base,
            "iva" => $iva,
            "total" => $total
        ];

}