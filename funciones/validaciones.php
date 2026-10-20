// Funciones de validacion
<?php>

function esHoraValidaJPB(string $hora): bool
{
    if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        return false;
    }

    [$h, $m] = explode(':', $hora);

    return $h <= 23 && $m <= 59;
}



function esFechaValidaJPB(string $fecha): bool




