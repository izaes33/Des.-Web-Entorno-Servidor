<!-- Recordamos que cuando un archivo contiene HTML y PHP deberá cerrarse la 
     etiqueta < ?php  -->
<?php

/*
===============================================================================
ARCHIVO: compra.php
ASIGNATURA: DESARROLLO WEB EN ENTORNO SERVIDOR (DWES)
===============================================================================
OBJETIVO DEL ARCHIVO
--------------------
Este archivo se encarga de recibir los datos de un formulario de compra,
comprobar que sean correctos y calcular el importe correspondiente.
En este ejemplo NO estamos guardando un carrito en caché, una compra en una 
base de datos ni realizando un pago real.
Únicamente estamos:
    1. Recibiendo información del usuario.
    2. Validando los datos recibidos.
    3. Comprobando que el producto exista.
    4. Comprobando que haya suficiente stock.
    5. Calculando los importes de la compra.
    6. Preparando las variables que después utilizaremos en el HTML.

===============================================================================
¿CÓMO FUNCIONA EL PROGRAMA?
===============================================================================
El usuario accede a la página de compra.
Inicialmente, el navegador realiza normalmente una petición GET.
El servidor ejecuta compra.php y muestra el formulario.
El usuario introduce:
    - Su nombre.
    - Su correo electrónico.
    - El producto que quiere comprar.
    - El número de unidades.
Cuando pulsa el botón de envío, el navegador realiza una petición POST.
Entonces PHP recibe los datos mediante el array superglobal:
    $_POST
El programa comprueba que los datos sean correctos.
Si encuentra errores, los guarda en el array $errores.
Si no encuentra errores, llama a calcularCompra() y prepara el resultado.

===============================================================================
ESQUEMA GENERAL
===============================================================================
                  USUARIO
                     v
              FORMULARIO HTML
                     | Pulsa "Enviar"
                     v
               PETICIÓN POST
                     v
                compra.php
                     v
             LEER DATOS $_POST
                     v
                VALIDACIONES
                     v
              ¿EXISTEN ERRORES?
                     |
              +------+------+
             SÍ             NO
              v             v
        GUARDAR ERRORES   CALCULAR COMPRA
              v             v
        MOSTRAR ERRORES   MOSTRAR RESULTADO

===============================================================================
CONCEPTOS DE PHP QUE UTILIZAREMOS
===============================================================================
    require_once                    Variables                   Arrays                          
    Arrays asociativos              Arrays superglobales        $_POST
    $_SERVER                        if / else                   Operador ??
    Operadores ===, !==, || y &&    filter_var()                FILTER_VALIDATE_EMAIL
    FILTER_VALIDATE_INT             Funciones                   Comparaciones
    Valores null, true y false      Acumulación de errores en un array
=============================================================================== */


/* ============================================================================
1. CARGAR LOS ARCHIVOS NECESARIOS
===============================================================================
require_once permite incorporar otro archivo PHP.
En este proyecto tenemos dos archivos fundamentales:
    datos.php: Contiene los productos de nuestra tienda. Por ejemplo:
            $productos = [
                [
                    "id" => 1,
                    "nombre" => "Teclado mecánico",
                    "precio" => 7990,
                    "stock" => 7
                ]];
    funciones.php
        Contiene funciones que vamos a reutilizar. Por ejemplo:
            leerCadena()
            buscarProductoPorId()
            calcularCompra()
¿Por qué utilizamos require_once y no copiamos todo aquí?
Porque queremos separar responsabilidades:
    datos.php       -> DATOS
    funciones.php   -> FUNCIONES REUTILIZABLES
    compra.php      -> LÓGICA DE LA COMPRA
La palabra "once" significa "una vez".
Si el archivo ya se ha incluido anteriormente en la misma ejecución,
PHP no vuelve a incluirlo.
Además, si el archivo requerido no existe o no puede cargarse,
PHP produce un error que impide continuar normalmente la ejecución.
=============================================================================== */
require_once "datos.php";
require_once "funciones.php";


/* ============================================================================
2. INICIALIZACIÓN DE VARIABLES
===============================================================================
Antes de procesar el formulario vamos a preparar todas las variables que necesitaremos.
Esto es importante porque el mismo archivo puede ejecutarse en dos situaciones:
    A) El usuario acaba de entrar y todavía no ha enviado el formulario.
    B) El usuario ya ha enviado el formulario mediante POST.
Queremos que las variables tengan un valor inicial conocido.
Así evitamos trabajar con variables que todavía no existen.
=============================================================================== */

