<?php

/* Esta es una página en la que al acceder vamos a obtener información 
sobre la sesión actual y permite regenerar su identificador. */

/**inspección y regeneración de sesión. 
 * Implica que ya se está creando la sesión con session_start() en config.php, 
 * que se está usando un token CSRF para proteger la regeneración de sesión,
 * se está mostrando el nombre de la cookie de sesión, el id de sesión y el 
 * contenido del carrito de la sesión. */
require_once __DIR__ . '/includes/config.php';


/* Comprobamos si el método de la solicitud es POST */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') { // ?? '' = si no existe $_SERVER['REQUEST_METHOD'] devuelve cadena vacía
// si existe, se comprueba si es igual a 'POST', y si es así se ejecuta el bloque de código de debajo.
    exigirPostValido(); // función definida en includes/config.php que comprueba el token CSRF
    session_regenerate_id(true); /* Esto sirve para comprobar si es necesario regenerar el ID de sesión. 
     Es decir, si antes de esta sesión teníamos un ID se sesión regeneramos el identificador de sesión y destruimos la sesión 
     antigua. Esto sirve para prevenir ataques de fijación de sesión y mejorar la seguridad de la aplicación. (en cualquier
     aplicación web que maneje sesiones de usuario, es recomendable regenerar el ID de sesión después de un inicio de sesión 
     exitoso (cada vez que se abre el navegador) o después de un cambio importante en la sesión del usuario, como cambiar 
     su contraseña o actualizar su perfil). */
    volver('sesion.php'); // función definida en includes/config.php que redirige a la página indicada con estos datos y 
    // termina la ejecución del script.
}

cabecera('Inspección de sesión'); /*cabecera() es una función definida en 
includes/config.php que genera la cabecera de la página */
?>
<section class="intro">
    <p class="etiqueta">LABORATORIO</p><h1>¿Cómo funciona una sesión?</h1></section>
<div class="panel">
   

  <p> <strong>Nombre de la cookie de sesión: </strong> 
     <code class="romper"><?= e(session_name()) ?>  </code> <!-- La etiqueta <code> se utiliza para mostrar 
      fragmentos de código o texto que se desea resaltar como código.
     La clase romper se utiliza para aplicar estilos CSS específicos a este elemento, como permitir que el 
     contenido se divida en varias líneas si es demasiado largo para caber en una sola línea.
     e() es una función de escapar definida en functions que evita interpretar como HTML los datos que se 
     imprimen en la página. A esta función le pasamos como argumento session_name(), que a su vez es otra 
     función que devuelve el nombre de la cookie de sesión. 
     session_name() y session_id() son funciones integradas de PHP-->
  </p>


  <p> <strong>Id de sesión: </strong> 
     <code class="romper"><?= e(session_id()) ?> </code>  
  </p>


  <!-- print_r() es una función de PHP que imprime información legible para humanos sobre una variable. 
     En este caso, se está utilizando para mostrar el contenido de la variable de sesión carrito como String, y no
     como objeto (true activa la salida en string) -->
  <p> <strong>Contenido de la variable de sesión carrito: </strong>
     <p class="romper"><?= e(print_r($_SESSION['carrito'], true)) ?> </p>

     
  <p> Unidades: <?= unidadesCarrito() ?> </p>


  <!-- Implementamos un botón que al pulsarlo regenera el identificador de sesión. 
   Este formulario hace un post sobre la misma página porque el formulario no tiene atributo 
   action. En HTML, cuando se omite, el navegador envía los datos a la URL de la página actual. 
   method="post" indica cómo enviarlos (mediante POST), no a dónde. Al pulsar el botón, el navegador 
   envía csrf_token a esa misma página. El servidor vuelve a ejecutar el PHP de la página, que puede 
   detectar el envío comprobando, por ejemplo, si el método de la solicitud es POST, y entonces regenerar 
   el identificador de sesión.
   Si quisieras enviarlo a otra página, indicarías su URL, por ejemplo action="otra_pagina.php", o podrías 
   escribir explícitamente la URL actual en action, aunque normalmente no hace falta.-->
  <form method="post">

   <!-- Se llama a la función campoCsrf() dentro de un echo para generar un token CSRF que se inserta en el 
    formulario como un campo oculto. Este echo imprime el <input> que devuelve la función dentro del formulario. 
    Cuando se envía, el navegador manda su valor con el nombre csrf_token. -->
    <?= campoCsrf() ?>

     <button type="submit">
        Regenerar identificador de sesión
     </button>

  </form>

   <!-- Implementamos un botón que al pulsarlo cierra la sesión y borra el carrito. -->
  <form action="cerrar.php" method="post" class="separado">
      <?= campoCsrf() ?>

     <button type="submit">
        Cerrar sesión y borrar carrito
     </button>

  </form>
    
</div>
<?php pie(); ?>
