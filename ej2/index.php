<!DOCTYPE html>
<html lang="es">

<?php

require_once __DIR__ . '/../funciones/visitas.php';
require_once __DIR__ . '/../funciones/otrasfunciones.php';

require_once __DIR__ . '/../funciones/validaciones.php';  // archivos del paso 5
require_once __DIR__ . '/../funciones/filtradatos.php';

$archivo = __DIR__ . '/../datos/visitas.csv';
[$filas_ok, $filas_ko, $visitas] = cargarVisitasJPB($archivo);

$residentes = obtencionResidentes($visitas); // variable que almacena el array de la funcion de otrasfunciones

$residentes_marcados = $_POST['residentes'] ?? [];  // casillas marcadas

$aviso = false;
$recuentoEliminadas = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // Verifica si se ha enviado el form

    if (sonResidentesCorrectosJPB($residentes, $residentes_marcados)) {

        $recuentoEliminadas = filtrarVisitasJPB($residentes_marcados, $visitas);
    } else {

        $aviso = true;

        $residentes_marcados = array_intersect($residentes_marcados, $residentes);
    }
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de visitas - Tarea unidad 1</title>
</head>


<body>
    <h1>Visitas concertadas</h1>

    <a href="index.php">Quitar el filtro</a> |
    <a href="../ej3/index.php">Solicitar una visita</a> |
    <a href="../index.php">Ir a la página principal</a>

    <p>
        Se han cargado <span id="filas_correctas"><?= $filas_ok ?></span> visitas del archivo <!-- Paso 3 -->
        y se han descartado <span id="filas_incorrectas"><?= $filas_ko ?></span> filas
        porque no cumplen el formato.
    </p>

    <?php if ($aviso) { ?>
        <div id="aviso">
            <h3 style="color:red">¡Atención!</h3>
            <p>
                <strong style="color:red">
                    Se han seleccionado residentes que no aparecen en el archivo.
                </strong>
            </p>
        </div>
    <?php } ?>

    <h2>Filtrar por residentes</h2>
    <h3>Selecciona los residentes por los que desees filtrar</h3>

    <form id="form_filtro_residentes" action="index.php" method="POST">

        <?php // Paso 4 , posterior al codigo del 3 mas abajo 
        ?>

        <?php foreach ($residentes as $residente) { ?> <!-- Paso 4 ,recorre los residentes y el input muestra los nombres-->

            <label style="display:inline-block;width:300px;">

                <input
                    type="checkbox"
                    name="residentes[]"
                    value="<?= htmlspecialchars($residente) ?>"
                    <?= in_array($residente, $residentes_marcados, true) ? 'checked' : '' ?>>

                <?= htmlspecialchars($residente) ?>

            </label>

        <?php } ?>

        <label style="display:inline-block;width:300px;">
            <input id="residente_no_valido" type="checkbox" name="residentes[]" value="Residente no válido">
            Residente no válido
        </label>

        <br>
        <button type="submit" id="boton_filtrar">Filtrar</button>

    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$aviso && !empty($residentes_marcados)) { ?>

        <h3>
            Se han filtrado los datos por los residentes seleccionados.
            Se dejan de mostrar
            <span id="recuentoEliminadas"><?= $recuentoEliminadas ?></span>
            visitas.
        </h3>

    <?php } ?>

    <table id="tabla_visitas" border="1">
        <thead>
            <tr>
                <th id="th_residente">Residente</th>
                <th id="th_solicitante">Solicitante</th>
                <th id="th_vinculo">Vínculo</th>
                <th id="th_fecha">Fecha</th>
                <th id="th_desde">Desde</th>
                <th id="th_hasta">Hasta</th>
                <th id="th_observaciones">Observaciones</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($visitas as $visita) { ?> <!-- Paso 2 , suprimimos foreach anidado -->

                <tr>
                    <td><?= htmlspecialchars($visita['residente']) ?></td>
                    <td><?= htmlspecialchars($visita['solicitante']) ?></td>
                    <td><?= htmlspecialchars($visita['vinculo']) ?></td>
                    <td><?= htmlspecialchars($visita['fecha']) ?></td>
                    <td><?= htmlspecialchars($visita['inicio']) ?></td>
                    <td><?= htmlspecialchars($visita['fin']) ?></td>
                    <td><?= htmlspecialchars($visita['observaciones']) ?></td>
                </tr>

            <?php } ?>

        </tbody>
    </table>

    <hr>

    <footer id="autor">
        Desarrollo Web en Entorno Servidor - Ejercicio 2 - Tarea 1 - Alumno
        <b>Juan Pedro Buger ®</b>
    </footer>

</body>

</html>