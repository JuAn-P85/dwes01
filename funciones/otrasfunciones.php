<?php // funcion para cumplir con la memoria de las casillas del paso 4 en el index2

function obtencionResidentes(array $visitas): array
{
    $residentes = array_column($visitas, 'residente');
    $residentes = array_unique($residentes);
    sort($residentes);

    return $residentes;
}