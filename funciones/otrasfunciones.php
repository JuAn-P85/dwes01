<?php // funcion para cumplir con la memoria de las casillas del paso 4 en el index2

function obtencionResidentes(array $visitas): array
{
    $residentes = array_column($visitas, 'residente');
    $residentes = array_unique($residentes); // hace que no se repitan
    sort($residentes);

    return $residentes;
}


function obtencionVinculos(array $visitas): array {   // funcion creada para el selector de vinculos del ejercicio 3
    $vinculos = array_unique(array_column($visitas, 'vinculo'));
    sort($vinculos);
    return $vinculos;
}