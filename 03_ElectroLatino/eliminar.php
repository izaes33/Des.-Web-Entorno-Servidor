<?php

/**============================================================================
 * TRAZABILIDAD: ELIMINACIÓN DE UN PRODUCTO DEL CARRITO
 * ============================================================================
 * Flujo general:
 * 1. Cargar la configuración e iniciar la sesión.
 * 2. Comprobar que la petición es de tipo POST (para acciones con efectos secundarios).
 * 3. Filtrar y validar el identificador del producto enviado.
 * 4. Control de errores con código HTTP 400 si el ID no es válido.
 * 5. Eliminar el producto de la sesión mediante unset().
 * 6. Redireccionar al usuario de vuelta a la vista del carrito.
 * ============================================================================ */


// PASO 1: Carga de dependencias y sesión
// require_once asegura incluir una sola vez la configuración global
// (donde normalmente se ejecuta session_start(), funciones auxiliares, etc.).
require_once __DIR__ . '/includes/config.php';

// PASO 2: Restricción del método HTTP
// Exige que la petición haya llegado por POST. Eliminar un elemento modifica el
// estado en el servidor; nunca debe ejecutarse por GET para evitar borrados accidentales.
exigirPostValido();

// PASO 3: Captura y validación del parámetro
// filter_input extrae 'producto_id' directamente desde $_POST y valida que sea un entero:
// - Devuelve el valor numérico (int) si es correcto.
// - Devuelve false si el valor recibido no es un número entero válido.
// - Devuelve null si la clave 'producto_id' ni siquiera existe en $_POST.
$id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
/* Parte por parte:
filter_input(...):  es una función incorporada de PHP. Lee un valor de una fuente de entrada, 
    como $_POST o $_GET, y opcionalmente lo valida o filtra.
INPUT_POST: indica que el dato se busca entre los enviados mediante un formulario con método POST.
'producto_id': nombre del campo del formulario que se quiere leer.
FILTER_VALIDATE_INT: comprueba que el dato sea un número entero válido. */

// PASO 4: Gestión de errores (Diálogo cliente-servidor)
// Si el ID es nulo o inválido, se interrumpe el flujo:
// - http_response_code(400): Código HTTP estándar 'Bad Request' (Petición incorrecta).
// - exit(...): Detiene el script inmediatamente enviando un mensaje claro al cliente.
// Por defecto reenviará al index.php, pero el código 400 indica que la petición no es correcta.
if ($id === false || $id === null) {
    http_response_code(400);
    exit('Identificador no válido');
}

// PASO 5: Eliminación en el almacén de sesión
// unset destruye la clave correspondiente al ID dentro del array asociativo del carrito.
// Si el producto no existía en el array, unset no genera ningún error.
unset($_SESSION['carrito'][$id]);

// PASO 6: Redirección (Patrón Post/Redirect/Get)
// Envía un encabezado de redirección (Location: carrito.php) para devolver al usuario
// a la interfaz del carrito y evitar que reenvíe el formulario de borrado si recarga la página.
volver('carrito.php');
