<?php

/**
 * ============================================================================
 * ARCHIVO: preferencias.php
 * PROYECTO: MiniTienda PHP
 * ============================================================================
 * OBJETIVO:
 * Permitir al visitante configurar sus preferencias de navegación (nombre y 
 * tema visual claro/oscuro) almacenándolas directamente en el navegador 
 * mediante cookies HTTP, así como ofrecer la opción de eliminarlas.
 * ============================================================================
 */

// ----------------------------------------------------------------------------
// 1. CARGA DE CONFIGURACIÓN E INICIO DE SESIÓN
// ----------------------------------------------------------------------------
// __DIR__ devuelve la ruta absoluta de la carpeta actual.
// Se incluye 'includes/config.php' una única vez (require_once), lo que arranca 
// la sesión, calcula la variable $https y carga las funciones de 'funciones.php'.
require_once __DIR__ . '/includes/config.php';


// ----------------------------------------------------------------------------
// 2. PROCESAMIENTO DE FORMULARIOS (CUANDO LA PETICIÓN ES POST)
// ----------------------------------------------------------------------------
// Comprueba si el usuario ha enviado alguno de los formularios de la página.
// ($_SERVER['REQUEST_METHOD'] ?? '') obtiene el método HTTP; si no existiera,
// el operador de fusión de null (??) devuelve '' para evitar avisos de PHP.
// Con === 'POST' verificamos estrictamente que sea una petición POST.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    // Verifica por seguridad que el método sea POST (y valida el token CSRF 
    // cuando está implementado). Si no es válido, aborta con código HTTP 405.
    exigirPostValido();

    /*
     * ¿DE DÓNDE SALEN 'accion' Y 'guardar'?
     * No están declaradas antes en PHP, sino que vienen de los dos formularios HTML
     * que hay más abajo en este mismo archivo:
     *   - Formulario 1 (Guardar): <input type="hidden" name="accion" value="guardar">
     *   - Formulario 2 (Borrar):  <input type="hidden" name="accion" value="borrar">
     *
     * Al pulsar el botón submit de uno de los dos formularios, el navegador mete
     * ese name="accion" dentro de $_POST['accion'] con su respectivo value.
     * Aquí creamos la variable $accion para leer cuál de los dos se pulsó.
     * El operador ?? 'guardar' asigna 'guardar' por defecto si no llegara el campo.
     */
    $accion = $_POST['accion'] ?? 'guardar';

    // Define el array asociativo de configuración para las cookies (setcookie):
    $opciones = [
        // 'expires': Fecha de caducidad en formato timestamp Unix.
        // time() es el segundo actual + (30 días * 24h * 60min * 60seg) = 30 días de duración.
        'expires' => time() + 30 * 24 * 60 * 60,

        // 'path': '/' hace que la cookie esté disponible en todas las páginas de la web.
        'path' => '/',

        /*
         * 'secure': Controla si la cookie exige una conexión cifrada (HTTPS).
         * - Si es true: el navegador SOLO envía la cookie por https:// (protege
         *   frente a robos en la red) y la bloquea en http://.
         * - Si es false: permite enviarla también por http:// normal.
         *
         * Usamos la variable dinámica $https (calculada en config.php) para que
         * se adapte sola al entorno sin cambiar el código:
         * - En local (http://localhost): $https vale false -> funciona en clase.
         * - En producción (https://...): $https vale true -> blinda la cookie.
         */
        'secure' => $https,

        // 'httponly': true impide que JavaScript acceda a la cookie mediante 
        // document.cookie, protegiéndola frente a ataques XSS.
        'httponly' => true,

        // 'samesite': 'Lax' restringe el envío de la cookie en peticiones desde 
        // sitios externos para mitigar ataques CSRF.
        'samesite' => 'Lax',
    ];

    // Siguiendo la convención de poner el caso más simple en el 'if' y el más 
    // complejo en el 'else', comprobamos primero si la acción es 'borrar':
    if ($accion === 'borrar') {

        // Para borrar una cookie en el navegador, se fija su fecha de expiración 
        // en el pasado: time() - 3600 equivale a hace 1 hora (3600 segundos).
        $opciones['expires'] = time() - 3600;

        // Envía la orden al navegador de vaciar ('') y caducar inmediatamente la cookie 'tema'.
        setcookie('tema', '', $opciones);

        // Envía la orden al navegador de vaciar ('') y caducar inmediatamente la cookie 'visitante'.
        setcookie('visitante', '', $opciones);
    } else {
        // Bloque 'else': se ejecuta cuando la acción es 'guardar' (caso por defecto).

        // 1. VALIDACIÓN Y GUARDADO DEL TEMA:
        // Recoge el valor del desplegable 'tema' enviado por POST; si no existe, usa 'claro'.
        $tema = $_POST['tema'] ?? 'claro';

        // Validación por lista blanca (whitelist) con operador ternario (? :):
        // - is_string($tema): comprueba que el dato recibido sea realmente texto (y no un array).
        // - in_array($tema, ['claro', 'oscuro'], true): comprueba de forma estricta (true)
        //   que el valor sea exactamente 'claro' u 'oscuro'.
        // - ? $tema : 'claro': si cumple ambas condiciones se conserva $tema; si alguien 
        //   manipuló el HTML enviando otro valor, se fuerza a 'claro' por seguridad.
        $tema = is_string($tema) &&
            in_array($tema, ['claro', 'oscuro'], true)
            ? $tema : 'claro';

        // Envía la cabecera HTTP para guardar la cookie 'tema' con el valor validado ('claro' u 'oscuro')
        // y las opciones de caducidad (30 días) y seguridad definidas en $opciones.
        // Se ejecuta antes de gestionar el nombre porque si el nombre viene vacío,
        // más abajo modificaremos $opciones['expires'] para caducar la cookie 'visitante'.
        setcookie('tema', $tema, $opciones);

        /*
         * 2. SANEAMIENTO Y VALIDACIÓN DEL NOMBRE DEL VISITANTE ($nombre):
         *    a) Captura inicial:
         *       Recoge el valor enviado por el <input name="visitante"> en $_POST['visitante'].
         *       Si no existe, el operador ?? asigna una cadena vacía ''.
         */
        $nombre = $_POST['visitante'] ?? '';

        /*
         *    b) Comprobación de tipo y limpieza de espacios laterales:
         *       is_string($nombre) verifica que sea texto (evita errores si alguien inyecta
         *       un array en la petición POST). Si es texto, trim() elimina los espacios en
         *       blanco al principio y al final (ej. "  Ana  " pasa a ser "Ana").
         */
        $nombre = is_string($nombre) ? trim($nombre) : '';

        /*
         *    c) Eliminación de caracteres de control invisibles (preg_replace):
         *       Busca mediante una expresión regular todos los caracteres de control ASCII:
         *         - \x00-\x1F : primeros 32 caracteres de control (saltos de línea \n, \r,
         *                       tabuladores \t, carácter nulo \0, etc.).
         *         - \x7F      : carácter DEL (borrar).
         *         - /u        : modificador Unicode para tratar la cadena en UTF-8 correctamente.
         *       Los sustituye por '' (los borra) para evitar inyecciones en cabeceras o textos
         *       rotos. El operador ?? '' garantiza devolver '' si preg_replace diera null por error.
         */
        $nombre = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $nombre
        ) ?? '';

        /*
         *    d) Recorte de longitud máxima:
         *       substr($nombre, 0, 40) corta la cadena desde la posición 0 hasta un máximo
         *       de 40 bytes/caracteres en el servidor, protegiendo la aplicación por si el
         *       usuario eliminó el atributo maxlength="40" del HTML con las herramientas F12.
         */
        $nombre = substr($nombre, 0, 40);

        /*
         * 3. GUARDADO O BORRADO CONDICIONAL DE LA COOKIE 'visitante':
         *    Como el nombre es opcional en el formulario, caben dos posibilidades:
         *    - Caso A ($nombre !== ''): El usuario ha escrito un nombre válido.
         *      Se guarda la cookie 'visitante' con ese nombre y caducidad de 30 días ($opciones).
         *    - Caso B (else -> $nombre está vacío ''): El usuario dejó el campo en blanco
         *      (o borró el nombre que tenía antes y pulsó guardar). En lugar de guardar una
         *      cookie vacía en el navegador, caducamos la cookie 'visitante' restando 3600
         *      segundos (1 hora en el pasado) a $opciones['expires'] para eliminarla.
         */
        if ($nombre !== '') {
            setcookie('visitante', $nombre, $opciones);
        } else {
            $opciones['expires'] = time() - 3600;
            setcookie('visitante', '', $opciones);
        }
    }

    /*
     * 4. REDIRECCIÓN (PATRÓN POST / REDIRECT / GET):
     *    Tras crear o borrar las cookies con setcookie(), redirigimos mediante volver()
     *    a 'preferencias.php' (enviando cabecera Location y código HTTP 303).
     *    Esto es fundamental por dos motivos:
     *      1. Evita que al recargar la página (F5) el navegador reenvíe el formulario POST.
     *      2. Las cookies recién enviadas con setcookie() no están disponibles en el array
     *         $_COOKIE de PHP hasta la siguiente petición HTTP; al redirigir, el navegador
     *         hace una nueva petición GET enviando ya las cookies actualizadas para que
     *         cabecera() aplique el nuevo tema visual y se muestre el nombre al instante.
     */
    volver('preferencias.php');
}


