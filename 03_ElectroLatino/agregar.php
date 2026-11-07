<?php
/**
 * Añadir productos al carrito de la sesión.
 * De momento comprobamos que el formulario envía los datos por POST.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/productos.php';
exigirPostValido();

/* Tenemos una variable id que se iguala al valor del campo producto_id del formulario de index.php mediante filter_input() 
esta función de PHP permite filtrar y validar los datos de entrada, de manera que recibe como parámetros: que queremos 
recuperar información de la solicitud POST, el nombre del campo que queremos recuperar y el filtro que queremos aplicar a ese 
valor. En este caso, estamos recuperando el valor del campo producto_id del formulario enviado por POST (en caso de que 
lo que recibamos sea un entero válido, o de lo contrario nos va a devolver false) */
$id = filter_input(INPUT_POST, 'producto_id', FILTER_VALIDATE_INT);

// Si el id es false, null o no existe en el array de productos, se devuelve un error 400 (Bad Request) y se detiene la 
// ejecución del script con un mensaje de error indicando que el producto no es válido.
if ($id === false || $id === null || !isset($productos[$id])) {
    http_response_code(400);
    exit('Producto no válido.');
}

// Esta variable comprueba si el producto ya existe en el carrito de la sesión. Si existe, se obtiene la cantidad actual 
// del producto en el carrito, y si no existe, se establece en 0 y luego se actualiza la cantidad del producto en el 
// carrito sumando 1 (de esta manera, cada vez que se añade un producto al carrito, se incrementa su cantidad en 1). 
$actual = (int) ( $_SESSION['carrito'][$id] ?? 0 );
/*Parte por parte:
$actual =: guarda un valor en la variable $actual.
(int): convierte el resultado a un número entero.
$_SESSION['carrito']: accede al carrito guardado en la sesión.
[$id]: busca dentro del carrito la cantidad asociada a ese identificador de producto.
?? 0: si esa posición no existe o su valor es null, usa 0 como valor predeterminado. */

// Se establece un límite máximo de 99 unidades por producto en el carrito. La función min() se utiliza para asegurarse de
// que la cantidad del producto en el carrito no supere este límite. Si la cantidad actual más 1 es mayor que 99, 
// se establecerá en 99. De lo contrario, se incrementará en 1. Esto evita que los usuarios puedan añadir más de 99 unidades 
// de un mismo producto al carrito.
$_SESSION['carrito'][$id] = min($actual + 1, 99);
/* $_SESSION['carrito'][$id]: selecciona en el carrito la cantidad del producto cuyo identificador es $id.
= guarda el resultado de nuevo en el carrito.
min(..., 99): elige el menor valor entre esa cantidad y 99. Así se limita el máximo a 99 unidades.
$actual + 1: aumenta en una unidad la cantidad actual.
Por ejemplo, si $actual es 4, guarda 5; si ya es 99, guarda 99. */

volver('carrito.php');