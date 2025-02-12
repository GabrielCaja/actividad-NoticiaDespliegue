<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>PHP RSS Filter</title>
    </head>
    <body>
        <form action="index.php" method="GET">
            <fieldset>
                <legend>FILTRO</legend>
                <label>PERIÓDICO : </label>
                <select name="periodicos">
                    <option value="elpais">El País</option>
                    <option value="elmundo">El Mundo</option>
                </select>
                <label>CATEGORÍA : </label>
                <select name="categoria">
                    <option value=""></option>
                    <option value="Política">Política</option>
                    <option value="Deportes">Deportes</option>
                    <option value="Ciencia">Ciencia</option>
                    <option value="España">España</option>
                    <option value="Economía">Economía</option>
                    <option value="Música">Música</option>
                    <option value="Cine">Cine</option>
                    <option value="Europa">Europa</option>
                    <option value="Justicia">Justicia</option>
                </select>
                <label>FECHA : </label>
                <input type="date" name="fecha" value="">
                <label style="margin-left: 5vw;">AMPLIAR FILTRO (la descripción contenga la palabra) : </label>
                <input type="text" name="buscar" value="">
                <input type="submit" name="filtrar" value="Filtrar">
            </fieldset>
        </form>

        <?php
            // Función para aplicar filtros y mostrar resultados
            function filtros($sql, $link)
            {
                $result = pg_query($link, $sql); // Ejecutar consulta
                if (! $result) {
                    die("Error en la consulta: " . pg_last_error($link));
                }

                while ($row = pg_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td style='border: 1px #E4CCE8 solid;'>" . htmlspecialchars($row['titulo']) . "</td>";
                    echo "<td style='border: 1px #E4CCE8 solid;'>" . htmlspecialchars($row['contenido']) . "</td>";
                    echo "<td style='border: 1px #E4CCE8 solid;'>" . htmlspecialchars($row['descripcion']) . "</td>";
                    echo "<td style='border: 1px #E4CCE8 solid;'>" . htmlspecialchars($row['categoria']) . "</td>";
                    echo "<td style='border: 1px #E4CCE8 solid;'><a href='" . htmlspecialchars($row['link']) . "' target='_blank'>" . htmlspecialchars($row['link']) . "</a></td>";
                    echo "<td style='border: 1px #E4CCE8 solid;'>" . htmlspecialchars($row['fpubli']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }

            require_once "conexionBBDD.php"; // Asegúrate de que esta conexión use PostgreSQL

            if (! $link) {
                die("Error de conexión: " . pg_last_error());
            } else {
                echo "<table style='border: 5px #E4CCE8 solid;'>";
                echo "<tr>";
                echo "<th><p style='color: #66E9D9;'>TÍTULO</p></th>";
                echo "<th><p style='color: #66E9D9;'>CONTENIDO</p></th>";
                echo "<th><p style='color: #66E9D9;'>DESCRIPCIÓN</p></th>";
                echo "<th><p style='color: #66E9D9;'>CATEGORÍA</p></th>";
                echo "<th><p style='color: #66E9D9;'>ENLACE</p></th>";
                echo "<th><p style='color: #66E9D9;'>FECHA DE PUBLICACIÓN</p></th>";
                echo "</tr>";

                if (isset($_GET['filtrar'])) {
                    $periodicos = strtolower(trim($_GET['periodicos'])); 
                    $cat        = $_GET['categoria'];
                    $f          = $_GET['fecha'];
                    $palabra    = $_GET['buscar'];

                    // Filtrar por periódico
                    if ($cat == "" && $f == "" && $palabra == "") {
                        $sql = "SELECT * FROM $periodicos ORDER BY fpubli DESC";
                        filtros($sql, $link);
                    }

                    // Filtrar por categoría
                    if ($cat != "" && $f == "" && $palabra == "") {
                        $sql = "SELECT * FROM $periodicos WHERE categoria ILIKE '%$cat%'";
                        filtros($sql, $link);
                    }

                    // Filtrar por fecha
                    if ($cat == "" && $f != "" && $palabra == "") {
                        $sql = "SELECT * FROM $periodicos WHERE fpubli = '$f'";
                        filtros($sql, $link);
                    }

                    // Filtrar por categoría y fecha
                    if ($cat != "" && $f != "" && $palabra == "") {
                        $sql = "SELECT * FROM $periodicos WHERE categoria ILIKE '%$cat%' AND fpubli = '$f'";
                        filtros($sql, $link);
                    }

                    // Filtrar por todo
                    if ($cat != "" && $f != "" && $palabra != "") {
                        $sql = "SELECT * FROM $periodicos WHERE descripcion ILIKE '%$palabra%' AND categoria ILIKE '%$cat%' AND fpubli = '$f'";
                        filtros($sql, $link);
                    }

                    // Filtrar por categoría y palabra
                    if ($cat != "" && $f == "" && $palabra != "") {
                        $sql = "SELECT * FROM $periodicos WHERE descripcion ILIKE '%$palabra%' AND categoria ILIKE '%$cat%'";
                        filtros($sql, $link);
                    }

                    // Filtrar por fecha y palabra
                    if ($cat == "" && $f != "" && $palabra != "") {
                        $sql = "SELECT * FROM $periodicos WHERE descripcion ILIKE '%$palabra%' AND fpubli = '$f'";
                        filtros($sql, $link);
                    }

                    // Filtrar por palabra
                    if ($palabra != "" && $cat == "" && $f == "") {
                        $sql = "SELECT * FROM $periodicos WHERE descripcion ILIKE '%$palabra%'";
                        filtros($sql, $link);
                    }
                } else {
                    // Mostrar todos los registros de El País por defecto
                    $sql = "SELECT * FROM elpais ORDER BY fpubli DESC";
                    filtros($sql, $link);
                }
            }
            echo "</table>";
        ?>
    </body>
</html>