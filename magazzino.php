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
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movimenti Magazzino</title>
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<h1 class="text-2xl font-bold text-blue-900 mb-6">Movimenti di Magazzino</h1>
<div class="flex flex-wrap gap-3 items-center bg-white rounded-lg shadow px-4 py-3 mb-4">
    <a href="inserisciMovimento.php" class="bg-green-600 hover:bg-green-800 text-white px-3 py-1 rounded transition">Nuovo Movimento Magazzino</a>
    <input type="text" id="searchBar" placeholder="Cerca libro..." onkeyup="filterTable()" class="border border-gray-300 rounded px-3 py-1 focus:ring-blue-500 focus:border-blue-500" />
</div>
<?php
if ($result->num_rows > 0) {
    echo "<div class='overflow-x-auto rounded-lg shadow bg-white my-6'>";
    echo "<table id='magazzinoTable' class='min-w-full divide-y divide-gray-200'>";
    echo "<thead class='bg-gray-100'><tr>";
    echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'><a href='#' id='sortCodice'>Codice</a></th>";
    echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'><a href='#' id='sortLibro'>Libro</a></th>";
    echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'><a href='#' id='sortTipo'>Tipo</a></th>";
    echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'>Quantità</th>";
    echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'><a href='#' id='sortData'>Data</a></th>";
    echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'>Descrizione</a></th>";
    echo "<th class='px-4 py-2'></th></tr></thead><tbody>";
    while ($row = $result->fetch_assoc()) {
        $classeTipo = ($row["tipo_movimento"] === 'Scarico') ? 'text-green-700 font-bold' : 'text-red-700 font-bold';
        $classeRiga = ($row["tipo_movimento"] === 'Scarico') ? 'bg-green-50' : 'bg-red-50';
        echo "<tr class='hover:bg-blue-50 $classeRiga'>";
        echo "<td class='px-4 py-2'>".$row["id_movimento"]."</td>";
        echo "<td class='px-4 py-2'>".$row["titolo"]."</td>";
        echo "<td class='px-4 py-2 $classeTipo'>".htmlspecialchars($row["tipo_movimento"])."</td>";
        echo "<td class='px-4 py-2'>".$row["quantita"]."</td>";
        echo "<td class='px-4 py-2'>".$row["data_movimento"]."</td>";
        echo "<td class='px-4 py-2'>".htmlspecialchars($row["descrizione"])."</td>";
        echo "<td class='px-4 py-2 flex gap-2'>";
        echo "<a href='modificaMagazzino.php?id=".$row["id_movimento"]."' class='bg-blue-600 hover:bg-blue-800 text-white px-3 py-1 rounded transition'>Modifica</a> ";
        echo "<a href='eliminaMovimento.php?id=".$row["id_movimento"]."' class='bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded transition' onclick=\"return confirm('Sei sicuro di voler eliminare questo movimento?')\">Elimina</a>";
        echo "</td></tr>";
    }
    echo "</tbody></table></div>";
} else {
    echo "<p class='text-center text-gray-500 mt-8'>Nessun movimento registrato.</p>";
}
?>
<script>
function filterTable() {
    var input = document.getElementById('searchBar');
    var filter = input.value.toUpperCase();
    var table = document.getElementById('magazzinoTable');
    var tr = table.getElementsByTagName('tr');
    for (var i = 1; i < tr.length; i++) {
        var titolo = tr[i].getElementsByTagName('td')[1];
        var txtValue = (titolo ? titolo.textContent : '');
        tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
    }
}
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
