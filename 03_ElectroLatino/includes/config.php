<?php

/* ============================================================
 * ARCHIVO: includes/config.php
 * PROYECTO: MiniTienda PHP
 * ============================================================
 * OBJETIVO: Este archivo contiene la configuración general
 *  que necesitan las distintas páginas de la tienda para
 *      trabajar con sesiones PHP.
 *
 * En nuestro proyecto utilizamos sesiones para:
 * 1. Mantener los productos del carrito de compra.
 * 2. Conservar las cantidades seleccionadas.
 * 3. Almacenar un token de seguridad CSRF.
 * 4. Identificar una sesión entre distintas
 *       peticiones HTTP del mismo navegador.
 * 5. Configurar determinadas propiedades de
 *       seguridad de la cookie de sesión.
 *
 * DIFERENCIA IMPORTANTE:
 * La cookie de sesión contiene normalmente un identificador,
 *      por ejemplo: PHPSESSID = abc123
 * Los datos del carrito se almacenan en el servidor y se
 *      asocian a ese identificador.
 *
 * EJEMPLO CONCEPTUAL:
 * NAVEGADOR: PHPSESSID = abc123
 * SERVIDOR: Sesión abc123:
 *       carrito = [
 *           1 => 2,
 *           3 => 1
 *       ]
 * csrf_token = TOKEN_ALEATORIO
 * Cuando el navegador vuelve a solicitar una página,
 *       envía la cookie de sesión.
 * PHP utiliza ese identificador para recuperar
 *       los datos correspondientes en el servidor.
 *
 * IMPORTANTE:
 * Este archivo debe cargarse antes de generar contenido
 *  HTML, porque session_start() y la configuración
 *      de cookies necesitan poder enviar cabeceras HTTP.  */


/* ============================================================
 * 1. ACTIVAR LA COMPROBACIÓN ESTRICTA DE TIPOS
 * ============================================================
 * declare(strict_types=1) es una construcción de PHP que
 * permite establecer determinadas directivas para el archivo 
 * actual. Esta sentencia tiene que ir al principio del archivo 
 * y su misión es activar el modo estricto de tipos, es decir,  
 * hacer que PHP sea más estricto con los tipos de datos que se 
 * pasana las funciones y los métodos desde este archivo, lo  
 * que ayuda a detectar errores de tipo en tiempo de ejecución 
 * y mejora la seguridad y la robustez del código y también 
 * puede mejorar el rendimiento al permitir que PHP optimice 
 * mejor el código. Además también afecta a la comprobación
 * de los tipos de retorno de las funciones definidas aquí.
 *
 * EJEMPLO:
 * Supongamos que tenemos:
 *       function sumar(int $a, int $b): int {
 *           return $a + $b;
 *       }
 * Si llamamos a: sumar("5", "3"); estamos pasando cadenas de 
 * texto en lugar de números enteros.
 * Con strict_types=1, una llamada como esa produce
 * un TypeError.
 * Sin strict_types=1, PHP puede convertir automáticamente
 * cadenas numéricas a enteros.
 *
 * IMPORTANTE:
 * El modo estricto no valida automáticamente los datos
 * recibidos mediante formularios.
 * Los valores de $_POST, $_GET y $_COOKIE deben seguir
 * validándose expresamente.
 *
 * Esta declaración se aplica al archivo actual y no activa
 * automáticamente el modo estricto en todos los archivos.
 *
 * Debe situarse al principio del archivo PHP,
 * antes de las instrucciones ejecutables.  */
declare(strict_types=1);