/* $errores será un ARRAY que almacenará los mensajes de error.
Inicialmente: $errores = [];
Significa: "Todavía no hemos encontrado ningún error".
Si posteriormente encontramos un problema, añadiremos un mensaje:
    $errores[] = "El nombre es obligatorio";
Podemos acumular varios errores:
    $errores = [
        "El nombre es obligatorio",
        "El correo electrónico no es válido",
        "No hay stock suficiente"
    ];
¿Por qué un array y no una única variable de texto? Porque queremos poder 
mostrar TODOS los errores encontradosy no solamente el último. */
$errores = [];


/* $compraRealizada es una variable de tipo BOOLEANO.
Inicialmente la compra NO se ha calculado correctamente:
Cuando todas las validaciones sean correctas, cambiaremos su valor a true;
Después, en el HTML, podremos utilizar: if ($compraRealizada)
para decidir si mostramos el resumen de compra. */
$compraRealizada = false;


/* $resultadoCompra almacenará el resultado devuelto por calcularCompra().
Cuando realicemos el cálculo, esta variable podrá contener un array
asociativo con los importes:
    [
        "subtotal" => ...,
        "descuento" => ...,
        "base" => ...,
        "iva" => ...,
        "total" => ...
    ]
Los valores exactos dependerán de la función calcularCompra(). */
$resultadoCompra = null;


/* $productoSeleccionado almacenará el producto que quiere comprar el usuario.
Podremos guardar un producto encontrado en el array $productos.
Por ejemplo:
    [
        "id" => 1,
        "nombre" => "Teclado mecánico",
        "precio" => 7990,
        "stock" => 7
    ] */
$productoSeleccionado = null;


/* Variables donde almacenaremos los datos del formulario.
$nombre y $email comienzan como cadenas vacías.
Esto permite que, cuando mostremos el formulario por primera vez,
los campos aparezcan inicialmente vacíos. */
$nombre = "";
$email = "";


/* $unidades comienza con el valor porque queremos que por defecto, el 
usuario solicite una unidad. También podremos utilizar esta variable para 
rellenar el atributo value de un input HTML:
    <input type="number" name="unidades" value="1"> */
$unidades = 1;


/* $productoId almacenará el identificador del producto seleccionado.
Inicialmente, se iguala a null  porque todavía no sabemos qué producto 
quiere comprar el usuario. Más adelante, si recibimos un ID válido, 
lo guardaremos aquí. */
$productoId = null;


