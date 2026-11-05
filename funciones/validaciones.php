<?php

require_once __DIR__ . '/../funciones/visitas.php';


function esHoraValidaJPB(string $hora): bool
{
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        return false;
    }

    [$h, $m] = explode(':', $hora);

    return (int)$h <= 23 && (int)$m <= 59;
}


function esFechaValidaJPB(string $fecha): bool
{
    if (!preg_match('~^\d{2}/\d{2}/\d{4}$~', $fecha)) {
        return false;
    }

    [$dia, $mes, $year] = explode('/', $fecha);

    return checkdate((int)$mes, (int)$dia, (int)$year);
}


function sonResidentesCorrectosJPB(array $residentesBuenos, array $residentesRecibidos): bool  // Paso 5  funcion 1
{
    foreach ($residentesRecibidos as $residente) {
        if (!in_array($residente, $residentesBuenos)) {
            return false;
        }
    }

    return true;
}



// Funciones del ejercicio 3 paso 2

function esDuracionValidaXYZ(array $conf, string $hora_inicio, string $hora_fin): bool
{
    $inicio = strtotime($hora_inicio);
    $fin = strtotime($hora_fin);
    $duracion = ($fin - $inicio) / 60;

    return $duracion >= $conf['duracion_minima'];
}

function estaEnUnaFranjaXYZ(array $conf, string $hora_inicio, string $hora_fin): bool {}

function solapaConVisitasXYZ(array $visitas, string $fecha, string $hora_inicio, string $hora_fin): bool {}
