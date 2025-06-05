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
$whereConditions = [];

if ($categoriaFiltro && in_array($categoriaFiltro, $categorie)) {
    $whereConditions[] = "l.categoria = '".$conn->real_escape_string($categoriaFiltro)."'";
}

// Filtro ricerca per titolo/autore
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchTerm) {
    $searchEscaped = $conn->real_escape_string($searchTerm);
    $whereConditions[] = "(l.titolo LIKE '%$searchEscaped%' OR a.nominativo LIKE '%$searchEscaped%')";
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Query aggiornata con filtri
$sqlCount = "SELECT COUNT(*) as total FROM libri l INNER JOIN autori a ON l.id_autore = a.id_autore $whereClause";
$totalResult = $conn->query($sqlCount);
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $perPage);

$sql = "SELECT l.id_libro, l.titolo, l.anno_stampa, l.prezzo, l.categoria, a.nominativo AS Autore, 
(SELECT IFNULL(SUM(CASE WHEN tipo_movimento='carico' THEN quantita ELSE -quantita END),0) FROM movimenti_magazzino m WHERE m.id_libro = l.id_libro) AS Quantita 
FROM libri l 
INNER JOIN autori a ON l.id_autore = a.id_autore 
$whereClause
ORDER BY l.titolo ASC LIMIT $perPage OFFSET $offset";

$result = $conn->query($sql);
if ($result === false) {
    die("Errore nella query: " . $conn->error);
}
?>