/* ============================================================================
3. COMPROBAR SI EL FORMULARIO SE HA ENVIADO
===============================================================================
Un archivo PHP puede recibir peticiones utilizando diferentes métodos HTTP.
Los más habituales en este nivel son: GET y POST.
    GET: Se utiliza habitualmente para solicitar información.
    POST: Se utiliza habitualmente para enviar datos que deben procesarse.
        Por ejemplo, al enviar un formulario de compra.
En HTML tendríamos:
    <form action="compra.php" method="POST">
        ...
        <button type="submit">
            Calcular compra
        </button>
    </form>
Cuando el usuario pulsa el botón, el navegador envía una petición POST.

¿QUÉ ES $_SERVER?
-----------------
$_SERVER es un ARRAY SUPERGLOBAL de PHP.
Contiene información sobre la petición y el entorno del servidor.
Una de sus claves es:
    $_SERVER["REQUEST_METHOD"]
que indica el método HTTP utilizado.
    Por ejemplo:
        "GET" - "POST"
La condición: $_SERVER["REQUEST_METHOD"] === "POST"
pregunta:
    "¿El método de esta petición es exactamente POST?"
(=== significa IGUALDAD ESTRICTA. Compara tanto el valor como el tipo).
Si la condición es verdadera: Procesamos el formulario.
Si es falsa: No ejecutamos este bloque de validación.
=============================================================================== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* ========================================================================
    4. VALIDACIÓN DEL NOMBRE
    ===========================================================================
    Queremos obtener el nombre introducido por el usuario.
    En el formulario HTML tendremos algo parecido a:
        <input type="text" name="nombre">
    En nuestro proyecto utilizamos una función auxiliar:
        leerCadena($_POST, "nombre")
    Esta función se encarga de:
        1. Buscar la clave "nombre" dentro de $_POST.
        2. Comprobar que el valor sea una cadena.
        3. Eliminar espacios al principio y al final.
        4. Devolver una cadena.
    Esto evita que un usuario pueda introducir solamente espacios
    para superar nuestra comprobación de campo obligatorio.
    =========================================================================== */
    $nombre = leerCadena($_POST, "nombre");

    /* Comprobamos si el nombre está vacío.
    $nombre === ""  significa:
        "¿El nombre es exactamente una cadena vacía?"
    Si está vacío, añadimos un mensaje al array $errores. */
    if ($nombre === "") {

        /* La expresión:  $errores[]
        significa: "Añade un nuevo elemento al final del array".
        No necesitamos indicar una posición concreta.
        PHP añade el elemento automáticamente.
        Por ejemplo:
            $errores = [];
            $errores[] = "Falta el nombre";
        Resultado:
            ["Falta el nombre"] */
        $errores[] = "Debes de introducir un nombre";
    }


    /* ========================================================================
    5. VALIDACIÓN DEL CORREO ELECTRÓNICO
    ===========================================================================
    Ahora necesitamos obtener y validar el email del usuario.
    En HTML tendremos:
        <input type="email" name="email">
    Primero leemos el dato:
        leerCadena($_POST, "email")
    Lo almacenamos en una variable llamada:
        $emailBruto
    Utilizamos el nombre "Bruto" para indicar que todavía NO hemos
    comprobado si tiene un formato correcto. Es decir:
    que un dato llegue desde el formulario NO significa que sea válido.
    =========================================================================== */
    $emailBruto = leerCadena($_POST, "email");
    $email = $emailBruto;


    /* Utilizamos filter_var(), que es una función incorporada en PHP 
    que permite validar o filtrar datos. Su estructura general es:
        filter_var(VALOR, FILTRO);
    En este caso:
        VALOR:
            $emailBruto
        FILTRO:
            FILTER_VALIDATE_EMAIL
    FILTER_VALIDATE_EMAIL es una CONSTANTE PREDEFINIDA DE PHP.
    Indica que queremos comprobar si el valor tiene un formato
    sintácticamente válido de dirección de correo electrónico.
    Ejemplos:
        "pepe@gmail.com"    -> válido
        "pepe"              -> inválido
        "pepe@"             -> inválido
        "@gmail.com"        -> inválido
    Si el formato es válido: Devuelve el email validado.
    Si el formato es incorrecto: Devuelve false. */
    $emailValidado = filter_var($emailBruto, FILTER_VALIDATE_EMAIL);


    /* Comprobamos el resultado de la validación.
    Utilizamos === porque queremos comprobar exactamente el valor false. */
    if ($emailValidado === false) {

        /* Si el email no es válido, añadimos un nuevo error.
        No detenemos el programa inmediatamente. Queremos seguir comprobando 
        los demás campos para poder mostrar varios errores juntos al usuario. */
        $errores[] = "Correo electrónico con formato incorrecto";

    } else {

        /* Si llegamos aquí, el formato del email es válido. Guardamos el resultado 
        en la variable $email. Esta variable se podrá utilizar posteriormente 
        para mostrar el resumen de compra. */
        $email = $emailValidado;
    }


    /* ========================================================================
    6. VALIDACIÓN DEL IDENTIFICADOR DEL PRODUCTO
    ===========================================================================
    El usuario seleccionará un producto mediante un <select>.
    Por ejemplo:
        <select name="producto">
            <option value="">Selecciona un producto</option>
            <option value="1">Teclado mecánico</option>
            <option value="2">Ratón gaming</option>
        </select>
    Cuando el usuario selecciona el producto 2, el formulario envía:
        producto=2
    En PHP podemos acceder mediante:   $_POST["producto"]
    
    Los datos que llegan desde formularios suelen recibirse como
    cadenas de texto.Por ejemplo: "2"
    Pero nosotros queremos trabajar con un ID entero: 2
    Además, no debemos confiar en que el usuario haya seleccionado
    necesariamente una de las opciones que aparecen en el HTML.
    Una petición HTTP puede modificarse manualmente.
    Por eso debemos validar el ID en el servidor.
    =========================================================================== */

    /* Primero obtenemos el dato:
        $_POST["producto"] ?? ""
    El operador ?? se llama OPERADOR DE FUSIÓN NULA. Significa:
        "Utiliza el valor de la izquierda si existe y no es null;
         en caso contrario, utiliza el valor de la derecha".
    Por ejemplo:
        $_POST["producto"] = "2"  -->   Resultado: "2"
    Pero si no existe la clave "producto":
        $_POST["producto"] ?? ""   -->   Resultado: ""
    Así evitamos intentar acceder directamente a una clave inexistente.
    Después aplicamos:    FILTER_VALIDATE_INT
    Este filtro comprueba si el valor representa un número entero válido.
    Ejemplos:
        "2"       -> 2
        "15"      -> 15
        "hola"    -> false
        "3.5"     -> false
    IMPORTANTE:
    No es lo mismo validar que convertir mediante (int). Por ejemplo: 
    (int) "hola" produce: 0    En cambio:
        filter_var("hola", FILTER_VALIDATE_INT)   devuelve:  false
    La validación nos permite detectar que el dato original
    no representaba un entero válido. */


    /* 1. Recogemos $_POST["producto"] (que sale del value="..." de la <option> elegida).
     *    - Si no existe, ?? "" evita errores pasando una cadena vacía.
     *    - filter_var comprueba si es un entero válido: si llega "2", devuelve el int 2;
     *      si llega vacío "" o texto, devuelve false. */
    $productoIdValidado = filter_var($_POST["producto"] ?? "", FILTER_VALIDATE_INT);


    /* Ahora comprobamos dos condiciones:
        $productoIdValidado === false  O BIEN:  $productoIdValidado < 1
    El operador || significa OR lógico. Basta con que UNA de las condiciones sea 
    verdadera para entrar en el if.
    Primera condición:
        El valor no representa un entero válido.
    Segunda condición:
        El número es menor que 1.
    IMPORTANTE:
    Que el ID sea un entero positivo NO significa todavía
    que el producto exista realmente.*/

    // 2. Comprobamos si falló la validación (es false) o si el ID no es positivo (< 1).
    if ($productoIdValidado === false || $productoIdValidado < 1) {
        $errores[] = "Debes de seleccionar un producto válido";
    
        } else {
    /* Si llegamos al else, el ID tiene un formato válido y es mayor que cero. 
    Por lo que Lo guardamos en la variable $productoId. */

        /* 3. ASIGNACIÓN CLAVE: Como el número es válido, lo guardamos en $productoId.
         *    Este $productoId (ya como int) es el que usará más abajo el <select>
         *    en ($productoId === $producto["id"]) para dejar marcada la opción "selected". */
        $productoId = $productoIdValidado;


        /* 4. Buscamos el producto en el array $productos para verificar que ese ID
        realmente existe en el catálogo (ej. que no enviaron un ID 999 inexistente).
        Para ello llamamos a:
            buscarProductoPorId($productos, $productoId)
        Y le pasamos DOS argumentos:
            1. $productos
                    El array que contiene todos los productos.
            2. $productoId
                    El identificador que queremos buscar.
        La función recorrerá los productos y buscará uno cuyo identificador coincida con el solicitado.
            Si buscarProductoPorId($productos, 2)
        encuentra el producto: Devuelve su array asociativo.
        Si no lo encuentra: Devuelve null.
        Por tanto, podemos distinguir: ID con formato correcto
        de: ID que realmente existe en la tienda. */
        $productoSeleccionado = buscarProductoPorId($productos,$productoId);


        /* Comprobamos si la búsqueda ha devuelto null.
            === null   significa que no hemos encontrado ningún producto.
        Ejemplo: ID = 999 Puede ser un entero perfectamente válido.
        Pero si no existe un producto con ese ID:
            buscarProductoPorId(...)   devolverá:   null  */
        if ($productoSeleccionado === null) {
            $errores[] = "El producto seleccionado no existe.";
        }
    }


    /*=========================================================================
    7. VALIDACIÓN DEL NÚMERO DE UNIDADES
    ===========================================================================
    En el formulario tendremos algo parecido a:
        <input type="number" name="unidades" min="1">
    El usuario debe indicar cuántas unidades quiere comprar.
    Queremos exigir dos condiciones:
        1. Que sea un número entero.
        2. Que sea mayor que cero.
    IMPORTANTE:
    Aunque HTML tenga:
        type="number"     y:     min="1"
    debemos volver a validar el dato en PHP. (Las restricciones del navegador NO 
    sustituyen las comprobaciones del servidor).
    ===========================================================================*/
    $unidadesValidadas = filter_var($_POST["unidades"] ?? "",FILTER_VALIDATE_INT);

    /* Comprobamos:
        ¿No es un entero válido?     O     ¿Es menor que 1?
    Si alguna condición se cumple, añadimos un error. */
    if ($unidadesValidadas === false || $unidadesValidadas < 1) {
        $errores[] =
            "El número de unidades debe de ser un entero mayor que cero";
    } else {
        /* Si el valor es correcto lo guardamos en $unidades y a partir de 
        aquí, esta variable contiene un número entero positivo. */
        $unidades = $unidadesValidadas;
    }


    /*=========================================================================
    8. COMPROBAR QUE HAY STOCK SUFICIENTE
    ===========================================================================
    Ya hemos validado:
        - El nombre.
        - El email.
        - El ID del producto.
        - El número de unidades.
    Ahora necesitamos aplicar una REGLA DE NEGOCIO:
        "No podemos vender más unidades de las que tenemos".
    Para hacer esta comprobación necesitamos conocer:
        $productoSeleccionado["stock"]
    Sin embargo, debemos tener cuidado porque: ¿Qué ocurre si el producto 
    no existe? En ese caso:
        $productoSeleccionado === null
    Y no podemos intentar acceder a: null["stock"]
    Por eso, primero comprobamos que tenemos un producto.
    =========================================================================== */


    /* El operador && significa AND lógico. Para que la condición completa sea verdadera,
       ---------------------------------------------------------------------------------
    deben cumplirse AMBAS condiciones.
    ---------------------------------
    CONDICIÓN 1:      $productoSeleccionado !== null
        Significa: "Hemos encontrado un producto".
    CONDICIÓN 2:     $unidades > $productoSeleccionado["stock"]
        Significa: "El usuario solicita más unidades de las disponibles".

    PHP utiliza evaluación de cortocircuito con &&.
    Si la primera condición es falsa, no necesita evaluar la segunda.
    Esto evita acceder al stock cuando no tenemos un producto válido. */

    if ($productoSeleccionado !== null && $unidades > $productoSeleccionado["stock"]) {
        $errores[] = "No hay stock suficiente";
    }


    /* ========================================================================
    9. COMPROBAR SI TODAS LAS VALIDACIONES HAN SIDO CORRECTAS
    ===========================================================================
    Hemos terminado de validar los campos. Durante las comprobaciones hemos ido 
    guardando mensajes en el array $errores. Ahora tenemos dos situaciones posibles:

        SITUACIÓN A: HAY ERRORES  -->  No debemos calcular la compra
        SITUACIÓN B: NO HAY ERRORES  -->  Significa que todas las validaciones han 
        sido superadas y podemos realizar el cálculo.
    =========================================================================== */
    /* La condición:     $errores === []     
            pregunta: "¿El array $errores está exactamente vacío?" */
    if ($errores === []) {


        /* ====================================================================
        10. CALCULAR LOS IMPORTES DE LA COMPRA
        =======================================================================
        Si llegamos aquí significa que:
            - El nombre no está vacío.
            - El email tiene un formato válido.
            - El ID del producto es válido.
            - El producto existe.
            - Las unidades son un entero positivo.
            - No se ha detectado falta de stock.
        Por tanto, ya podemos calcular la compra. Para ello utilizamos nuestra función:
            calcularCompra()
        Esta función recibe DOS argumentos:     
            El precio del producto.     -     El número de unidades.

        La función se encargará de calcular los importes según las reglas definidas 
        en funciones.php.
        ======================================================================= */
        $resultadoCompra = calcularCompra($productoSeleccionado["precio"],$unidades);


        /* ====================================================================
        11. MARCAR QUE EL CÁLCULO SE HA COMPLETADO
        =======================================================================
        Una vez calculada la compra, cambiamos:     $compraRealizada = false;
                                          por:      $compraRealizada = true;
        Esta variable actuará como una BANDERA o INDICADOR.
        En programación, una bandera es una variable que nos permite
        recordar si ha ocurrido una determinada situación.
        Después, en el HTML, podremos escribir:
            if ($compraRealizada)     para mostrar el resumen.
        Si es false  -->  Mostraremos el formulario.
        Si es true:  -->  Mostraremos el resultado de la compra.
        ======================================================================= */
        $compraRealizada = true;
    }


    /*=========================================================================
    FIN DEL PROCESAMIENTO POST
    ===========================================================================
    Aquí termina el bloque que se ejecuta cuando el usuario ha enviado el formulario 
    mediante POST. A partir de este punto ya tenemos preparadas las variables
    que necesitaremos para generar la respuesta HTML.
    =========================================================================== */
}


