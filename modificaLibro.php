<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Includo la connessione al database
require_once 'connessione.php';

// 1. Controllo se è stato passato l'id del libro da modificare
if (!isset($_GET['id_upd'])) {
    echo "<p>Errore: ID libro non specificato.</p>";
    exit;
}

$id_libro = (int)$_GET['id_upd'];

// 2. Recupero i dati del libro da modificare
$sql_libro = "SELECT * FROM libri WHERE id_libro = $id_libro";
$result_libro = $conn->query($sql_libro);

if ($result_libro->num_rows != 1) {
    echo "<p>Errore: libro non trovato.</p>";
    exit;
}

$libro = $result_libro->fetch_assoc();

// 3. Recupero autori per il menù a tendina
$sql_autori = "SELECT id_autore, nominativo FROM autori ORDER BY nominativo ASC";
$result_autori = $conn->query($sql_autori);

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

// 4. Se è stato inviato il form di modifica
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titolo = trim($_POST["titolo"]);
    $id_autore = intval($_POST["id_autore"]);
    $anno_stampa = intval($_POST["anno_stampa"]);
    $prezzo = floatval($_POST["prezzo"]);
    $categoria = $_POST["categoria"];
    $trama = isset($_POST["trama"]) ? trim($_POST["trama"]) : null;
    if ($titolo === "" || !$id_autore || $categoria === "") {
        header('Location: libri.php?error=1');
        exit;
    }
    $stmt_upd = $conn->prepare("UPDATE libri SET titolo = ?, id_autore = ?, anno_stampa = ?, prezzo = ?, categoria = ?, trama = ? WHERE id_libro = ?");
    $stmt_upd->bind_param("siidssi", $titolo, $id_autore, $anno_stampa, $prezzo, $categoria, $trama, $id_libro);
    if ($stmt_upd->execute()) {
        header('Location: libri.php');
    } else {
        header('Location: libri.php?error=1');
    }
    $stmt_upd->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylea.css">
    <title>Modifica Libro</title>
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Modifica Libro</h1>
    <form method="post" action="modificaLibro.php?id_upd=<?php echo $id_libro; ?>" class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
        <label for="titolo" class="font-semibold text-blue-900">Titolo:</label>
        <input type="text" name="titolo" id="titolo" value="<?php echo htmlspecialchars($libro['titolo']); ?>" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="id_autore" class="font-semibold text-blue-900">Autore:</label>
        <select name="id_autore" id="id_autore" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Seleziona un autore --</option>
            <?php
            if ($result_autori->num_rows > 0) {
                while ($autore = $result_autori->fetch_assoc()) {
                    $selected = ($autore["id_autore"] == $libro["id_autore"]) ? "selected" : "";
                    echo "<option value='".$autore["id_autore"]."' $selected>".$autore["nominativo"]."</option>";
                }
            }
            ?>
        </select>
        <label for="categoria" class="font-semibold text-blue-900">Categoria:</label>
        <select name="categoria" id="categoria" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Seleziona una categoria --</option>
            <?php foreach ($categorie as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($libro['categoria'] == $cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="anno_stampa" class="font-semibold text-blue-900">Anno di stampa:</label>
        <input type="number" name="anno_stampa" id="anno_stampa" value="<?php echo $libro['anno_stampa']; ?>" min="1000" max="2100" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="prezzo" class="font-semibold text-blue-900">Prezzo (€):</label>
        <input type="number" step="0.01" name="prezzo" id="prezzo" value="<?php echo $libro['prezzo']; ?>" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="trama" class="font-semibold text-blue-900">Trama (max 255 caratteri):</label>
        <textarea name="trama" id="trama" maxlength="255" rows="3" class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"><?php echo isset($libro['trama']) ? htmlspecialchars($libro['trama']) : ''; ?></textarea>
        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">Salva Modifiche</button>
    </form>
</main>
</body>
</html>
