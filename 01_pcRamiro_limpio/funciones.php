<?php

function formatearPrecio(int $centimos): string
{
    $euros = $centimos / 100;

    return number_format(
        $euros,
        2,
        ",",
        "."
    ) . " €";
}

function obtenerEstadoStock(int $stock): string
{
    if ($stock === 0) {
        return "Agotado";
    }

    if ($stock <= 5) {
        return "Últimas unidades";
    }

    return "Disponible";
}

function obtenerClaseEstado(int $stock): string
{
    if ($stock === 0) {
        return "agotado";
    }

    if ($stock <= 5) {
        return "aviso";
    }

    return "disponible";
}

function escapar(string $texto): string
{
    return htmlspecialchars(
        $texto,
        ENT_QUOTES | ENT_SUBSTITUTE,
        "UTF-8"
    );
}

function buscarProductoPorId(array $productos, int $id): ?array
{
    foreach ($productos as $producto) {
        if ($producto["id"] === $id) {
            return $producto;
        }
    }

    return null;
}

function normalizarTexto(string $texto): string
{
    $texto = trim($texto);

    if (function_exists("mb_strtolower")){
        return mb_strtolower($texto, "UTF-8");
    }

    return strtolower($texto);   
}

function buscarProductos ( 
    array $productos,  
    string $busqueda
): array {

    $resultados = [];

    $busqueda = normalizarTexto($busqueda);

    if ($busqueda === ""){
        return $resultados;
    }

    foreach ($productos as $producto){
        $nombre = normalizarTexto($producto["nombre"]);

        if (str_contains($nombre, $busqueda)){
            $resultados[] = $producto;
        }
    }

    return $resultados;
}

function leerCadena(array $origen, string $clave): string {

    $valor = $origen[$clave] ?? "";
    if (!is_string($valor)) {
        return "";
    }
    return $valor;
}

function calcularCompra(
        int $precio,
        int $unidades
) : array {

        $subtotal = $precio *  $unidades;

        $descuento = 0;

        if ($unidades >= UNIDADES_DESCUENTO){

            $descuento = (int) round($subtotal * DESCUENTO_CANTIDAD); 
        }

        $base = $subtotal - $descuento;

        $iva = (int) round ($base * IVA_GENERAL);

        $total = $base + $iva;

        return [
            "subtotal" => $subtotal,
            "descuento" => $descuento,
            "base" => $base,
            "iva" => $iva,
            "total" => $total
        ];

}