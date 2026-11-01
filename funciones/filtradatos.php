<?php   // Paso 5 del ejercicio 2 , funcion 2

function filtrarVisitasJPB(array $residentes, array &$visitas): int
{
    $visitas_borradas = 0;

    foreach ($visitas as $i => $visita) {
        if (!in_array($visita['residente'], $residentes)) {
            unset($visitas[$i]);
            $visitas_borradas++;
        }
    }

    return $visitas_borradas;
}