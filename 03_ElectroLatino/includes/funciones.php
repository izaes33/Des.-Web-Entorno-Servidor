<?php


/** Funciones reutilizables: HTML, formato, validación y navegación. */

declare(strict_types=1);


// Evita interpretar como HTML los datos que se imprimen en la página.
function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


// Muestra un importe con formato habitual en España.
function euros(float $cantidad): string
{
    return number_format($cantidad, 2, ',', '.') . ' €';
}


// Esta función devuelve el número de unidades del carrito que se almacena en la variable de sesión 
// carrito mediante array_sum() ($_SESSION['carrito'] es un array asociativo).
function unidadesCarrito(): int
{
    return array_sum($_SESSION['carrito']);
}


function totalCarrito(array $productos): float
{
    $total = 0.0;

    foreach ($_SESSION['carrito'] as $id => $cantidad) {
        // Si productos es una colección, y el id que está en el carrito existe en el array, 
        // se multiplica el precio del producto por la cantidad y se suma al total.
        if (isset($productos[$id])) {
            // Nos aseguramos de que la cantidad sea un entero para evitar errores de tipo y 
            // posibles vulnerabilidades.
            $total += $productos[$id]['precio'] * (int)$cantidad;
        }
    }
    return $total;
}


// Comprueba que el formulario se ha enviado por POST.
// La protección CSRF se añadirá en la última sesión.
function exigirPostValido(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { //Comprobamos si el método de la solicitud es POST, 
        // y es un código válido, manda una cabecera y lo permite, y si no lo es, manda un código de error y un mensaje de error.
        http_response_code(405); // El servidor manda un código de respuesta 405 (Método no permitido) si el método no es POST
        header('Allow: POST'); // Se envía un encabezado Allow con el valor POST para indicar que solo se permite el método POST
        exit('Método no permitido.'); // Se detiene la ejecución del script y se muestra un mensaje de error indicando que el 
        // método no está permitido
    }
}


/**
 * La función campoCsrf() genera un token CSRF para posteriormente contrastarlo con en servidor y evitar ataques de 
 * falsificación de solicitudes entre sitios (CSRF).
 * La función genera un campo de formulario oculto (<input type="hidden">) que contiene el token CSRF. Este campo se 
 * puede incluir en formularios HTML para enviar el token al servidor cuando se envía el formulario. 
 * El servidor puede verificar el token recibido con el token almacenado en la sesión del usuario para asegurarse de 
 * que la solicitud es legítima y no ha sido manipulada por un atacante.
 * La función utiliza la función htmlspecialchars() para escapar caracteres especiales en el token y evitar problemas 
 * de seguridad relacionados con la inyección de código HTML.
 */
function campoCsrf(): string
{
    // Escapa caracteres especiales en el token (en este caso el valor fijo "prueba" de ejemplo)
    // ENT_QUOTES convierte tanto comillas dobles como simples para evitar inyección de código HTML/XSS
    $token = htmlspecialchars("prueba", ENT_QUOTES, "UTF-8"); // En un caso real, se generaría un token único y seguro para cada sesión

    // Devuelve un campo <input> de tipo "hidden" (oculto para el usuario)
    // Al enviar el formulario, este valor viajará al servidor bajo el nombre "csrf_token" para su verificación
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}


// Patrón POST / REDIRECT / GET: evita repetir el envío al recargar.
// La función volver() redirige a la página indicada y termina la ejecución del script.
function volver(string $pagina): never
{ // never: indica que la función no devuelve ningún valor y que nunca debería terminar normalmente, 
    // sino que siempre debería terminar con una redirección o un error.
    // Debe llamarse antes de generar HTML o cualquier otra salida para que PHP pueda enviar la cabecera.
    header('Location: ' . $pagina, true, 303); // Header: página se utiliza para redirecciones (el resto es opcional)
    // 303 See Other: indica que la respuesta a la solicitud se encuentra en otra URI y fuerza a que para ir a la dirección se 
    // se use el método GET, POST o REDIRECT (aunque la redirección sea a la misma página).
    exit;
}


/** Determina qué tema visual ('claro' u 'oscuro') debe aplicarse a la página,
 * leyendo y validando la cookie 'tema' enviada por el navegador. */
function temaActual(): string
{
    /*   - ?? 'claro' (operador de fusión de null): Si la cookie aún no existe
     *   por ejemplo, si es la primera vez que entra el usuario o borró las cookies, se
     *   asigna directamente 'claro' sin lanzar ningún aviso de error en PHP. */
    $tema = $_COOKIE['tema'] ?? 'claro';

    /* Validación de seguridad por lista blanca (Whitelist)
     * Como las cookies vienen del cliente y cualquier usuario puede modificarlas
     * manualmente desde las herramientas del navegador (F12), hacemos dos comprobaciones:
     *
     * 1. !is_string($tema):
     *    Comprueba si el dato recibido NO es una cadena de texto (por si alguien
     *    manipuló la petición enviando un array en la cookie).
     *
     * 2. !in_array($tema, ['claro', 'oscuro'], true):
     *    Comprueba si el valor de $tema NO está dentro de la lista de valores
     *    permitidos ('claro' u 'oscuro'). El tercer parámetro "true" exige una
     *    comparación estricta (===) de valor y tipo. */
    if (
        !is_string($tema) || !in_array($tema, ['claro', 'oscuro'], true)
    ) {
        return 'claro';
    }
    /* Si superó el filtro anterior, tenemos la certeza de que $tema vale
     * exactamente 'claro' u 'oscuro', y lo devolvemos para inyectarlo en
     * la etiqueta <html lang="es" data-tema="..."> de cabecera(). */
    return $tema;
}


function cabecera(string $titulo): void
{
    // Esta función empieza a enviar HTML; no la llames en controladores que luego deban redirigir.
    // Con cookies recuperaremos aquí el nombre del visitante.
    $tema = temaActual();
?>
    <!doctype html>
    <html lang="es" data-tema="<?= e($tema) ?>">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($titulo) ?> | MiniTienda PHP</title>
        <link rel="stylesheet" href="css/estilos.css">
    </head>

    <body>
        <header class="cabecera">
            <div class="contenedor barra">
                <a class="marca" href="index.php">MiniTienda PHP</a>
                <nav aria-label="Navegación principal">
                    <a href="index.php">Catálogo</a>
                    <a href="carrito.php">Carrito (<?= unidadesCarrito() ?>)</a>
                    <a href="preferencias.php">Preferencias</a>
                    <a href="sesion.php">Sesión</a>
                </nav>
            </div>
        </header>
        <main class="contenedor">
        <?php
    }


    function pie(): void
    {
        ?>
        </main>
        <footer class="pie">Proyecto didáctico de DWES · Unidad 4 · Docente: José Manuel Villar Ferradal</footer>
    </body>

    </html>
<?php
    }
