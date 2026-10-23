<?php


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
