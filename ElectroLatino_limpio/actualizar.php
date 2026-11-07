<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/productos.php';
exigirPostValido();

cabecera('Actualizar');

$id = filter_input(
    INPUT_POST,
    'producto_id',
    FILTER_VALIDATE_INT
);

$cantidad = filter_input(
    INPUT_POST,
    'cantidad',
    FILTER_VALIDATE_INT
);


if ($id === false  || $id ===null || !isset($productos[$id])
 || $cantidad === false  || $cantidad ===null || $cantidad < 0 ) {

  http_response_code(400);
  exit('Producto o cantidad no válidos');
 }

  if ($cantidad === 0){
      unset($_SESSION['carrito'][$id]);
  }else {
    $_SESSION['carrito'][$id] = $cantidad;
  }
  
  volver('carrito.php');


  