<?php

declare(strict_types=1);


function e(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


function euros(float $cantidad): string {
    return number_format($cantidad, 2, ',', '.') . ' €';
}


function unidadesCarrito(): int {
      return array_sum($_SESSION['carrito']);
}


function totalCarrito(array $productos): float {
    $total = 0.0;

    foreach ($_SESSION['carrito'] as $id => $cantidad){
        if(isset($productos[$id])) {
            $total += $productos[$id]['precio'] * (int)$cantidad;
        }
    }
    return $total;

}


function exigirPostValido(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método no permitido.');
    }
}

function campoCsrf(): string
{
    $token = htmlspecialchars(
        "prueba",
        ENT_QUOTES,
        'UTF-8'
    );

    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}


function volver(string $pagina): never {
    header('Location: ' . $pagina, true, 303);
    exit;
}


function temaActual(): string {
    
        $tema = $_COOKIE['tema'] ?? 'claro';

        if (

            !is_string($tema) ||
            !in_array($tema, ['claro', 'oscuro'], true)

        ) {
            return 'claro';
        }

        return $tema;


}

function cabecera(string $titulo): void {
    
    $tema = temaActual();
    ?>
    <!doctype html>
    <html lang="es" data-tema="<?= e($tema) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($titulo) ?> | MiniTienda PHP</title>
        <link rel="stylesheet" href="css/estilos.css">
    </head>
    <body>
    <header class="cabecera">
        <div class="contenedor barra">
            <a class="marca" href="index.php">MiniTienda PHP</a>
            <nav aria-label="Navegación principal">
                <a href="index.php">Catálogo</a>
                <a href="carrito.php">Carrito (<?= unidadesCarrito() ?>)</a>
                <a href="preferencias.php">Preferencias</a>
                <a href="sesion.php">Sesión</a>
            </nav>
        </div>
    </header>
    <main class="contenedor">
    <?php
}

function pie(): void {
    ?>
    </main>
    <footer class="pie">Proyecto didáctico de DWES · Unidad 4 · Docente: José Manuel Villar Ferradal</footer>
    </body></html>
    <?php
}
