<?php
/**  vaciar carrito mediante la sesión. */
require_once __DIR__ . '/includes/config.php';
exigirPostValido();

$_SESSION['carrito'] = [];

volver('carrito.php');

