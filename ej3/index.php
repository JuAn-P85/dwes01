<?php
require_once __DIR__ . '/../funciones/visitas.php';
require_once __DIR__ . '/../funciones/otrasfunciones.php';

$archivo = __DIR__ . '/../datos/visitas.csv';
[,, $visitas] = cargarVisitasJPB($archivo);

$residentes = obtencionResidentes($visitas); //otras funciones

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar una visita - Tarea unidad 1</title>
</head>

<body>
    <h1>Solicitar una visita</h1>

    <a href="../ej2/index.php">Ver las visitas concertadas</a> |
    <a href="../index.php">Ir a la página principal</a>

    <h2>No se ha podido concertar la visita</h2>
    <ul id="errores">
        <li></li>
    </ul>

    <form action="guardar.php" method="post">
        <p>
            <label for="residente">Residente al que se visita:</label><br>
            <select id="residente" name="residente">
                <option value="">-- Selecciona un residente --</option>
                <?php foreach ($residentes as $usuario) { ?> <!-- para mostrar los usuarios en el desplegable -->
                    <option value="<?php echo htmlspecialchars($usuario) ?>"><?php echo htmlspecialchars($usuario) ?></option> <!-- la primera manda el valor y la segunda muestra el texto desntro del option-->
                <?php } ?>
                <option value="residente-de-pruebas-no-existente">Residente de pruebas (no existente)</option>
            </select>
        </p>

        <p>
            <label for="vinculo">Vínculo con el residente:</label><br>
            <select id="vinculo" name="vinculo">
                <option value="">-- Selecciona el vínculo --</option>
                <?php foreach (VINCULOS as $vinculo) { ?>
                    <option value="<?php echo htmlspecialchars($vinculo) ?>"><?php echo htmlspecialchars($vinculo) ?></option>
                <?php } ?>
                <option value="vinculo-erroneo-de-pruebas">Valor erróneo de vínculo (para pruebas) </option>
            </select>
        </p>

        <p>
            <label for="solicitante">Quién solicita la visita:</label><br>
            <input type="text" id="solicitante" name="solicitante" size="40" value="">
        </p>

        <p>
            <label for="fecha">Día de la visita (dd/mm/aaaa):</label><br>
            <input type="text" id="fecha" name="fecha" size="12" value="">
        </p>

        <p>
            <label for="inicio">Hora de entrada (hh:mm):</label><br>
            <input type="text" id="inicio" name="inicio" size="6" value="">
        </p>

        <p>
            <label for="fin">Hora de salida (hh:mm):</label><br>
            <input type="text" id="fin" name="fin" size="6" value="">
        </p>

        <p>
            <label for="observaciones">Observaciones (opcional):</label><br>
            <textarea id="observaciones" name="observaciones" rows="3" cols="60"></textarea>
        </p>
        <p><button type="submit" id="enviar">Registrar la visita</button></p>
    </form>

    <hr>
    <footer id="autor">Desarrollo Web en Entorno Servidor - Ejercicio 3 - Tarea 1 - Alumno <b>Juan Pedro Buger ®</b></footer>
</body>

</html>