<?php
require_once __DIR__ . '/../funciones/visitas.php';
require_once __DIR__ . '/../funciones/otrasfunciones.php';

$conf = require __DIR__ . '/../conf/horario.php';

$tramos          = $conf['horario'];
$duracion_minima = $conf['duracion_minima'];

$archivo = __DIR__ . '/../datos/visitas.csv';
[, , $visitas] = cargarVisitasJPB($archivo);
$residentes = obtencionResidentes($visitas);

// variables para datos del formulario 
$residente     = trim($_POST['residente'] ?? '');
$vinculo       = trim($_POST['vinculo'] ?? '');
$solicitante   = trim($_POST['solicitante'] ?? '');
$fecha         = trim($_POST['fecha'] ?? '');
$inicio        = trim($_POST['inicio'] ?? '');
$fin           = trim($_POST['fin'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

$errores = [];
$estado = 'errores'; // 'errores' | 'ok' | 'ko'

// referente a residentes
if (empty($residente)) {
    $errores[] = 'No se ha seleccionado ningún residente. '
        . 'Por fabor,elige uno de la lista desplegable.';
} elseif (!in_array($residente, $residentes)) {
    $errores[] = "El residente \"$residente\" no existe en la lista. "
        . 'Selecciona un nombre válido.';
}

// vínculo
if (empty($vinculo)) {
    $errores[] = 'No has seleccionado ningún vínculo. '
        . 'Por fabor, elige una opción del desplegable ('
        . implode(', ', VINCULOS) . ').';
} elseif (!in_array($vinculo, VINCULOS)) {
    $errores[] = "El vínculo \"$vinculo\" no es válido. "
        . 'Las opciones permitidas son: ' . implode(', ', VINCULOS)
        . '. Selecciona una de ellas.';
}

// Solicitante
if (empty($solicitante)) {
    $errores[] = 'No se ha indicado el nombre de la persona que solicita la visita. '
        . 'Por fabor, escribe el nombre completo del solicitante.';
}

//Fecha
if (empty($fecha)) {
    $errores[] = 'No has indicado ninguna fecha. '
        . 'Por fabor ,escríbela con el formato dd/mm/aaaa.';
} elseif (!esFechaValidaJPB($fecha)) {
    if (!preg_match('~^\d{2}/\d{2}/\d{4}$~', $fecha)) {
        $errores[] = "La fecha \"$fecha\" no tiene un formato válido. "
            . 'Debe escribirse con el formato dd/mm/aaaa '. 'Por ejemplo: 06/11/2026.';
    } else {
        [$d, $m, $a] = explode('/', $fecha);
        $errores[] = "La fecha \"$fecha\" no existe en el calendario. "
            . "El mes $m no tiene día $d en el año $a. " . 'Por fabor comprueba los datos intocucidos inténtalo de nuevo.';
    }
}

// Horarios
if (empty($inicio)) {
    $errores[] = 'No se ha indicado la hora de inicio de visita. '
        . 'Por fabor, escríbela con el formato hh:mm.';
} elseif (!esHoraValidaJPB($inicio)) {
    $errores[] = "La hora de entrada \"$inicio\" no tiene un formato válido. "
        . 'Debe escribirse con el formato hh:mm ' . 'Por ejemplo: 11:30.';
}


if (empty($fin)) {
    $errores[] = 'No has indicado una hora de salida. '
        . 'Por fabor, escríbela con el formato hh:mm ';
} elseif (!esHoraValidaJPB($fin)) {
    $errores[] = "La hora de salida \"$fin\" no tiene un formato válido. "
        . 'Debe escribirse con el formato hh:mm ' . 'Por ejemplo: 12:30.';
}


if (esHoraValidaJPB($inicio) && esHoraValidaJPB($fin) && $fin <= $inicio) {
    $errores[] = "La hora de salida \"$fin\" tiene que ser  posterior a \"$inicio\". "
        . 'Por fabor ,indica una hora de salida por una posterior a ' . $inicio . '.';
}

// Duración mínima
if (esHoraValidaJPB($inicio) && esHoraValidaJPB($fin) && $fin > $inicio) {
    if (!esDuracionValidaJPB($conf, $inicio, $fin)) {
        [$hi, $mi] = explode(':', $inicio);
        [$hf, $mf] = explode(':', $fin);
        $duracion = ((int)$hf * 60 + (int)$mf) - ((int)$hi * 60 + (int)$mi);

        $errores[] = "La visita de $inicio a $fin dura $duracion minutos, "
            . "pero la politica establece una duración mínima de $duracion_minima minutos. "
            . 'Por fabor amplía la franja de salida para alcanzar al menos ' . $duracion_minima . ' minutos.';
    }
}

// franja horaria
if (esHoraValidaJPB($inicio) && esHoraValidaJPB($fin) && $fin > $inicio) {
    if (!estaEnUnaFranjaJPB($conf, $inicio, $fin)) {
        $textoTramos = array_map(
            fn($f) => "de {$f[0]} a {$f[1]}",
            $tramos
        );

        $errores[] = "La visita de $inicio a $fin no se encuentra dentro de una franja horaria. "
            . 'Se puede visitar ' . implode(' y ', $textoTramos) . ', '            
            . 'Por fabor elige una hora de entrada y salida que estén dentro de una misma franja.';
    }
}

// Solapes
if (
    esFechaValidaJPB($fecha) && esHoraValidaJPB($inicio)
    && esHoraValidaJPB($fin) && $fin > $inicio
    && in_array($residente, $residentes)
) {

    if (solapaConVisitasJPB($visitas, $fecha, $inicio, $fin)) {
        $visitaSolapada = null;
        foreach ($visitas as $v) {
            if (
                $v['residente'] === $residente && $v['fecha'] === $fecha
                && $inicio < $v['fin'] && $fin > $v['inicio']
            ) {
                $visitaSolapada = $v;
                break;
            }
        }

        $errores[] = "Ya exite una visita concertada con $residente "
            . "el $fecha que se solapa con la franja de $inicio a $fin "
            . "(visita existente: {$visitaSolapada['inicio']} a {$visitaSolapada['fin']}). "
            . 'Por fabor, consulta el listado de ese residente y elige un hueco libre.';
    }
}

// guarda si pasa todo ( paso 4 ejercicio 3)
if (empty($errores)) {

    $nuevaVisita = [
        'residente'     => $residente,
        'solicitante'   => $solicitante,
        'vinculo'       => $vinculo,
        'fecha'         => $fecha,
        'inicio'        => $inicio,
        'fin'           => $fin,
        'observaciones' => $observaciones,
    ];

    $escrito = guardarVisitaCSV($archivo, $nuevaVisita);

    $estado = $escrito ? 'ok' : 'ko';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de la solicitud - Tarea unidad 1</title>
</head>

<body>
    <h1>Resultado de la solicitud</h1>

    <?php if ($estado === 'ok'): ?>
        <p id="resultado">
            Visita registrada correctamente.
            <?php echo htmlspecialchars($solicitante) ?> visitará a
            <?php echo htmlspecialchars($residente) ?> el
            <?php echo htmlspecialchars($fecha) ?> de
            <?php echo htmlspecialchars($inicio) ?> a
            <?php echo htmlspecialchars($fin) ?>.
        </p>
    <?php elseif ($estado === 'ko'): ?>
        <p id="resultado">
            Los datos son correctos, pero <strong>no ha sido posible guardar la visita</strong>
            en el archivo. Por fabor, inténtalo de nuevo o avisa al administrador de sistemas ,Juan Pedro Buger.
        </p>
    <?php else: ?>
        <p id="resultado">No se ha podido concertar la visita.</p>
        <ul id="errores">
            <?php foreach ($errores as $error): ?>
                <li><?php echo htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <a href="index.php">Solicitar otra visita</a> |
    <a href="../ej2/index.php">Ver las visitas concertadas</a> |
    <a href="../index.php">Ir a la página principal</a>

    <hr>
    <footer id="autor">Desarrollo Web en Entorno Servidor - Ejercicio 3 - Tarea 1 - Alumno <b>Juan Pedro Buger ®</b></footer>
</body>

</html>