<?php
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<div class='alert-success'>✅ Libro eliminato con successo.</div>";
}
if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo "<div class='alert-error'>❌ Errore durante l'eliminazione del libro.</div>";
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
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Elenco Libri</h1>
    
    <!-- Controlli di ricerca e filtri -->
    <div class="bg-white rounded-lg shadow px-4 py-3 mb-4">
        <form method="get" class="flex flex-wrap items-center gap-3">
            
            <!-- Campo di ricerca -->
            <div class="flex-1 min-w-200">
                <label for="search" class="font-semibold text-gray-700 block mb-1">Cerca libro:</label>
                <input type="text" 
                       name="search" 
                       id="search" 
                       value="<?php echo htmlspecialchars($searchTerm); ?>" 
                       placeholder="Cerca per titolo o autore..." 
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
            </div>
            
            <!-- Filtro categoria -->
            <div>
                <label for="categoria" class="font-semibold text-gray-700 block mb-1">Categoria:</label>
                <select name="categoria" id="categoria" class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tutte</option>
                    <?php foreach ($categorie as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($categoriaFiltro==$cat) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Pulsanti azione -->
            <div class="flex gap-2 items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">🔍 Cerca</button>
                <a href="libri.php" class="bg-gray-400 hover:bg-gray-600 text-white px-4 py-2 rounded transition">🔄 Reset</a>
            </div>
        </form>
        
        <!-- Pulsante aggiungi libro -->
        <div class="mt-3 pt-3 border-t border-gray-200">
            <a href="inserisciLibro.php" class="bg-green-600 hover:bg-green-800 text-white px-4 py-2 rounded transition inline-block">➕ Inserisci nuovo libro</a>
            <a href="esportaLibri.php" class="bg-yellow-600 hover:bg-yellow-800 text-white px-4 py-2 rounded transition inline-block ml-2">📄 Esporta CSV</a>
        </div>
    </div>
    
    <!-- Tabella risultati -->
    <div class="overflow-x-auto rounded-lg shadow bg-white my-6">
        <table id="libriTable" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">
                        <a href="#" id="sortCodice" class="hover:text-blue-900">Codice ↕</a>
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">
                        <a href="#" id="sortTitolo" class="hover:text-blue-900">Titolo ↕</a>
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">
                        <a href="#" id="sortAutore" class="hover:text-blue-900">Autore ↕</a>
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">
                        <a href="#" id="sortAnno" class="hover:text-blue-900">Anno ↕</a>
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Categoria</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Prezzo</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Quantità</th>
                    <th class="px-4 py-2 text-center">Azioni</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-blue-50">
                        <td class="px-4 py-2"><?php echo $row['id_libro']; ?></td>
                        <td class="px-4 py-2 font-medium"><?php echo htmlspecialchars($row['titolo']); ?></td>
                        <td class="px-4 py-2"><?php echo htmlspecialchars($row['Autore']); ?></td>
                        <td class="px-4 py-2"><?php echo $row['anno_stampa']; ?></td>
                        <td class="px-4 py-2">
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                <?php echo htmlspecialchars($row['categoria']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-2">€ <?php echo number_format($row['prezzo'], 2); ?></td>
                        <td class="px-4 py-2">
                            <span class="font-semibold <?php echo $row['Quantita'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo $row['Quantita']; ?>
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex gap-1 justify-center">
                                <a href="dettagliLibro.php?id=<?php echo $row['id_libro']; ?>" 
                                   class="bg-gray-400 hover:bg-gray-600 text-white px-2 py-1 rounded transition text-xs"
                                   title="Dettagli">👁️</a>
                                <a href="modificaLibro.php?id_upd=<?php echo $row['id_libro']; ?>" 
                                   class="bg-blue-600 hover:bg-blue-800 text-white px-2 py-1 rounded transition text-xs"
                                   title="Modifica">✏️</a>
                                <a href="eliminaLibro.php?id=<?php echo $row['id_libro']; ?>" 
                                   class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded transition text-xs"
                                   onclick="return confirm('Sei sicuro di voler eliminare questo libro?')"
                                   title="Elimina">🗑️</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <?php if ($searchTerm || $categoriaFiltro): ?>
                            Nessun libro trovato con i criteri di ricerca specificati.
                            <br><a href="libri.php" class="text-blue-600 hover:underline">Mostra tutti i libri</a>
                        <?php else: ?>
                            Nessun libro presente nel database.
                            <br><a href="inserisciLibro.php" class="text-blue-600 hover:underline">Inserisci il primo libro</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginazione -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php 
                $active = ($i == $page) ? 'active' : '';
                $params = http_build_query(array_filter([
                    'page' => $i,
                    'search' => $searchTerm,
                    'categoria' => $categoriaFiltro
                ]));
                ?>
                <a href="libri.php?<?php echo $params; ?>" class="<?php echo $active; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
    
    <!-- Riepilogo risultati -->
    <div class="text-center text-gray-600 mt-4">
        Visualizzati <?php echo $result->num_rows; ?> libri di <?php echo $totalRows; ?> totali
        <?php if ($searchTerm): ?>
            per la ricerca "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
        <?php endif; ?>
        <?php if ($categoriaFiltro): ?>
            nella categoria "<strong><?php echo htmlspecialchars($categoriaFiltro); ?></strong>"
        <?php endif; ?>
    </div>
</main>

<script>
// Ordinamento tabella
let sortDirections = { codice: true, titolo: true, autore: true, anno: true };

function sortTableByCol(colIdx, dirKey) {
    let table = document.getElementById('libriTable');
    let rows = Array.from(table.rows).slice(1);
    
    rows.sort(function(a, b) {
        let aText = a.cells[colIdx].textContent.trim().toLowerCase();
        let bText = b.cells[colIdx].textContent.trim().toLowerCase();
        
        // Se sono numeri, convertili
        if (!isNaN(aText) && !isNaN(bText)) {
            aText = parseFloat(aText); 
            bText = parseFloat(bText);
        }
        
        if (aText < bText) return sortDirections[dirKey] ? -1 : 1;
        if (aText > bText) return sortDirections[dirKey] ? 1 : -1;
        return 0;
    });
    
    sortDirections[dirKey] = !sortDirections[dirKey];
    
    // Riordina le righe
    for (let row of rows) {
        table.tBodies[0].appendChild(row);
    }
}

// Event listeners per ordinamento
document.getElementById('sortCodice').addEventListener('click', function(e) { 
    e.preventDefault(); 
    sortTableByCol(0, 'codice'); 
});
document.getElementById('sortTitolo').addEventListener('click', function(e) { 
    e.preventDefault(); 
    sortTableByCol(1, 'titolo'); 
});
document.getElementById('sortAutore').addEventListener('click', function(e) { 
    e.preventDefault(); 
    sortTableByCol(2, 'autore'); 
});
document.getElementById('sortAnno').addEventListener('click', function(e) { 
    e.preventDefault(); 
    sortTableByCol(3, 'anno'); 
});

// Ricerca in tempo reale (opzionale)
document.getElementById('search').addEventListener('input', function() {
    // Potresti implementare una ricerca AJAX qui se vuoi
});
</script>

<?php $conn->close(); ?>
</body>
</html>