/* ============================================================================
12. ¿QUÉ OCURRE DESPUÉS DE ESTE CÓDIGO?
===============================================================================
Después del bloque PHP normalmente aparecerá el HTML de la página.
En ese HTML podemos utilizar las variables que acabamos de preparar.

CASO 1: LA COMPRA SE HA CALCULADO CORRECTAMENTE
----------------------------------------------
    $compraRealizada === true
Mostraremos el resumen utilizando:
    $resultadoCompra
    $nombre
    $email
    $productoSeleccionado
    $unidades

CASO 2: NO SE HA CALCULADO LA COMPRA
-----------------------------------
    $compraRealizada === false
Mostraremos el formulario.
Si además:
    $errores !== []
mostraremos los mensajes de error.
Podemos recorrerlos utilizando:
    foreach ($errores as $error)
Y mostrarlos en HTML utilizando:
    escapar($error)

==========================================
IDEA FUNDAMENTAL
==========================================
La lógica de un formulario PHP debe seguir este orden:
    1. PREPARAR VARIABLES.
    2. COMPROBAR SI EL FORMULARIO SE HA ENVIADO.
    3. LEER LOS DATOS RECIBIDOS.
    4. VALIDAR LOS DATOS.
    5. APLICAR LAS REGLAS DEL NEGOCIO.
    6. COMPROBAR SI EXISTEN ERRORES.
    7. REALIZAR EL CÁLCULO SI TODO ES CORRECTO.
    8. GENERAR LA RESPUESTA HTML.

=============================================================
REGLA PARA RECORDAR
=============================================================
        NUNCA CONFIAR EN LOS DATOS QUE LLEGAN DEL CLIENTE.
        VALIDAR AL ENTRAR.
        PROCESAR SOLO LOS DATOS CORRECTOS.
        ESCAPAR AL MOSTRAR DATOS EN HTML.
============================================================= */
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


