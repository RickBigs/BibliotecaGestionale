<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'connessione.php';
$perPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Recupero le categorie possibili dall'ENUM della tabella libri
$categorie = [];
$res_enum = $conn->query("SHOW COLUMNS FROM libri LIKE 'categoria'");
if ($res_enum) {
    $row_enum = $res_enum->fetch_assoc();
    if (preg_match("/enum\\((.*)\\)/", $row_enum['Type'], $matches)) {
        $vals = explode(",", str_replace("'", "", $matches[1]));
        foreach ($vals as $val) {
            $categorie[] = trim($val);
        }
    }
}

// Filtro categoria
$categoriaFiltro = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$whereCategoria = ($categoriaFiltro && in_array($categoriaFiltro, $categorie)) ? "WHERE l.categoria = '".$conn->real_escape_string($categoriaFiltro)."'" : '';

// Query aggiornata con filtro categoria
$sqlCount = "SELECT COUNT(*) as total FROM libri l $whereCategoria";
$totalResult = $conn->query($sqlCount);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);
$sql = "SELECT l.id_libro, l.titolo, l.anno_stampa, l.prezzo, l.categoria, a.nominativo AS Autore, 
(SELECT IFNULL(SUM(CASE WHEN tipo_movimento='carico' THEN quantita ELSE -quantita END),0) FROM movimenti_magazzino m WHERE m.id_libro = l.id_libro) AS Quantita FROM libri l 
INNER JOIN autori a ON l.id_autore = a.id_autore 
$whereCategoria
ORDER BY l.titolo ASC LIMIT $perPage OFFSET $offset";
$result = $conn->query($sql);
if ($result === false) {
    die("Errore nella query: " . $conn->error);
}
?>

<?php
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<div class='alert-success'>Libro eliminato con successo.</div>";
}
if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo "<div class='alert-error'>Errore durante l'eliminazione del libro.</div>";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elenco Libri</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>
    <h1>Elenco Libri</h1>
    <a href="inserisciLibro.php" class="bottone btn-add">Inserisci un nuovo libro</a>
    <a href="esportaLibri.php" class="bottone btn-add">Esporta Libri CSV</a>
    <input type="text" id="searchBar" placeholder="Cerca libro o autore..." onkeyup="filterTable()">
<div class="filtra-categoria-wrapper">
    <form method="get">
        <label for="categoria">Filtra per categoria:</label>
        <select name="categoria" id="categoria" onchange="this.form.submit()">
            <option value="">Tutte</option>
            <?php foreach ($categorie as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($categoriaFiltro==$cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if($categoriaFiltro): ?>
            <a href="libri.php" class="bottone">Azzera filtro</a>
        <?php endif; ?>
    </form>
</div>
<?php
if ($result->num_rows > 0) {
    echo '<table id="libriTable"><tr>';
    echo '<th><a href="#" id="sortCodice" style="color:inherit;text-decoration:underline;cursor:pointer;">Codice</a></th>';
    echo '<th><a href="#" id="sortTitolo" style="color:inherit;text-decoration:underline;cursor:pointer;">Titolo</a></th>';
    echo '<th><a href="#" id="sortAutore" style="color:inherit;text-decoration:underline;cursor:pointer;">Autore</a></th>';
    echo '<th><a href="#" id="sortAnno" style="color:inherit;text-decoration:underline;cursor:pointer;">Anno di stampa</a></th>';
    echo '<th>Categoria</th>';
    echo '<th>Prezzo</th>';
    echo '<th>Quantità</th>';
    echo '<th>Elimina</th>';
    echo '<th>Modifica</th>';
    echo '<th>Dettagli</th>';
    echo '</tr>';
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id_libro']}</td><td>{$row['titolo']}</td><td>{$row['Autore']}</td><td>{$row['anno_stampa']}</td><td>{$row['categoria']}</td><td>{$row['prezzo']}</td><td>{$row['Quantita']}</td><td><a href='eliminaLibro.php?id={$row['id_libro']}' class='bottone-elimina' onclick=\"return confirm('Sei sicuro di voler eliminare questo libro?')\">Elimina</a></td><td><a href='modificaLibro.php?id_upd={$row['id_libro']}' class='bottone'>Modifica</a></td><td><a href='dettagliLibro.php?id={$row['id_libro']}' class='bottone'>Dettagli</a></td></tr>";
    }
    echo "</table>";
    if ($totalPages > 1) {
        echo "<div class='pagination'>";
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i == $page) ? 'active' : '';
            echo 
            "<form style='display:inline;' method='get'>
            <button type='submit' name='page' value='$i' class='$active'>$i
            </button>
            </form>";
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
    var table = document.getElementById('libriTable');
    var tr = table.getElementsByTagName('tr');
    for (var i = 1; i < tr.length; i++) {
        var titolo = tr[i].getElementsByTagName('td')[1];
        var autore = tr[i].getElementsByTagName('td')[2];
        var txtValue = (titolo ? titolo.textContent : '') + ' ' + (autore ? autore.textContent : '');
        tr[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? '' : 'none';
    }
}
// Ordinamento tabella per colonne
let sortDirections = { codice: true, titolo: true, autore: true, anno: true };
function sortTableByCol(colIdx, dirKey) {
    let table = document.getElementById('libriTable');
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
document.getElementById('sortTitolo').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(1, 'titolo'); });
document.getElementById('sortAutore').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(2, 'autore'); });
document.getElementById('sortAnno').addEventListener('click', function(e) { e.preventDefault(); sortTableByCol(3, 'anno'); });
</script>
<?php $conn->close(); ?>
</body>
</html>
