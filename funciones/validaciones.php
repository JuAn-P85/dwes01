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

function esDuracionValidaJPB(array $conf, string $hora_inicio, string $hora_fin): bool
{
    $inicio = strtotime($hora_inicio);
    $fin = strtotime($hora_fin);
    $duracion = ($fin - $inicio) / 60;

    return $duracion >= $conf['duracion_minima'];
}

function estaEnUnaFranjaJPB(array $conf, string $hora_inicio, string $hora_fin): bool
{
    foreach ($conf['horario'] as $franja) {

        if (
            $hora_inicio >= $franja[0] && $hora_fin <= $franja[1]
        ) {
            return true;
        }
    }

    return false;
}

function solapaConVisitasJPB(array $visitas, string $fecha, string $hora_inicio, string $hora_fin): bool 
{
    foreach ($visitas as $visita) {


        if ($visita['fecha'] == $fecha) {

            // Después comprobamos si los horarios se solapan.
            if (
                $hora_inicio < $visita['fin'] &&
                $hora_fin > $visita['inicio']
            ) {
                return true;
            }
        }
    }

    return false;
}