// ----------------------------------------------------------------------------
// 3. PREPARACIÓN DE LA VISTA Y CABECERA HTML
// ----------------------------------------------------------------------------
// Llama a la función cabecera() (de funciones.php) que imprime el <!doctype html>,
// <head>, el menú de navegación superior y abre la etiqueta <main>.
cabecera('Preferencias');


/*
 * RECUPERACIÓN DEL NOMBRE DEL VISITANTE DESDE LA COOKIE ($_COOKIE):
 * 1. $_COOKIE['visitante'] ?? '':
 *    Lee la cookie 'visitante' enviada por el navegador en la petición actual.
 *    Si el usuario aún no tiene esa cookie (o la borró), ?? asigna ''.
 * 2. Validación defensiva de la cookie:
 *    Como las cookies vienen del cliente y un usuario puede manipularlas manualmente
 *    en su navegador, no confiamos ciegamente en su contenido: comprobamos con
 *    is_string() que sea texto y la recortamos a un máximo de 40 caracteres con
 *    substr($visitante, 0, 40) antes de pintarla en el value del <input>.
 */
$visitante = $_COOKIE['visitante'] ?? '';
$visitante = is_string($visitante) ? substr($visitante, 0, 40) : '';

// Cierra el bloque PHP para comenzar a escribir código HTML directamente.
?>


