<?php // funcion para cumplir con la memoria de las casillas del paso 4 en el index2

function obtencionResidentes(array $visitas): array
{
    $residentes = array_column($visitas, 'residente');
    $residentes = array_unique($residentes); // hace que no se repitan
    sort($residentes);

    return $residentes;
}


//Funciones propias

// calcula la duración en minutos entre dos horas del mismo día.

function duracionEnMinutosJPB(string $inicio, string $fin): int
{
    [$hi, $mi] = explode(':', $inicio);
    [$hf, $mf] = explode(':', $fin);

    return ((int)$hf * 60 + (int)$mf) - ((int)$hi * 60 + (int)$mi);
}


//devuelve la ultima visita  de un residente 

function ultimaVisitaResidenteJPB(array $visitas, string $residente): ?array
{
    $historial = historialVisitasResidenteJPB($visitas, $residente);

    if (empty($historial)) {
        return null;
    }

    return $historial[count($historial) - 1];
}

//Calcula cuánto tiempo ha pasado desde la última visita

function tiempoDesdeUltimaVisitaJPB(array $visitas, string $residente): string
{
    $ultima = ultimaVisitaResidenteJPB($visitas, $residente);

    if ($ultima === null) {
        return 'nunca ha recibido visitas';
    }

    $fechaUltima = DateTime::createFromFormat('d/m/Y', $ultima['fecha']);
    $hoy         = new DateTime();
    $dias        = $fechaUltima->diff($hoy)->days;

    if ($dias === 0) {
        return 'recibió visita hoy mismo';
    }

    return $dias === 1
        ? 'hace 1 día'
        : "hace $dias días";
}

// retorna el histórico de visitas del residente indicado
function historialVisitasResidenteJPB(array $visitas, string $residente): array
{
    $historial = array_values(array_filter(
        $visitas,
        fn($v) => $v['residente'] === $residente
    ));

    usort($historial, function ($a, $b) {
        $fa = DateTime::createFromFormat('d/m/Y', $a['fecha']);
        $fb = DateTime::createFromFormat('d/m/Y', $b['fecha']);
        if ($fa != $fb) {
            return $fa <=> $fb;
        }
        return strcmp($a['inicio'], $b['inicio']);
    });

    return $historial;
}