<!--
SITUACIÓN DE PARTIDA
--------------------
En la parte superior de compra.php ya hemos programado la lógica que:
    1. Recibe los datos enviados mediante POST.
    2. Valida el nombre.
    3. Valida el email.
    4. Comprueba que el producto exista.
    5. Valida el número de unidades.
    6. Comprueba el stock disponible.
    7. Calcula la compra si no hay errores.
Ahora debemos utilizar los resultados de esa lógica para generar el HTML.

VARIABLES QUE YA TENEMOS DISPONIBLES
------------------------------------
$errores: Array que contiene los mensajes de error.
    Si está vacío, significa que no se han detectado errores.
$compraRealizada: Booleano que indica si la compra se ha calculado correctamente.
    true  -> Mostrar el resumen de compra.
    false -> Mostrar el formulario.
$resultadoCompra: Array asociativo que contiene los importes calculados.
    Sus claves son:
        "subtotal"
        "descuento"
        "base"
        "iva"
        "total"
$productoSeleccionado: Contiene el producto seleccionado, cuando se ha encontrado.
$nombre: Contiene el nombre introducido por el usuario.
$email: Contiene el email validado.
$unidades: Contiene el número de unidades.
$productoId: Contiene el ID del producto seleccionado.

OBJETIVO FINAL
--------------
Queremos que nuestra página funcione de esta manera:
                  compra.php
                      v
             ¿Compra calculada?
                      |
               +------+------+
               |             |
              SÍ             NO
               v             v
          MOSTRAR         ¿HAY ERRORES?
          RESUMEN              |
                         +----+----+
                         |         |
                        SÍ         NO
                         v         v
                     MOSTRAR    MOSTRAR
                     ERRORES   FORMULARIO
                         v
                     MOSTRAR
                    FORMULARIO
