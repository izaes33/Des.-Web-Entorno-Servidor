<?php

require_once __DIR__ . '/includes/config.php';
exigirPostValido();

cabecera('Vaciar carrito');

$_SESSION['carrito'] = [];

volver('carrito.php');