/* ============================================================
 * 2. ACTIVAR EL MODO ESTRICTO DE IDENTIFICADORES
 * ============================================================
 * ini_set() permite modificar en tiempo de ejecución las 
 * opciones de configuración de PHP (definidas originalmente en 
 * el archivo php.ini). 
 * Su estructura recibe siempre dos argumentos: la directiva a 
 * modificar ('session.use_strict_mode') y el valor ('1' para 
 * activar, '0' para desactivar). En las directivas booleanas 
 * de PHP, '1' suele representar activado y '0' suele 
 * representar desactivado.
 *
 * ¿QUÉ HACE session.use_strict_mode?
 * No viene activa por defecto y sirve para prevenir ataques de 
 * fijación de sesión (Session Fixation). Con esta opción, al 
 * iniciar sesión se asigna un ID válido que el servidor 
 * comprueba en cada petición, rechazando cualquier ID que no 
 * haya sido inicializado previamente por el gestor de sesiones 
 * e impidiendo que sea modificado manualmente.
 *
 * EJEMPLO:
 * Un atacante intenta aprovechar una sesión abierta o un token 
 * en caché de un usuario con pocos permisos para cambiar el ID 
 * de sesión (o probar distintos patrones de ID) por el de un 
 * administrador y obtener acceso privilegiado:
 *       PHPSESSID = id_de_administrador
 * Con el modo estricto activado, PHP bloquea el cambio, 
 * rechaza el identificador no inicializado por el sistema y 
 * puede generar uno nuevo.
 *
 * IMPORTANTE:
 * Es fundamental activarlo siempre en el archivo config.php al 
 * trabajar con sesiones. Esta protección depende del gestor de 
 * sesiones y no sustituye otras medidas de seguridad, como 
 * regenerar el ID tras la autenticación.  */
ini_set('session.use_strict_mode', '1');


/* ============================================================
 * 3. UTILIZAR SOLO COOKIES PARA EL ID DE SESIÓN
 * ============================================================
 * Volvemos a utilizar ini_set(). En este caso modificamos la
 * directiva: session.use_only_cookies. El valor '1' activa
 * esta opción.
 *
 * ¿QUÉ SIGNIFICA?
 * Con ini_set('session.use_only_cookies', '1') indicamos al 
 * servidor que gestione y valide el identificador de sesión 
 * única y exclusivamente a través de cookies. De esta forma 
 * evitamos que PHP acepte el identificador de sesión mediante 
 * mecanismos alternativos, como parámetros en la URL, el 
 * localStorage u otras partes de la caché.
 *
 * EJEMPLO DE LO QUE QUEREMOS EVITAR:
 *       productos.php?PHPSESSID=abc123
 * Si esta opción no está activa, alguien podría intentar 
 * enviar un ID de sesión distinto como parámetro en la URL, 
 * quedando expuesto además en el historial del navegador, 
 * registros del servidor, sistemas de monitorización o 
 * enlaces compartidos.
 *
 * ¿POR QUÉ ES IMPORTANTE?
 * Cerrar el paso a otros medios y obligar al uso de cookies 
 * mejora la seguridad del servidor de forma sencilla, ya que 
 * las cookies permiten aplicar medidas adicionales como la 
 * encriptación para dificultar ataques. Por otro lado,
 * si el identificador de sesión apareciera en una URL,
 * aumentaría el riesgo de exposición accidental. Con esta
 * configuración indicamos que el identificador debe
 * transportarse mediante cookies.
 *
 * IMPORTANTE:
 * La cookie solo transporta el identificador; los datos de la 
 * sesión (como el carrito) se mantienen guardados en el 
 * servidor.  */
ini_set('session.use_only_cookies', '1');


