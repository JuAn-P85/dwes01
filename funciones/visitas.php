<?php

require_once __DIR__ . '/_cargadatos.php';
require_once __DIR__ . '/validaciones.php';

function cargarVisitasJPB(string $archivo): array
{

    $filas_ok = 0;
    $filas_ko = 0;
    $visitas = [];
    $filas = cargarCSV($archivo);


    foreach ($filas as $fila) { // Recorre el CSV
        if (
            count($fila) == count(CABECERAS) &&  // Verifica 8 columnas
            in_array($fila[2], VINCULOS, true) && // Verificacion de los vinculos
            esFechaValidaJPB($fila[3]) &&   // usando las funciones de validaciones.php
            esHoraValidaJPB($fila[4]) &&  //
            esHoraValidaJPB($fila[5]) &&
            $fila[5] > $fila[4]  // Verificacion de que la hora de salida es superior a la de llegada
        ) {
            $filas_ok++;
            $visita = array_combine(CABECERAS, $fila);
            $visitas[] = $visita;
        } else {
            $filas_ko++;
        }
    }

    return [$filas_ok, $filas_ko, $visitas];
}
