<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'connessione.php';
$sql = "SELECT autori.id_autore AS id_autore, autori.nominativo AS nominativo FROM autori ORDER BY autori.nominativo ASC";
$result = $conn->query($sql);
?>
<?php
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<div class='alert-success'>✅ Operazione completata con successo.</div>";
}
if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo "<div class='alert-error'>❌ Errore durante l'operazione.</div>";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Autori</title>
    <style>
    #searchBar { margin-bottom: 1rem; padding: 0.5rem 1rem; width: 100%; max-width: 400px; border: 1px solid #ccc; border-radius: 5px; }
    .pagination { display: flex; justify-content: center; margin: 1rem 0; gap: 0.5rem; }
    .pagination button { background: #2d5f5d; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
    .pagination button.active, .pagination button:hover { background: #1c3938; }
    .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin-bottom: 15px; }
    .alert-error { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>
    <h1>Elenco Autori</h1>
    <a href="inserisciAutore.php" class="bottone btn-add">Inserisci un nuovo autore</a>
    <a href="esportaAutori.php" class="bottone btn-add">Esporta CSV</a>
    <input type="text" id="searchBar" placeholder="Cerca autore..." onkeyup="filterTable()">
    <?php
    $perPage = 10;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($page - 1) * $perPage;
    $sqlCount = "SELECT COUNT(*) as total FROM autori";
    $totalResult = $conn->query($sqlCount);
    $totalRows = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $perPage);
    $sql = "SELECT id_autore, nominativo FROM autori ORDER BY nominativo ASC LIMIT $perPage OFFSET $offset";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        echo "<div class='table-wrapper'>";
        echo "<table id='autoriTable'>";
        echo "<tr>";
        echo "<th><a href='#' id='sortCodice' style='color:inherit;text-decoration:underline;cursor:pointer;'>Codice</a></th>";
        echo "<th><a href='#' id='sortNominativo' style='color:inherit;text-decoration:underline;cursor:pointer;'>Nominativo</a></th>";
        echo "<th>Elimina</th><th>Modifica</th><th>Dettagli</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>".$row["id_autore"]."</td><td>".$row["nominativo"]."</td><td><a href='eliminaAutore.php?id=".$row["id_autore"]."' onclick=\"return confirm('Sei sicuro di voler eliminare questo autore?')\" class='bottone-elimina'>Elimina</a></td><td><a href='modificaAutore.php?id_upd=".$row["id_autore"]."' class='bottone'>Modifica</a></td><td><a href='dettagliAutore.php?id=".$row["id_autore"]."' class='bottone'>Dettagli</a></td></tr>";
        }
        echo "</table>";
        echo "</div>";
        if ($totalPages > 1) {
            echo "<div class='pagination'>";
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<form style='display:inline;' method='get'><button type='submit' name='page' value='$i' class='$active'>$i</button></form>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>Nessun risultato trovato.</p>";
    }
    ?>

    <script>
    function filterTable() {
        var input = document.getElementById('searchBar');
        var filter = input.value.toUpperCase();
        var table = document.getElementById('autoriTable');
        var tr = table.getElementsByTagName('tr');
        for (var i = 1; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName('td')[1];
            if (td) {
                var txtValue = td.textContent || td.innerText;
                tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
            }
        }
    }
    let sortDirections = { codice: true, nominativo: true };
function sortTableByCol(colIdx, dirKey) {
    let table = document.getElementById('autoriTable');
    let rows = Array.from(table.rows).slice(1);
    rows.sort(function(a, b) {
        let aText = a.cells[colIdx].textContent.trim().toLowerCase();
        let bText = b.cells[colIdx].textContent.trim().toLowerCase();
        if (!isNaN(aText) && !isNaN(bText)) {
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
document.getElementById('sortNominativo').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(1, 'nominativo'); });
    </script>
<?php $conn->close(); ?>
</body>
</html>
