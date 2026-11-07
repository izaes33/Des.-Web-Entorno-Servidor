<?php

/**
 * ============================================================================
 * ARCHIVO: cerrar.php
 * PROYECTO: MiniTienda PHP
 * ============================================================================
 * OBJETIVO:
 * Cerrar completamente la sesión activa y borrar el carrito realizando una
 * limpieza en tres niveles:
 *   1. Memoria de PHP ($_SESSION).
 *   2. Cookie identificadora en el navegador del cliente (setcookie).
 *   3. Datos persistentes de la sesión en el servidor (session_destroy).
 * ============================================================================
 */


// PASO 1: Cargar la configuración y recuperar la sesión actual
// ----------------------------------------------------------------------------
// Es necesario ejecutar session_start() (dentro de config.php) para que PHP 
// sepa qué sesión concreta debe eliminar.
require_once __DIR__ . '/includes/config.php';


// PASO 2: Seguridad HTTP (forzar método POST)
// ----------------------------------------------------------------------------
// Impide que se pueda cerrar la sesión mediante un simple enlace GET o
// escribiendo la URL 'cerrar.php' en el navegador.
exigirPostValido();


// PASO 3: Vaciar los datos de sesión en memoria (Nivel 1)
// ----------------------------------------------------------------------------
// Sobrescribe la superglobal $_SESSION con un array vacío [].
// Así se eliminan al instante el carrito ($_SESSION['carrito']) y el token CSRF
// de la memoria durante la ejecución actual del script.
$_SESSION = [];


// PASO 4: Eliminar la cookie de sesión en el navegador (Nivel 2)
// ----------------------------------------------------------------------------
// ini_get('session.use_cookies') consulta la configuración de PHP para
// confirmar si las sesiones se están gestionando mediante cookies.
if (ini_get('session.use_cookies')) {

    // Recupera en el array $params todos los parámetros con los que se configuró
    // originalmente la cookie de sesión en config.php (path, domain, secure,
    // httponly y samesite). 
    // Para borrar una cookie en el navegador es obligatorio
    // especificar exactamente el mismo 'path' y 'domain' con los que fue creada.
    $params = session_get_cookie_params();

    // Envía una cabecera HTTP ordenando al navegador caducar la cookie de sesión:
    // - session_name(): Obtiene el nombre de la cookie (por defecto 'PHPSESSID').
    // - '': Vacía el identificador de sesión que contenía la cookie.
    // - 'expires' => time() - 3600: Fija la fecha de expiración 1 hora en el pasado
    //   (3600 segundos antes del momento actual) para que el navegador la borre ya.
    // - El resto de claves copian los atributos exactos de $params.
    setcookie(
        session_name(),
        '',
        [
            'expires' => time() - 3600,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'],
        ]
    );
}


// PASO 5: Destruir la sesión en el servidor (Nivel 3)
// ----------------------------------------------------------------------------
// session_destroy() elimina en el servidor el archivo o registro asociado al
// ID de sesión actual, liberando el almacenamiento.
session_destroy();


// PASO 6: Redirección Post / Redirect / Get
// ----------------------------------------------------------------------------
// Redirige al catálogo (index.php) mediante GET (código 303) y detiene el script.
volver('index.php');