<!-- ==========================================================================
     4. SECCIÓN INTRODUCTORIA
     ========================================================================== -->
<!-- Contenedor semántico con clase CSS 'intro' para el encabezado de la página -->
<section class="intro">

    <!-- Subtítulo superior estilizado mediante la clase 'etiqueta' -->
    <p class="etiqueta">COOKIES EN EL CLIENTE</p>

    <!-- Título principal de la página -->
    <h1>Preferencias del visitante</h1>

    <!-- Párrafo explicativo para el usuario -->
    <p>
        En esta página podrás configurar tus preferencias
        de navegación, como tu nombre y el tema visual.
    </p>

</section>


<!-- ==========================================================================
     5. PANEL DE FORMULARIOS DE PREFERENCIAS
     ========================================================================== -->
<!-- Contenedor visual con diseño de tarjeta ('panel') y ancho reducido ('estrecho') -->
<div class="panel estrecho">

    <!-- ----------------------------------------------------------------------
         FORMULARIO 1: GUARDAR PREFERENCIAS
         method="post" envía los datos ocultos en el cuerpo de la petición HTTP.
         Al no tener atributo 'action', se envía a esta misma página (preferencias.php).
         ---------------------------------------------------------------------- -->
    <form method="post">

        <?php
        // Bloque PHP de apertura/cierre previo a la inserción del token CSRF
        ?>

        <!-- Imprime el <input type="hidden" name="csrf_token" ...> para evitar ataques CSRF -->
        <?= campoCsrf() ?>

        <!-- Campo oculto que envía $_POST['accion'] = 'guardar' al servidor para que 
             el PHP superior sepa que se ha pulsado el formulario de guardado -->
        <input
            type="hidden"
            name="accion"
            value="guardar">

        <!-- Etiqueta vinculada al campo de texto mediante for="visitante" (mejora accesibilidad) -->
        <label for="visitante">
            Nombre (opcional)
        </label>

        <!-- Campo de texto para el nombre:
             - type="text": entrada de texto de una línea.
             - id="visitante": identificador que lo enlaza con el <label>.
             - name="visitante": clave con la que llegará a $_POST['visitante'].
             - maxlength="40": limita en el navegador la escritura a 40 caracteres máximo.
             - value="<?= e($visitante) ?>": imprime el nombre recuperado de $_COOKIE['visitante']
               para que el cuadro de texto recuerde y muestre el nombre guardado actualmente.
               Se pasa por la función e() (htmlspecialchars) para escapar caracteres especiales
               y evitar ataques XSS si alguien modificó la cookie con código HTML/JS.
             - placeholder="Ej.: Ana": texto de ayuda gris que aparece cuando el campo está vacío. -->
        <input
            type="text"
            id="visitante"
            name="visitante"
            maxlength="40"
            value="<?= e($visitante) ?>"
            placeholder="Ej.: Ana">

        <!-- Etiqueta vinculada al desplegable mediante for="tema" -->
        <label for="tema">
            Tema visual
        </label>

        <!-- Menú desplegable (<select>):
             - id="tema": lo vincula con su <label>.
             - name="tema": clave con la que viajará la opción elegida en $_POST['tema']. -->
        <select id="tema" name="tema">

            <!-- Primera opción: envía value="claro" al servidor. 
                 El atributo 'selected' hace que aparezca marcada por defecto. -->
            <option value="claro" selected>
                Claro
            </option>

            <!-- Segunda opción: envía value="oscuro" al servidor si se selecciona. -->
            <option value="oscuro">
                Oscuro
            </option>

        </select>

        <!-- Botón que desencadena el envío (submit) del primer formulario -->
        <button type="submit">
            Guardar preferencias
        </button>

    </form>

    <!-- ----------------------------------------------------------------------
         FORMULARIO 2: BORRAR COOKIES DE PREFERENCIAS
         Formulario independiente que también envía por POST a la misma página.
         La clase 'separado' añade margen superior mediante CSS.
         ---------------------------------------------------------------------- -->
    <form method="post" class="separado">

        <?php
        // Bloque PHP de apertura/cierre previo a la inserción del token CSRF
        ?>

        <!-- Token CSRF obligatorio para validar la petición POST -->
        <?= campoCsrf() ?>

        <!-- Campo oculto que envía $_POST['accion'] = 'borrar' para que el bloque
             PHP superior entre en el 'if ($accion === 'borrar')' y caduque las cookies -->
        <input
            type="hidden"
            name="accion"
            value="borrar">

        <!-- Botón de envío con estilo visual secundario (class="secundario") -->
        <button
            type="submit"
            class="secundario">
            Borrar cookies de preferencias
        </button>

    </form>

    <!-- Nota informativa al pie del panel -->
    <p class="nota">
        Las preferencias del visitante se almacenarán
        mediante cookies del navegador.
    </p>

</div>

<?php
// ----------------------------------------------------------------------------
// 6. CIERRE DEL DOCUMENTO HTML
// ----------------------------------------------------------------------------
// Llama a la función pie() (de funciones.php) que cierra <main>, imprime el 
// <footer> y cierra las etiquetas </body> y </html>.
pie();