/* ============================================================
 * 4. DETECTAR SI LA CONEXIÓN UTILIZA HTTPS
 * ============================================================
 * HTTPS es la versión segura de HTTP. Utiliza TLS para proteger
 * la comunicación entre el navegador y el servidor. Queremos
 * saber si la petición actual se está realizando mediante
 * HTTPS. Guardamos el resultado en la variable $https. Esta
 * variable contendrá un booleano: true o false.
 *
 * $_SERVER es una variable superglobal que contiene información
 * sobre la petición y el entorno del servidor. $_SERVER['HTTPS']
 * es una entrada que determinados servidores utilizan para
 * indicar que la conexión está utilizando HTTPS. Vamos a
 * estudiar la expresión.
 *
 * PRIMERA PARTE: !empty($_SERVER['HTTPS'])
 * empty() comprueba si una variable o expresión tiene un valor
 * considerado vacío. Entre otros:
 * - null
 * - false
 * - ''
 * - '0'
 * El operador ! significa negación lógica. Por tanto:
 * !empty(...) significa: "El valor no está vacío". Además,
 * empty() permite comprobar una entrada inexistente de un array
 * sin generar el aviso habitual por acceso a una clave no
 * definida.
 *
 * SEGUNDA PARTE: $_SERVER['HTTPS'] !== 'off'
 * El operador !== significa "estrictamente diferente". Compara
 * el valor y el tipo. En determinadas configuraciones, el
 * servidor puede indicar: HTTPS = 'on' cuando la conexión es
 * segura. También puede indicar: HTTPS = 'off' cuando no lo es.
 * Queremos descartar expresamente el valor 'off'.
 *
 * OPERADOR &&:
 * && significa AND lógico. Ambas condiciones deben ser
 * verdaderas. PHP evalúa las condiciones de izquierda a derecha
 * y aplica cortocircuito. Si la primera condición es falsa, no
 * necesita evaluar la segunda. Esto evita acceder a
 * $_SERVER['HTTPS'] en la segunda condición cuando no existe o
 * está vacío.
 *
 * RESULTADOS HABITUALES:
 *       HTTPS = 'on'       -> $https = true
 *       HTTPS = 'off'      -> $https = false
 *       HTTPS no definido  -> $https = false
 *
 * UTILIDAD:
 * Posteriormente utilizaremos $https para configurar el
 * atributo Secure de la cookie de sesión.
 *
 * IMPORTANTE:
 * Esta comprobación es adecuada para un entorno sencillo. En
 * producción, si existe un proxy inverso o un balanceador de
 * carga que termina TLS, puede ser necesaria una configuración
 * adicional y de confianza en el servidor.
 *
 * No debemos confiar sin más en cabeceras proporcionadas por
 * el cliente.  */
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';


/* ============================================================
 * 5. CONFIGURAR LA COOKIE DE SESIÓN
 * session_set_cookie_params(['lifetime' => 0,'path' => '/', 'secure' => $https, 'samesite' => 'Lax',]);
 * ============================================================
 * session_set_cookie_params() es una función integrada de PHP.
 * Permite establecer los parámetros que se utilizarán para la
 * cookie identificadora de sesión.
 *
 * En este caso utilizamos un array asociativo para indicar las
 * distintas opciones. Un array asociativo almacena parejas:
 * clave => valor. Por ejemplo:
 *       'httponly' => true
 * La clave identifica la opción y el valor indica cómo queremos
 * configurarla. Vamos a estudiar cada una.
 *
 * IMPORTANTE:
 * Debe ejecutarse ANTES de session_start().  */
