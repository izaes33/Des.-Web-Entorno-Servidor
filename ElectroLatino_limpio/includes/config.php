<?php
declare(strict_types=1);


ini_set('session.use_strict_mode', '1');


ini_set('session.use_only_cookies', '1');


$https = !empty($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] !== 'off';


session_set_cookie_params([


    'lifetime' => 0,


    'path' => '/',


    'secure' => $https,


    'httponly' => true,


    'samesite' => 'Lax',

]);


session_start();


if (


    !isset($_SESSION['carrito'])

    ||


    !is_array($_SESSION['carrito'])

) {


    $_SESSION['carrito'] = [];

}


if (empty($_SESSION['csrf_token'])) {


    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

}


require_once __DIR__ . '/funciones.php';
