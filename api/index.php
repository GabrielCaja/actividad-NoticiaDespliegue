<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Formulario de Filtro</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        img {
            max-width: 100%; /* Ajusta la imagen al tamaño del contenedor */
            height: auto;
            display: block;
            margin-bottom: 10px;
        }
        a {
            color: #1a0dab; /* Color de los enlaces */
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        p {
            margin-bottom: 20px;
        }
        i {
            font-style: italic;
        }
    </style>

</head>
<body>
    <form action="index.php">
        <fieldset>
            <legend>FILTRO</legend>

            <label>PERIODICO : </label>
            <select name="periodicos">
                <option value="elpais">El Pais</option>
                <option value="elmundo">El Mundo</option>
            </select>

            <label>CATEGORIA : </label>
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
            <input type="date" name="fecha">

            <label style="margin-left: 5vw;">AMPLIAR FILTRO (la descripción contenga la palabra) : </label>
            <input type="text" name="buscar">

            <input type="submit" name="filtrar" value="Filtrar">
        </fieldset>
    </form>
</body>
</html>

<?php
    require_once "RSSElPais.php";
    require_once "RSSElMundo.php";
    require_once "conexionBBDD.php";

    function filtros($sql, $link, $params)
    {
        $result = pg_query_params($link, $sql, $params);

        if (! $result) {
            echo "Error en la consulta SQL: " . pg_last_error($link);
            return;
        }

        while ($arrayFiltro = pg_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . htmlspecialchars($arrayFiltro['titulo']) . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . htmlspecialchars($arrayFiltro['contenido']) . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . htmlspecialchars($arrayFiltro['descripcion']) . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . htmlspecialchars($arrayFiltro['categoria']) . "</td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'><a href='" . htmlspecialchars($arrayFiltro['link']) . "' target='_blank'>Enlace</a></td>";
            echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . date('d-M-Y', strtotime($arrayFiltro['fpubli'])) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    }

    if (! $link) {
        die("Conexión fallida: " . pg_last_error());
    } else {
        echo "<table style='border: 5px #E4CCE8 solid;'>
            <tr><th>TITULO</th><th>CONTENIDO</th><th>DESCRIPCIÓN</th><th>CATEGORÍA</th><th>ENLACE</th><th>FECHA DE PUBLICACIÓN</th></tr>";

        if (isset($_GET['filtrar'])) {
            $params     = [];
            $conditions = [];

            if (! empty($_GET["categoria"])) {
                $conditions[] = "categoria ILIKE $" . (count($params) + 1);
                $params[]     = "%" . $_GET["categoria"] . "%";
            }
            if (! empty($_GET["fecha"])) {
                $conditions[] = "fpubli = $" . (count($params) + 1);
                $params[]     = date("Y-m-d", strtotime($_GET["fecha"]));
            }
            if (! empty($_GET["buscar"])) {
                $conditions[] = "descripcion ILIKE $" . (count($params) + 1);
                $params[]     = "%" . $_GET["buscar"] . "%";
            }

            $sql = "SELECT * FROM elpais UNION SELECT * FROM elmundo";
            if (! empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
            $sql .= " ORDER BY fpubli DESC";

            filtros($sql, $link, $params);
        } else {
            $sql = "SELECT * FROM elpais UNION SELECT * FROM elmundo ORDER BY fpubli DESC LIMIT 20";
            filtros($sql, $link, []);
        }

        echo "</table>";
    }

    pg_close($link);
?>
