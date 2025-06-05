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
    <link rel="stylesheet" href="styles.css">
    <title>Elenco Autori</title>
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Elenco Autori</h1>
    <div class="flex flex-wrap gap-3 items-center bg-white rounded-lg shadow px-4 py-3 mb-4">
        <a href="inserisciAutore.php" class="bg-green-600 hover:bg-green-800 text-white px-3 py-1 rounded transition">Inserisci un nuovo autore</a>
        <a href="esportaAutori.php" class="bg-blue-600 hover:bg-blue-800 text-white px-3 py-1 rounded transition">Esporta CSV</a>
        <input type="text" id="searchBar" placeholder="Cerca autore..." onkeyup="filterTable()" class="border border-gray-300 rounded px-3 py-1 focus:ring-blue-500 focus:border-blue-500" />
    </div>
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
        echo "<div class='overflow-x-auto rounded-lg shadow bg-white my-6'>";
        echo "<table id='autoriTable' class='min-w-full divide-y divide-gray-200'>";
        echo "<thead class='bg-gray-100'><tr>";
        echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'><a href='#' id='sortCodice'>Codice</a></th>";
        echo "<th class='px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase'><a href='#' id='sortNominativo'>Nominativo</a></th>";
        echo "<th class='px-4 py-2'></th><th class='px-4 py-2'></th><th class='px-4 py-2'></th></tr></thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr class='hover:bg-blue-50'>";
            echo "<td class='px-4 py-2'>".$row["id_autore"]."</td>";
            echo "<td class='px-4 py-2'>".$row["nominativo"]."</td>";
            echo "<td class='px-4 py-2'><a href='eliminaAutore.php?id=".$row["id_autore"]."' onclick=\"return confirm('Sei sicuro di voler eliminare questo autore?')\" class='bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded transition'>Elimina</a></td>";
            echo "<td class='px-4 py-2'><a href='modificaAutore.php?id_upd=".$row["id_autore"]."' class='bg-blue-600 hover:bg-blue-800 text-white px-3 py-1 rounded transition'>Modifica</a></td>";
            echo "<td class='px-4 py-2'><a href='dettagliAutore.php?id=".$row["id_autore"]."' class='bg-gray-400 hover:bg-gray-600 text-white px-3 py-1 rounded transition'>Dettagli</a></td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
        if ($totalPages > 1) {
            echo "<div class='flex flex-wrap gap-2 justify-center my-4'>";
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-blue-900';
                echo "<form style='display:inline;' method='get'><button type='submit' name='page' value='$i' class='px-3 py-1 rounded $active'>$i</button></form>";
            }
            echo "</div>";
        }
    } else {
        echo "<p class='text-center text-gray-500 mt-8'>Nessun risultato trovato.</p>";
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
</body>
</html>
