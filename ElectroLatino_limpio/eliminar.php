<?php

require_once __DIR__ . '/includes/config.php';
exigirPostValido();

cabecera('Eliminar Producto');

$id = filter_input(
    INPUT_POST,
    'producto_id',
    FILTER_VALIDATE_INT
);

if ( $id === false || $id === null){
    http_response_code(400);
    exit('Identificador no válido');
}

unset($_SESSION['carrito'][$id]);

volver('carrito.php');

