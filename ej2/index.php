<!DOCTYPE html>
<html lang="es">

<?php

require_once __DIR__ . '/../funciones/visitas.php';

$archivo = __DIR__ . '/../datos/visitas.csv';

[$filas_ok, $filas_ko, $visitas] = cargarVisitasJPB($archivo);
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
        Se han cargado <span id="filas_correctas"><?= $filas_ok ?></span> visitas del archivo
        y se han descartado <span id="filas_incorrectas"><?= $filas_ko ?></span> filas
        porque no cumplen el formato.
    </p>

    <div id="aviso">
        <h3 style="color:red">¡Atención!</h3>
        <p><strong style="color:red"></strong></p>
    </div>

    <h2>Filtrar por residentes</h2>
    <h3>Selecciona los residentes por los que desees filtrar</h3>
    <form id="form_filtro_residentes" action="index.php" method="POST">

        <label style="display:inline-block;width:300px;">
            <input type="checkbox" name="residentes[]" value="">

        </label>
        <label style="display:inline-block;width:300px;">
            <input id="residente_no_valido" type="checkbox" name="residentes[]" value="Residente no válido">
            Residente no válido
        </label>
        <br>
        <button type="submit" id="boton_filtrar">Filtrar</button>

    </form>

    <H3> Se han filtrado los datos por los residentes seleccionados. Se dejan de mostrar <span id="recuentoEliminadas"><?= $filas_ko ?></span> visitas.</H3>

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
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <hr>
    <footer id="autor">Desarrollo Web en Entorno Servidor - Ejercicio 2 - Tarea 1 - Alumno <b>Juan Pedro Buger ®</b></footer>
</body>

</html>
