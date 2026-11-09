<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);
?>


<?php
require_once __DIR__ . '/../funciones/visitas.php';
require_once __DIR__ . '/../funciones/otrasfunciones.php';
require_once __DIR__ . '/../funciones/_guardardatos.php'; // nesesario para elelseif sin errores

$conf = require __DIR__ . '/../conf/horario.php';

$tramos          = $conf['horario'];
$duracion_minima = $conf['duracion_minima'];

$archivo = __DIR__ . '/../datos/visitas.csv';
[,, $visitas] = cargarVisitasJPB($archivo);
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
$estado  = 'errores'; // 'errores' | 'ok' | 'ko'

// valores que se conservan en el formulario (vacío = dato erróneo)
$valores = [
    'residente'     => '',
    'vinculo'       => '',
    'solicitante'   => '',
    'fecha'         => '',
    'inicio'        => '',
    'fin'           => '',
    'observaciones' => $observaciones,
];

// referente a residentes
if (empty($residente)) {
    $errores[] = 'No se ha seleccionado ningún residente. '
        . 'Por fabor,elige uno de la lista desplegable.';
} elseif (!in_array($residente, $residentes)) {
    $errores[] = "El residente \"$residente\" no existe en la lista. "
        . 'Selecciona un nombre válido.';
} else {
    $valores['residente'] = $residente;
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
} else {
    $valores['vinculo'] = $vinculo;
}

// Solicitante
if (empty($solicitante)) {
    $errores[] = 'No se ha indicado el nombre de la persona que solicita la visita. '
        . 'Por fabor, escribe el nombre completo del solicitante.';
} else {
    $valores['solicitante'] = $solicitante;
}

//Fecha
if (empty($fecha)) {
    $errores[] = 'No has indicado ninguna fecha. '
        . 'Por fabor ,escríbela con el formato dd/mm/aaaa.';
} elseif (!esFechaValidaJPB($fecha)) {
    if (!preg_match('~^\d{2}/\d{2}/\d{4}$~', $fecha)) {
        $errores[] = "La fecha \"$fecha\" no tiene un formato válido. "
            . 'Debe escribirse con el formato dd/mm/aaaa ' . 'Por ejemplo: 06/11/2026.';
    } else {
        [$d, $m, $a] = explode('/', $fecha);
        $errores[] = "La fecha \"$fecha\" no existe en el calendario. "
            . "El mes $m no tiene día $d en el año $a. " . 'Por fabor comprueba los datos intocucidos inténtalo de nuevo.';
    }
} else {
    $valores['fecha'] = $fecha;
}

// Horarios
$inicioValido = false;
$finValido    = false;

if (empty($inicio)) {
    $errores[] = 'No se ha indicado la hora de inicio de visita. '
        . 'Por fabor, escríbela con el formato hh:mm.';
} elseif (!esHoraValidaJPB($inicio)) {
    $errores[] = "La hora de entrada \"$inicio\" no tiene un formato válido. "
        . 'Debe escribirse con el formato hh:mm ' . 'Por ejemplo: 11:30.';
} else {
    $inicioValido = true;
}


if (empty($fin)) {
    $errores[] = 'No has indicado una hora de salida. '
        . 'Por fabor, escríbela con el formato hh:mm ';
} elseif (!esHoraValidaJPB($fin)) {
    $errores[] = "La hora de salida \"$fin\" no tiene un formato válido. "
        . 'Debe escribirse con el formato hh:mm ' . 'Por ejemplo: 12:30.';
} else {
    $finValido = true;
}


if ($inicioValido && $finValido && $fin <= $inicio) {
    $errores[] = "La hora de salida \"$fin\" tiene que ser  posterior a \"$inicio\". "
        . 'Por fabor ,indica una hora de salida por una posterior a ' . $inicio . '.';
    $inicioValido = false;
    $finValido    = false;
}

// Duración mínima
if ($inicioValido && $finValido && !esDuracionValidaJPB($conf, $inicio, $fin)) {
    $duracion = duracionEnMinutosJPB($inicio, $fin);

    $errores[] = "La visita de $inicio a $fin dura $duracion minutos, "
        . "pero la politica establece una duración mínima de $duracion_minima minutos. "
        . 'Por fabor amplía la franja de salida para alcanzar al menos ' . $duracion_minima . ' minutos.';
    $inicioValido = false;
    $finValido    = false;
}

// franja horaria
if ($inicioValido && $finValido && !estaEnUnaFranjaJPB($conf, $inicio, $fin)) {
    $textoTramos = array_map(
        fn($f) => "de {$f[0]} a {$f[1]}",
        $tramos
    );

    $errores[] = "La visita de $inicio a $fin no se encuentra dentro de una franja horaria. "
        . 'Se puede visitar ' . implode(' y ', $textoTramos) . ', '
        . 'Por fabor elige una hora de entrada y salida que estén dentro de una misma franja.';
    $inicioValido = false;
    $finValido    = false;
}

// las horas que siguen válidas se conservan
if ($inicioValido) {
    $valores['inicio'] = $inicio;
}
if ($finValido) {
    $valores['fin']    = $fin;
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

// paso 5: si hay errores, se incorpora index.php (con el formulario) y se aborta
if ($estado === 'errores') {
    require __DIR__ . '/index.php';
    exit;
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

        <h2>Histórico de visitas de <?php echo htmlspecialchars($residente) ?></h2>
        <p>
            <?php echo htmlspecialchars($residente) ?>:
            <?php echo tiempoDesdeUltimaVisitaJPB($visitas, $residente) ?>.
        </p>

        <?php $historial = historialVisitasResidenteJPB($visitas, $residente); ?>
        <?php if (empty($historial)): ?>
            <p>Este residente no tiene visitas registradas.</p>
        <?php else: ?>
            <ul id="historico">
                <?php foreach ($historial as $v): ?>
                    <li>
                        <?php echo htmlspecialchars($v['fecha']) ?>
                        de <?php echo htmlspecialchars($v['inicio']) ?>
                        a <?php echo htmlspecialchars($v['fin']) ?>
                        (<?php echo htmlspecialchars($v['vinculo']) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php else: ?>
        <p id="resultado">
            Los datos son correctos, pero <strong>no ha sido posible guardar la visita</strong>
            en el archivo. Por fabor, inténtalo de nuevo o avisa al administrador de sistemas ,Juan Pedro Buger.
        </p>
    <?php endif; ?>

    <a href="index.php">Solicitar otra visita</a> |
    <a href="../ej2/index.php">Ver las visitas concertadas</a> |
    <a href="../index.php">Ir a la página principal</a>

    <hr>
    <footer id="autor">Desarrollo Web en Entorno Servidor - Ejercicio 3 - Tarea 1 - Alumno <b>Juan Pedro Buger ®</b></footer>
</body>

</html>