session_set_cookie_params([

    /* ========================================================
     * 5.1. LIFETIME
     * ========================================================
     * Indica la duración configurada para la cookie de sesión.
     * El valor 0 indica que queremos una cookie de sesión del
     * navegador. Es decir, no establecemos una fecha de
     * caducidad persistente mediante este parámetro sino que 
     * la cookie se elimina al cerrar el navegador.
     * Normalmente estas cookies se eliminan al finalizar la
     * sesión del navegador.
     *
     * IMPORTANTE:
     * Algunos navegadores pueden restaurar cookies de sesión
     * cuando recuperan sesiones anteriores. Por tanto,
     * lifetime=0 no garantiza que la cookie desaparezca
     * inmediatamente al cerrar una ventana.
     *
     * Tampoco determina por sí solo cuánto tiempo se conservan
     * los datos de sesión en el servidor. Esa duración depende
     * de otros factores, como la configuración del
     * almacenamiento y la recogida de sesiones caducadas.  */
    'lifetime' => 0,


    /* ========================================================
     * 5.2. PATH
     * ========================================================
     * Indica la ruta del sitio web para la que se aplica la
     * cookie. El valor '/' indica que la cookie se aplica a
     * todo el sitio dentro de su dominio.
     *
     * ¿POR QUÉ NOS INTERESA?
     * Porque todas las páginas de MiniTienda necesitan poder
     * recuperar la misma sesión.
     *
     * EJEMPLO:
     * Si tenemos: http://localhost:8000/ la cookie podrá
     * enviarse en peticiones a rutas como:
     *       /index.php
     *       /carrito.php
     *       /preferencias.php
     *       /sesion.php
     *
     * IMPORTANTE:
     * El atributo Path delimita el ámbito de envío de la
     * cookie. No constituye por sí solo una frontera de
     * seguridad.  */
    'path' => '/',


    /* ========================================================
     * 5.3. SECURE
     * ========================================================
     * Indica si la cookie debe enviarse únicamente mediante
     * conexiones seguras. Es un valor booleano: true o false.
     * Utilizamos la variable $https calculada anteriormente.
     *
     * EJEMPLO:
     * Si la conexión utiliza HTTPS ($https = true), la cookie
     * se configura con Secure. Si estamos trabajando localmente
     * mediante HTTP ($https = false), la cookie no lleva ese
     * atributo.
     *
     * ¿POR QUÉ ES IMPORTANTE?
     * Porque ayuda a evitar que el identificador de sesión se
     * envíe por conexiones HTTP no cifradas.
     *
     * IMPORTANTE:
     * En producción debemos utilizar HTTPS y configurar
     * correctamente Secure. Los navegadores pueden aplicar
     * excepciones específicas para localhost.  */
    'secure' => $https,


    /* ========================================================
     * 5.4. HTTPONLY
     * ========================================================
     * Es un atributo de seguridad de las cookies. Al establecer
     * true indicamos que JavaScript no debe poder acceder a
     * esta cookie mediante document.cookie.
     *
     * EJEMPLO:
     * Supongamos que un script ejecuta:
     *       document.cookie
     * Las cookies marcadas como HttpOnly no aparecerán en ese
     * resultado.
     *
     * ¿POR QUÉ ES IMPORTANTE?
     * Porque el identificador de sesión es un dato sensible.
     * HttpOnly dificulta que determinados ataques XSS puedan
     * leerlo directamente. (XSS significa: Cross-Site Scripting.
     * Es una vulnerabilidad que puede permitir ejecutar
     * JavaScript no deseado dentro del contexto de una página).
     *
     * IMPORTANTE:
     * HttpOnly no elimina por completo los riesgos relacionados
     * con XSS. Tampoco impide que el navegador envíe
     * automáticamente la cookie en peticiones HTTP
     * correspondientes. La cookie seguirá siendo visible en las
     * herramientas de desarrollo del navegador.  */
    'httponly' => true,


    /* ========================================================
     * 5.5. SAMESITE
     * ========================================================
     * SameSite es un atributo de las cookies que controla
     * determinados contextos en los que pueden enviarse en
     * peticiones entre sitios. Los valores habituales son:
     * Strict, Lax y None.
     *
     * SAMESITE=STRICT:
     * Es la opción más restrictiva de las tres. Limita el envío
     * de la cookie en contextos entre sitios.
     *
     * SAMESITE=LAX:
     * Es la opción que utilizamos. Restringe el envío de
     * cookies en determinados contextos entre sitios, pero
     * permite ciertos tipos de navegación, como algunas
     * navegaciones de nivel superior.
     *
     * SAMESITE=NONE:
     * Permite el envío de cookies en contextos entre sitios,
     * sujeto a las políticas del navegador. Requiere el
     * atributo Secure en los navegadores modernos.
     *
     * ¿POR QUÉ UTILIZAMOS LAX?
     * Porque ofrece un equilibrio entre compatibilidad y
     * protección. Ayuda a reducir determinados riesgos
     * relacionados con CSRF (Cross-Site Request Forgery).
     *
     * IMPORTANTE:
     * SameSite no sustituye los tokens CSRF que utilizamos en
     * los formularios del proyecto.  */
    'samesite' => 'Lax',

]);


