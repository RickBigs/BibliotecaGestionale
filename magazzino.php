<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Reindirizzo alla pagina di login se l'utente non è autenticato
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Includo la connessione al database
require_once 'connessione.php';

// Query per unire movimenti + titoli dei libri
$sql = "SELECT m.id_movimento, l.titolo, m.tipo_movimento, m.quantita, m.data_movimento, m.descrizione
        FROM movimenti_magazzino m
        INNER JOIN libri l ON m.id_libro = l.id_libro
        ORDER BY m.data_movimento DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movimenti Magazzino</title>

<style>
</style>
</head>
<body>

<?php require_once 'header.php'; ?>

<h1>Movimenti di Magazzino</h1>

<a href="inserisciMovimento.php" class="bottone btn-add">Nuovo Movimento Magazzino</a>

<?php

if ($result->num_rows > 0) {
    echo "<table id='magazzinoTable'>";
    echo "<tr>";
    echo "<th><a href='#' id='sortCodice' style='color:inherit;text-decoration:underline;cursor:pointer;'>Codice</a></th>";
    echo "<th><a href='#' id='sortLibro' style='color:inherit;text-decoration:underline;cursor:pointer;'>Libro</a></th>";
    echo "<th><a href='#' id='sortTipo' style='color:inherit;text-decoration:underline;cursor:pointer;'>Tipo</a></th>";
    echo "<th>Quantità</th>";
    echo "<th><a href='#' id='sortData' style='color:inherit;text-decoration:underline;cursor:pointer;'>Data</a></th>";
    echo "<th>Descrizione</th>";
    echo "<th>Azioni</th>";
    echo "</tr>";
    while ($row = $result->fetch_assoc()) {
        $classeTipo = ($row["tipo_movimento"] === 'Carico') ? 'testo-verde' : 'testo-rosso';
        $classeRiga = ($row["tipo_movimento"] === 'Carico') ? 'riga-verde' : 'riga-rosso';
        echo "<tr class='$classeRiga'>
                <td>".$row["id_movimento"]."</td>
                <td>".$row["titolo"]."</td>
                <td class='$classeTipo'>".htmlspecialchars($row["tipo_movimento"])."</td>
                <td>".$row["quantita"]."</td>
                <td>".$row["data_movimento"]."</td>
                <td>".htmlspecialchars($row["descrizione"])."</td>
                <td>
                    <a href='modificaMagazzino.php?id=".$row["id_movimento"]."' class='bottone'>Modifica</a>
                    <a href='eliminaMovimento.php?id=".$row["id_movimento"]."' class='bottone-elimina' onclick=\"return confirm('Sei sicuro di voler eliminare questo movimento?')\">Elimina</a>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nessun movimento registrato.</p>";
}
?>

<?php $conn->close(); ?>

<script>
let sortDirections = { codice: true, libro: true, tipo: true, data: true };
function sortTableByCol(colIdx, dirKey) {
    let table = document.getElementById('magazzinoTable');
    let rows = Array.from(table.rows).slice(1);
    rows.sort(function(a, b) {
        let aText = a.cells[colIdx].textContent.trim().toLowerCase();
        let bText = b.cells[colIdx].textContent.trim().toLowerCase();
        if (!isNaN(Date.parse(aText)) && !isNaN(Date.parse(bText))) {
            aText = Date.parse(aText); bText = Date.parse(bText);
        } else if (!isNaN(aText) && !isNaN(bText)) {
            aText = parseFloat(aText); bText = parseFloat(bText);
        }
        if (aText < bText) return sortDirections[dirKey] ? -1 : 1;
        if (aText > bText) return sortDirections[dirKey] ? 1 : -1;
        return 0;
    });
    sortDirections[dirKey] = !sortDirections[dirKey];
    for (let row of rows) table.tBodies[0].appendChild(row);
}
document.getElementById('sortCodice').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(0, 'codice'); });
document.getElementById('sortLibro').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(1, 'libro'); });
document.getElementById('sortTipo').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(2, 'tipo'); });
document.getElementById('sortData').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(4, 'data'); });
</script>

</body>
</html>
