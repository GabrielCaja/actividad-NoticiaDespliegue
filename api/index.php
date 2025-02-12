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
require_once "conexionBBDD.php"; 

function filtros($sql, $link) {
    $result = pg_query($link, $sql);

    if (!$result) {
        echo "Error en la consulta SQL: " . pg_last_error($link);
        return;
    }

    while ($arrayFiltro = pg_fetch_assoc($result)) {
        echo "<tr>";              
        echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 20%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['titulo'] . "</td>";
        echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 40%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['contenido'] . "</td>";
        echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 20%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['descripcion'] . "</td>";                      
        echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 10%; overflow: hidden; text-overflow: ellipsis;'>" . $arrayFiltro['categoria'] . "</td>";                       
        echo "<td style='border: 1px solid #E4CCE8; padding: 8px; max-width: 10%; overflow: hidden; text-overflow: ellipsis;'><a href='" . $arrayFiltro['link'] . "' target='_blank'>" . $arrayFiltro['link'] . "</a></td>";                              
        $fecha = date_create($arrayFiltro['fpubli']);
        $fechaConversion = date_format($fecha, 'd-M-Y');
        echo "<td style='border: 1px solid #E4CCE8; padding: 8px;'>" . $fechaConversion . "</td>";
        echo "</tr>";  
    }

    // Cerrar la tabla HTML
    echo "</table>";
}

// Verifica si la conexión falló
if (!$link) {
    die("Conexión fallida: " . pg_last_error());
} else {
    echo "<table style='border: 5px #E4CCE8 solid;'>";
    echo "<tr><th><p style='color: #66E9D9;'>TITULO</p ></th><th><p style='color: #66E9D9;'>CONTENIDO</p ></th><th><p style='color: #66E9D9;'>DESCRIPCIÓN</p ></th><th><p style='color: #66E9D9;'>CATEGORÍA</p ></th><th><p style='color: #66E9D9;'>ENLACE</p ></th><th><p style='color: #66E9D9;'>FECHA DE PUBLICACIÓN</p ></th></tr><br>";

    if (isset($_GET['filtrar'])) {
        $cat = isset($_GET["categoria"]) ? $_GET["categoria"] : '';
        $fech = isset($_GET["fecha"]) ? date("Y-m-d", strtotime($_GET["fecha"])) : '';
        $palabra = isset($_GET["buscar"]) ? $_GET["buscar"] : '';

        $sql = "SELECT * FROM elpais";
        $conditions = [];

        if ($cat != "") {
            $conditions[] = "categoria ILIKE '%$cat%'";
        }
        if ($fech != '' && $fech != '1970-01-01') {
            $conditions[] = "fpubli = '$fech'";
        }
        if (!empty($palabra)) {
            $conditions[] = "descripcion ILIKE '%$palabra%'";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY fpubli DESC";

        filtros($sql, $link);
    } else {
        $sql = "SELECT * FROM elpais ORDER BY fpubli DESC LIMIT 20";
        filtros($sql, $link);
    }

    echo "</table>";
}

// Cerrar la conexión a la base de datos
pg_close($link);
?>
