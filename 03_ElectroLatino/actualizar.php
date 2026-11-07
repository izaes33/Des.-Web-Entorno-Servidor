<?php

/**
 * ============================================================================
 * CONTROLADOR: ACTUALIZACIÓN / ELIMINACIÓN DE PRODUCTOS EN EL CARRITO
 * ============================================================================
 * Flujo de ejecución del script:
 * 1. Inicialización de dependencias y catálogo de productos.
 * 2. Verificación de seguridad HTTP (forzar método POST).
 * 3. Filtrado y tipado de parámetros de entrada ($id y $cantidad).
 * 4. Validación contextual (control de fallos y comunicación HTTP 400).
 * 5. Mutación del carrito en sesión (convención de simplificación / eliminación).
 * 6. Redirección Post/Redirect/Get para evitar reenvíos accidentales.
 * ============================================================================
 */

// ----------------------------------------------------------------------------
// PASO 1: Carga de dependencias y configuración global
// ----------------------------------------------------------------------------
// config.php inicia la sesión (session_start()), funciones auxiliares y utilidades[cite: 3].
require_once __DIR__ . '/includes/config.php';
// productos.php carga el array del catálogo de productos ($productos) en memoria[cite: 3].
require_once __DIR__ . '/includes/productos.php';

// ----------------------------------------------------------------------------
// PASO 2: Seguridad y restricción de método HTTP
// ----------------------------------------------------------------------------
// Esta acción modifica el estado del servidor (el contenido del carrito).
// Por seguridad y diseño REST/HTTP, debe ejecutarse exclusivamente por POST,
// bloqueando peticiones GET para evitar alteraciones accidentales desde enlaces o marcadores.
exigirPostValido();

// ----------------------------------------------------------------------------
// PASO 3: Captura y tipado seguro de entradas
// ----------------------------------------------------------------------------
// filter_input lee directamente del array $_POST y valida el tipo de dato:
// - Devuelve un int si el valor es un entero válido[cite: 3].
// - Devuelve false si el formato no es válido (por ejemplo, texto 'abc').
// - Devuelve null si la clave no fue enviada en la petición HTTP.
$id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);
$cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

// ----------------------------------------------------------------------------
// PASO 4: Validación contextual (Diálogo cliente-servidor)
// ----------------------------------------------------------------------------
// Se aplica una política de fallo rápido (fail-fast) antes de alterar datos.
// Si el cliente envía datos corruptos, manipulados o incoherentes:
//   - $id === false || $id === null : El ID no es un número entero o no se envió.
//   - !isset($productos[$id])        : El producto no existe en el catálogo ($productos).
//   - $cantidad === false || $cantidad === null : La cantidad no es un número entero o falta.
//   - $cantidad < 0                 : No se admiten cantidades negativas.
if (
    $id === false || $id === null || !isset($productos[$id])
    || $cantidad === false || $cantidad === null || $cantidad < 0
) {
    // Código estándar 400 (Bad Request): notifica al cliente que la petición es errónea
    http_response_code(400);
    // Interrumpe la ejecución inmediata y muestra el mensaje descriptivo
    exit('Producto o cantidad no válidos');
}

// ----------------------------------------------------------------------------
// PASO 5: Mutación de la sesión (Estructura if / else por convención)
// ----------------------------------------------------------------------------
// Siguiendo la buena práctica explicada por el profesor:
// - En el 'if' se evalúa la salida más simple e inmediata: si la cantidad es 0,
//   el usuario quiere retirar el producto, por lo que se destruye la clave con unset().
// - En el 'else' se gestiona la asignación estándar: actualizar las unidades del producto.
if ($cantidad === 0) {
    unset($_SESSION['carrito'][$id]);
} else {
    $_SESSION['carrito'][$id] = $cantidad;
}

// ----------------------------------------------------------------------------
// PASO 6: Redirección limpia (Patrón Post/Redirect/Get)
// ----------------------------------------------------------------------------
// Devuelve una cabecera de redirección a 'carrito.php'.
// Así el usuario visualiza el carrito actualizado y se evita que al recargar (F5)
// el navegador vuelva a enviar la petición POST
volver('carrito.php');