/* ============================================================
 * 6. INICIAR O RECUPERAR LA SESIÓN
 * ============================================================
 * session_start() es una función integrada de PHP. Su finalidad
 * es iniciar una sesión o recuperar una sesión existente.
 * Cuando se ejecuta, PHP comprueba si la petición incluye un
 * identificador de sesión válido.
 *
 * CASO 1:
 * El navegador no tiene una cookie de sesión válida. PHP puede
 * generar un nuevo identificador e iniciar una nueva sesión 
 * enviando la cookie al navegador.
 *
 * CASO 2:
 * El navegador envía una cookie con un identificador válido.
 * PHP intenta recuperar los datos asociados a ese
 * identificador.
 *
 * EJEMPLO:
 * El navegador envía: PHPSESSID = abc123 junto a la cookie
 * de sesión que tenía almacenada y PHP recupera los datos 
 * asociados a la sesión. Por ejemplo:
 *       $_SESSION['carrito'] = [
 *           1 => 2,
 *           3 => 1
 *       ];
 * A partir de este momento podemos utilizar la variable
 * superglobal (Array de sesión) $_SESSION para almacenar y 
 * recuperar datos de la sesión como el carrito de compra, 
 * el token CSRF, preferencias del usuario, etc.
 *
 * IMPORTANTE:
 * session_start() debe ejecutarse antes de generar contenido
 * HTML si PHP necesita enviar cabeceras relacionadas con la
 * sesión. Por eso lo colocamos en config.php, antes de llamar a
 * funciones que generan contenido HTML. También es importante
 * observar que hemos configurado primero los parámetros de la
 * cookie.
 *
 * ORDEN CORRECTO:
 * 1. Configurar las directivas de sesión.
 * 2. Configurar la cookie de sesión.
 * 3. Ejecutar session_start().
 * 4. Trabajar con $_SESSION.  */
session_start();


/* ============================================================
 * 7. COMPROBAR SI EXISTE EL CARRITO 
 * ===========================================================
        if ( !isset($_SESSION['carrito']) || !is_array($_SESSION['carrito']) ) {
                $_SESSION['carrito'] = [];
        }
 * ============================================================
 * Queremos garantizar que $_SESSION['carrito'] exista y
 * contenga un array. Esto es necesario porque posteriormente
 * utilizaremos instrucciones como:
 *       $_SESSION['carrito'][$id] = $cantidad;
 *       foreach ($_SESSION['carrito'] as ...)
 *       unset($_SESSION['carrito'][$id]);
 * Todas estas operaciones esperan que el carrito tenga una
 * estructura compatible con un array. Para comprobarlo
 * utilizamos if. La condición tiene dos partes:
 * !isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])
 * Vamos a estudiar ambas.  */