IMPORTANTE:
Si hay errores, el usuario debe poder corregir el formulario.
No queremos que tenga que volver a empezar desde cero.
============================================================= -->

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

    <!-- IF QUE COMPRUEBA $compraRealizada -->
    <?php if ($compraRealizada): ?>


    <!-- SECCIÓN CON EL RESUMEN -->
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

    <!-- Con action le decimos al formulario dónde se envían los datos.
    (En el fondo, para el servidor ésto es una URL). Esto significa 
    que éste archivo es el que contiene la lógica para tratar los datos.
    El método POST indica cómo se envían los datos, y los va a enviar en 
    el cuerpo de la solicitud, y NO en la URL (como en el caso de GET). 
    Así que al pulsar el botón submit, el navegador envía los datos del 
    formulario mediante POST a compra.php.-->
    <form action="compra.php" method="POST" class="formulario">

    <!--=======================================================================
        CAMPO 1: NOMBRE
        =======================================================================      -->
        <div class="campo">

            <!-- <label> muestra el texto descriptivo del campo.
            El atributo: for="nombre" se relaciona con el atributo: id="nombre" del input.
            (Quiere decir que el <label> está asociado al <input> cuyo id coincide 
            con el valor de for). 
            Al hacer clic en «Nombre», el cursor se activa en ese campo. Además, esta 
            asociación ayuda a tecnologías de asistencia, como los lectores de pantalla. 
            (El valor de for debe coincidir exactamente con el id del input).  -->
            <label for="nombre">Nombre</label>

            <!-- El atributo value permite indicar el valor que debe aparecer dentro 
             del campo. Aquí estamos utilizando:
            value="< ?= escapar($nombre) ?>"
            Porque si el usuario envía el formulario y comete un error,
            queremos conservar el nombre que había escrito.
                Ejemplo:
                    - El usuario escribe "Pepe".
                    - Introduce un email incorrecto.
                    - PHP detecta el error.
                    - Volvemos a mostrar el formulario.
                    - El campo nombre debe seguir mostrando "Pepe" porque lógica de compra.php 
                    ya ha leído el nombre introducido por el usuario y lo ha guardado en la variable $nombre. 
                    De manera que si hay errores, el usuario podrá corregir el formulario sin tener que 
                    volver a empezar desde cero..
            IMPORTANTE:
            escapar() es necesario porque el valor se introduce dentro de un atributo HTML.
            En este caso, nuestra función de escape HTML también convierte las comillas en 
            entidades seguras. -->
            <!-- id relaciona la entrada de formulario con su label (el for del <label> debe 
                tener el mismo valor que el id del <input>), Así, al hacer clic en la etiqueta
                <label> que se renderiza en la pantalla, se activa el campo de texto.
            name es lo que PHP usa para recibir el dato, permitiendo acceder a su valor, 
            por ejemplo mediante $_POST['nombre']. -->
            <input type="text" id="nombre" name="nombre" value="<?= escapar($nombre) ?>">
        </div>


    <!--=======================================================================
        CAMPO 2: EMAIL
        =======================================================================
        type="email" indica que esperamos un correo electrónico.
        El navegador puede realizar una comprobación básica del formato del email.
        Sin embargo, NO debemos confiar solamente en esa validación.
        En PHP ya hemos utilizado:
            filter_var($emailBruto, FILTER_VALIDATE_EMAIL),
        y la validación se realizará tras enviar el formulario
        El atributo: name="email" 
            permite acceder al dato mediante: $_POST["email"]
        El atributo value intenta conservar el email almacenado en la variable 
            $email. -->
        <div class="campo">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= escapar($email) ?>"
            >
        </div>

    <!--=======================================================================
        CAMPO 3: SELECCIÓN DE PRODUCTO
        =======================================================================
        Este campo es interesante porque las opciones NO están escritas 
        manualmente. Las vamos a generar recorriendo el array $producto a partir 
        de los productos disponibles en datos.php. Para eso utilizamos:
            foreach ($productos as $producto)
        Y por cada producto generamos un <option>.
        ======================================================================= -->
        <div class="campo">

            <label for="producto">Producto</label>

            <!-- En HTML, un desplegable utiliza: <select>
            Y cada opción utiliza: <option>
                Ejemplo:
                    <select name="producto">
                        <option value="1">Teclado</option>
                        <option value="2">Ratón</option>
                    </select> 
                (Para generar una <option> por cada producto utilizamos foreach -->
            <select id="producto" name="producto">

            <!-- En cada opción: value="..." debe aparecer el ID del producto.
                Por ejemplo:
                    <option value="2">Ratón gaming</option>
                Si el usuario selecciona esa opción, PHP recibirá:
                    $_POST["producto"] = "2" 
                value="" permite representar que todavía no se ha seleccionado ningún producto. 
                En PHP, un valor vacío no superará la validación FILTER_VALIDATE_INT. -->
                <option value="">Selecciona un producto</option>

                <?php
                /*
                 * ============================================================================
                 * FLUJO DE EJECUCIÓN PASO A PASO (RECORDAR LA OPCIÓN ELEGIDA EN EL <select>)
                 * ============================================================================
                 * 1. PRIMERA VEZ QUE ENTRAS EN LA PÁGINA (GET):
                 *    - No se ha ejecutado el bloque POST de arriba, así que $productoId es null.
                 *    - Al comparar null === $producto["id"], $seleccionado da siempre false
                 *      y ninguna <option> lleva el atributo "selected".
                 *
                 * 2. TRAS ENVIAR EL FORMULARIO (POST) ELIGIENDO UN PRODUCTO (ej. Ratón, value="2"):
                 *    - El navegador envía $_POST["producto"] = "2".
                 *    - Arriba, filter_var lo valida y lo convierte al entero 2, y en el else
                 *      se asigna: $productoId = $productoIdValidado (ahora $productoId vale 2).
                 *    - Al llegar a este foreach, se comparan los ID de cada producto con 2:
                 *        * Vuelta 1 (Teclado, ID 1): ¿2 === 1? -> false -> <option value="1">
                 *        * Vuelta 2 (Ratón, ID 2):   ¿2 === 2? -> true  -> <option value="2" selected>
                 *        * Vuelta 3 (Monitor, ID 3): ¿2 === 3? -> false -> <option value="3">
                 * ============================================================================
                 */
                foreach ($productos as $producto):
                    /*
                     * PASO 1: ¿Es este el producto que eligió el usuario?
                     * Comparamos el ID enviado ($productoId) con el ID del producto actual
                     * ($producto["id"]). Guardamos true si coinciden o false si no.
                     */
                    $seleccionado = ($productoId === $producto["id"]);

                    /*
                     * PASO 2: Dibujar la opción (<option>) y marcarla si corresponde
                     * - value="<?= $producto["id"] ?>": Define el ID que se enviará en $_POST["producto"].
                     * - <?= $seleccionado ? "selected" : "" ?>: Operador ternario (if/else abreviado).
                     *   Si $seleccionado es true, escribe "selected"; si es false, no escribe nada ("").
                     */
                ?>
                    <option value="<?= $producto["id"] ?>" <?= $seleccionado ? "selected" : "" ?>>
                        
                        <!--EL TEXTO VISIBLE:
                        Queremos mostrar: Nombre del producto - Precio
                        Por ejemplo: Ratón gaming - 39,90 €
                        Utilizamos escapar() para mostrar el nombre. Y:
                        formatearPrecio() para mostrar el precio. -->
                        <?= escapar($producto["nombre"]) ?> - <?= formatearPrecio($producto["precio"]) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

    <!--=======================================================================
        CAMPO 4: UNIDADES
        =======================================================================
        type="number"  permite introducir valores numéricos.
        name="unidades"  indica que PHP recibirá el valor mediante:
            $_POST["unidades"]
        El atributo: value=" < ? = $unidades ? >" permite conservar el número de unidades.
        ======================================================================= -->
        <div class="campo">
            <label for="unidades">Unidades</label>
            <input type="number" id="unidades" name="unidades" value="<?= $unidades ?>" min="1">
        </div>

        <p>A partir de <strong> <?= UNIDADES_DESCUENTO ?> </strong> unidades se aplica <strong> <?= DESCUENTO_CANTIDAD * 100 ?> %</strong> de descuento </p>

    <!--=======================================================================
        BOTÓN DE ENVÍO
        =======================================================================
        type="submit" indica que este botón envía el formulario.
        Cuando el usuario pulse el botón:
            1. El navegador recoge los campos del formulario.
            2. Utiliza sus atributos name.
            3. Envía los datos a compra.php mediante POST.
            4. PHP vuelve a ejecutar compra.php desde el principio.
            5. Entra en el bloque:
                if ($_SERVER["REQUEST_METHOD"] === "POST")
            6. Valida todos los datos.
            7. Prepara los errores o el resultado.
            8. Genera el HTML correspondiente.
        IMPORTANTE:
        Pulsar el botón NO significa que JavaScript ejecute automáticamente una función.
        En este ejercicio el procesamiento se realiza en PHP en el servidor.
        ======================================================================= -->
        <div>
            <button type="submit">Calcular compra</button>
        </div>

    </form>
   </section>

    <?php endif; ?>

</main>


<!--
===============================================================================
COMPROBACIONES FINALES
===============================================================================
Cuando hayáis completado todos los TODO, probad lo siguiente:
PRUEBA 1
--------
Entrar directamente en:
    compra.php
RESULTADO ESPERADO:
    Debe aparecer el formulario.
    
PRUEBA 2
--------
Enviar el formulario sin introducir un nombre.
RESULTADO ESPERADO:
    Debe aparecer un mensaje de error.

PRUEBA 3
--------
Introducir un email incorrecto.
RESULTADO ESPERADO:
    Debe aparecer el error correspondiente.

PRUEBA 4
--------
No seleccionar ningún producto.
RESULTADO ESPERADO:
    Debe aparecer un error indicando que falta un producto válido.

PRUEBA 5
--------
Seleccionar un producto y solicitar más unidades
de las que existen en stock.
RESULTADO ESPERADO:
    Debe aparecer un mensaje de stock insuficiente.

PRUEBA 6
--------
Introducir todos los datos correctamente.
RESULTADO ESPERADO:
    Debe aparecer el resumen de compra.

PRUEBA 7
--------
Solicitar una cantidad de unidades que permita aplicar descuento,
siempre que exista stock suficiente.
RESULTADO ESPERADO:
    Debe aparecer la fila correspondiente al descuento.

PRUEBA 8
--------
Pulsar el enlace "Realizar otra compra".
RESULTADO ESPERADO:
    Debe volver a aparecer el formulario.

===============================================================================
PREGUNTAS PARA REFLEXIONAR
===============================================================================
1. ¿Por qué utilizamos POST para enviar el formulario?
2. ¿Para qué sirve el atributo name de un input?
3. ¿Qué diferencia existe entre id y name?
4. ¿Por qué utilizamos escapar() al mostrar el nombre?
5. ¿Por qué utilizamos formatearPrecio()?
6. ¿Por qué necesitamos un foreach para generar el select?
7. ¿Para qué sirve el atributo selected?
8. ¿Qué diferencia existe entre $errores y $compraRealizada?
9. ¿Por qué mostramos el formulario cuando hay errores?
10. ¿Por qué no debemos confiar solamente en la validación HTML?

===============================================================================
OBJETIVO DE APRENDIZAJE: comprender cómo se conectan:
===============================================================================
    HTML
      |
      v
    FORMULARIO
      |
      v
    POST
      |
      v
    PHP
      |
      v
    VALIDACIONES
      |
      v
    VARIABLES
      |
      v
    HTML GENERADO DINÁMICAMENTE

La idea más importante es:
    PHP DECIDE QUÉ HTML SE GENERA SEGÚN EL RESULTADO
    DEL PROCESAMIENTO DEL FORMULARIO.
=============================================================================== -->

</body>
</html>