if (
    /* ========================================================
     * PRIMERA CONDICIÓN
     * ========================================================
     * isset() comprueba si una variable o entrada de un array
     * está definida y su valor no es null.
     *
     * EJEMPLO:
     * Supongamos que tenemos:
     *       $_SESSION = [];
     * En ese caso, isset($_SESSION['carrito']) devuelve false.
     * El operador ! niega el resultado. Por tanto,
     * !isset($_SESSION['carrito']) devuelve true cuando no
     * existe una entrada válida llamada carrito.  */
    !isset($_SESSION['carrito'])

    ||

    /* ========================================================
     * SEGUNDA CONDICIÓN Y OPERADOR ||
     * ========================================================
     * is_array() comprueba si una variable contiene un array.
     *
     * EJEMPLO:
     * Si tenemos: $_SESSION['carrito'] = []; is_array(...)
     * devuelve true. Pero si tenemos:
     *       $_SESSION['carrito'] = "hola";
     * is_array(...) devuelve false. Al utilizar !is_array(),
     * comprobamos si el valor NO es un array.
     *
     * OPERADOR ||:
     * Significa OR lógico. Basta con que una de las condiciones
     * sea verdadera para entrar en el if. Además, PHP utiliza
     * evaluación de cortocircuito: si la primera condición es
     * verdadera, no necesita evaluar la segunda. Esto resulta
     * útil cuando la clave carrito todavía no existe.  */
    !is_array($_SESSION['carrito'])

) {

    /* ========================================================
     * 8. INICIALIZAR EL CARRITO VACÍO
     * ========================================================
     * Si el carrito no existe o no contiene un array, lo
     * inicializamos. Utilizamos: $_SESSION['carrito'] = [];
     * El operador = realiza una asignación y los corchetes []
     * representan un array vacío.
     *
     * EJEMPLO:
     * ANTES:
     *       $_SESSION = [];
     * DESPUÉS:
     *       $_SESSION = [
     *           'carrito' => []
     *       ];
     *
     * ¿QUÉ OCURRE SI YA EXISTE?
     * Supongamos que tenemos:
     *       $_SESSION['carrito'] = [
     *           1 => 2,
     *           3 => 1
     *       ];
     * En ese caso, isset(...) devuelve true e is_array(...)
     * devuelve true. Por tanto, !isset(...) devuelve false y
     * !is_array(...) devuelve false. La condición completa es
     * falsa y NO entramos en el if.
     *
     * IMPORTANTE:
     * Esto es fundamental. Si inicializásemos el carrito en
     * cada petición sin comprobarlo, perderíamos los productos
     * anteriores. Gracias a esta condición, el carrito solo se
     * inicializa cuando es necesario.  */
    $_SESSION['carrito'] = [];

}

//minuto 24
/* ============================================================
 * 9. COMPROBAR SI EXISTE EL TOKEN CSRF
 * ============================================================
 * if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
 * ============================================================
 * CSRF significa: Cross-Site Request Forgery o "Falsificación 
 * de solicitudes entre sitios". Es un ataque en el que un sitio 
 * externo intenta provocar acciones en nombre de un usuario 
 * identicado sin su consentimiento aprovechando su sesión. 
 * (Como el navegador puede enviar automáticamente las cookies de 
 * sesión, de manera que un atacante podría intentar realizar 
 * acciones no deseadas en un sitio web en el que el usuario está 
 * autenticado, para evitarlo el servidor debe verificar que las 
 * solicitudes provienen de fuentes legítimas).
 * 
 * Tenemos operaciones como:
 *      - Añadir productos.
 *      - Actualizar cantidades.
 *      - Eliminar productos.
 *      - Vaciar el carrito.
 *      - Guardar preferencias.
 *      - Cerrar la sesión.
 * Estas operaciones se realizan mediante formularios POST y 
 * para protegerlas utilizamos un token CSRf, el cual es un valor 
 * aleatorio muy difícil de adivinar y asociado a la sesión actual. 
 * Los formularios envían ese token al servidor y posteriormente, 
 * exigirPostValido() comprueba que el token recibido coincide 
 * con el almacenado en la sesión.
 *
 * FUNCIÓN empty():
 * empty() comprueba si una variable o expresión tiene un valor
 * vacío. Por ejemplo, si no existe $_SESSION['csrf_token'],
 * empty() devuelve true. También devuelve true si contiene una
 * cadena vacía.
 *
 * IMPORTANTE:
 * Utilizamos esta comprobación porque queremos crear el token
 * únicamente cuando sea necesario. Si ya existe un token,
 * debemos conservarlo. Si generásemos un token nuevo en cada
 * petición, los formularios abiertos anteriormente podrían
 * dejar de ser válidos inesperadamente.  */
if (empty($_SESSION['csrf_token'])) {

    /* ========================================================
     * 10. GENERAR UN TOKEN CSRF ALEATORIO
     * ========================================================
     * Guardamos el token en $_SESSION['csrf_token']. Para
     * generarlo combinamos dos funciones de PHP: random_bytes()
     * y bin2hex().
     *
     * PRIMERA FUNCIÓN: random_bytes(32)
     * random_bytes() genera bytes aleatorios criptográficamente
     * seguros. El argumento 32 indica que queremos generar 32
     * bytes. Un byte contiene 8 bits, por tanto: 32 bytes * 8
     * bits = 256 bits. Los bytes generados pueden contener
     * valores que no resultan adecuados para representarlos
     * directamente como texto visible. Por eso utilizamos
     * bin2hex().
     *
     * SEGUNDA FUNCIÓN: bin2hex()
     * bin2hex() convierte una secuencia de bytes en una
     * representación hexadecimal. Cada byte se representa
     * mediante dos caracteres hexadecimales. Por tanto, 32
     * bytes producen 64 caracteres hexadecimales. Los
     * caracteres posibles son: 0 1 2 3 4 5 6 7 8 9 y a b c d e f.
     *
     * EJEMPLO ILUSTRATIVO:
     *       9f2a7c18e4b6d390...
     * En una ejecución real, el token será más largo y tendrá
     * 64 caracteres.
     *
     * ¿POR QUÉ GUARDARLO EN $_SESSION?
     * Porque necesitamos conservarlo entre distintas peticiones
     * HTTP. Cuando mostramos un formulario, incluimos ese token
     * mediante la función campoCsrf(). Cuando el formulario se
     * envía, exigirPostValido() compara el token recibido con
     * este valor.
     *
     * IMPORTANTE:
     * El token debe ser impredecible. No debemos utilizar
     * valores sencillos como fechas, nombres de usuario o
     * números consecutivos. random_bytes() es adecuada para
     * generar este tipo de valores.  */
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}


/* ============================================================
 * 11. CARGAR LAS FUNCIONES REUTILIZABLES
 * ============================================================
 * Finalmente cargamos el archivo: includes/funciones.php. En
 * ese archivo definimos funciones que utilizaremos en distintas
 * páginas de MiniTienda.
 *
 * EJEMPLO:
 *       e()               -> Escapa texto para mostrarlo de forma
 *                            segura dentro de HTML.
 *       euros()           -> Formatea importes monetarios.
 *       unidadesCarrito() -> Calcula el número total de unidades
 *                            almacenadas en el carrito.
 *       totalCarrito()    -> Calcula el importe total de los
 *                            productos seleccionados.
 *       campoCsrf()       -> Genera el campo oculto del formulario
 *                            que contiene el token CSRF.
 *       exigirPostValido()-> Comprueba que una petición utiliza
 *                            POST y contiene un token CSRF válido.
 *       volver()          -> Realiza una redirección HTTP y
 *                            termina la ejecución del script.
 *       cabecera()        -> Genera la estructura HTML inicial.
 *       pie()             -> Genera el cierre del documento HTML.
 *
 * ¿POR QUÉ UTILIZAMOS __DIR__?
 * Este archivo se encuentra dentro de la carpeta includes. Por
 * tanto, __DIR__ representa la ruta absoluta de esa carpeta. Al
 * concatenar '/funciones.php' obtenemos la ruta completa al
 * archivo de funciones.
 *
 * ¿POR QUÉ require_once?
 * Porque necesitamos que las funciones estén disponibles en
 * todas las páginas que cargan config.php. Además, require_once
 * evita incluir repetidamente el mismo archivo. Si
 * funciones.php no existe o no puede cargarse, PHP produce un
 * error.
 *
 * IMPORTANTE:
 * Este require_once también explica por qué las páginas pueden
 * utilizar funciones como campoCsrf() sin incluir funciones.php
 * directamente. Basta con cargar config.php, siempre que este
 * archivo incluya correctamente funciones.php.  */
require_once __DIR__ . '/funciones.php';