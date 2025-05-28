<?php
// Avvio la sessione e controllo permessi admin
session_start();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'Admin') {
    header('Location: index.php?error=permesso');
    exit;
}
// Includo la connessione al database
require_once 'connessione.php';
// Controllo che sia stato passato l'id del movimento da modificare
if (!isset($_GET['id'])) {
    header('Location: magazzino.php?error=1');
    exit;
}
$id_movimento = intval($_GET['id']);
// Recupero i dati del movimento da modificare
$stmt = $conn->prepare("SELECT * FROM movimenti_magazzino WHERE id_movimento = ?");
$stmt->bind_param("i", $id_movimento);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    $stmt->close();
    header('Location: magazzino.php?error=1');
    exit;
}
$movimento = $result->fetch_assoc();
$stmt->close();
// Recupero elenco libri per la select
$libri = $conn->query("SELECT id_libro, titolo FROM libri ORDER BY titolo ASC");
// Se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_libro = intval($_POST['id_libro']);
    $tipo_movimento = $_POST['tipo_movimento'];
    $quantita = intval($_POST['quantita']);
    $data_movimento = $_POST['data_movimento'];
    $descrizione = trim($_POST['descrizione']);
    // Validazione dati
    if ($id_libro && in_array($tipo_movimento, ['carico','scarico']) && $quantita > 0 && $data_movimento) {
        // Se è uno scarico, controllo che la quantità non superi la disponibilità (escludendo il movimento attuale)
        if ($tipo_movimento === 'scarico') {
            $q = $conn->prepare("SELECT IFNULL(SUM(CASE WHEN tipo_movimento='carico' THEN quantita ELSE -quantita END),0) as disp FROM movimenti_magazzino WHERE id_libro = ? AND id_movimento != ?");
            $q->bind_param("ii", $id_libro, $id_movimento);
            $q->execute();
            $q->bind_result($disponibilita);
            $q->fetch();
            $q->close();
            if ($quantita > $disponibilita) {
                echo "<div class='alert-error'>Impossibile scaricare: la quantità richiesta supera la disponibilità attuale ($disponibilita).</div>";
                $conn->close();
                exit;
            }
        }
        // Aggiorno il movimento nel database
        $stmt_upd = $conn->prepare("UPDATE movimenti_magazzino SET id_libro=?, tipo_movimento=?, quantita=?, data_movimento=?, descrizione=? WHERE id_movimento=?");
        $stmt_upd->bind_param("isissi", $id_libro, $tipo_movimento, $quantita, $data_movimento, $descrizione, $id_movimento);
        if ($stmt_upd->execute()) {
            // Aggiorna la tabella disponibilita dopo la modifica
            $q = $conn->prepare("SELECT IFNULL(SUM(CASE WHEN tipo_movimento='carico' THEN quantita WHEN tipo_movimento='scarico' THEN -quantita ELSE 0 END),0) FROM movimenti_magazzino WHERE id_libro = ?");
            $q->bind_param("i", $id_libro);
            $q->execute();
            $q->bind_result($nuova_quantita);
            $q->fetch();
            $q->close();
            $stmt_disp = $conn->prepare("INSERT INTO disponibilita (id_libro, quantita) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantita = ?");
            $stmt_disp->bind_param("iii", $id_libro, $nuova_quantita, $nuova_quantita);
            $stmt_disp->execute();
            $stmt_disp->close();
            // Aggiorna anche la colonna quantita nella tabella libri
            $stmt_libro = $conn->prepare("UPDATE movimenti_magazzino SET quantita = ? WHERE id_libro = ?");
            $stmt_libro->bind_param("ii", $nuova_quantita, $id_libro);
            $stmt_libro->execute();
            $stmt_libro->close();
            header('Location: magazzino.php?success=1');
        } else {
            header('Location: magazzino.php?error=1');
        }
        $stmt_upd->close();
        $conn->close();
        exit;
    } else {
        // Messaggio di errore se i dati non sono validi
        echo "<div class='alert-error'>Dati non validi.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    
    <title>Modifica Movimento Magazzino</title>
</head>
<body>
<?php require_once 'header.php'; ?>
<h1>Modifica Movimento di Magazzino</h1>
<form method="post" action="modificaMagazzino.php?id=<?php echo $id_movimento; ?>">
    <label for="id_libro">Libro:</label>
    <select name="id_libro" id="id_libro" required>
        <option value="">-- Seleziona libro --</option>
        <?php while ($row = $libri->fetch_assoc()) {
            $sel = ($row['id_libro'] == $movimento['id_libro']) ? 'selected' : '';
            echo "<option value='{$row['id_libro']}' $sel>{$row['titolo']}</option>";
        } ?>
    </select>
    <label for="tipo_movimento">Tipo movimento:</label>
    <select name="tipo_movimento" id="tipo_movimento" required>
        <option value="carico" <?php if($movimento['tipo_movimento']==='carico') echo 'selected'; ?>>Carico</option>
        <option value="scarico" <?php if($movimento['tipo_movimento']==='scarico') echo 'selected'; ?>>Scarico</option>
    </select>
    <label for="quantita">Quantità:</label>
    <input type="number" name="quantita" id="quantita" min="1" value="<?php echo $movimento['quantita']; ?>" required>
    <label for="data_movimento">Data movimento:</label>
    <input type="date" name="data_movimento" id="data_movimento" value="<?php echo $movimento['data_movimento']; ?>" required>
    <label for="descrizione">Descrizione (opzionale):</label>
    <textarea name="descrizione" id="descrizione" maxlength="255" rows="2" style="width:100%;margin-bottom:1rem;"><?php echo htmlspecialchars($movimento['descrizione']); ?></textarea>
    <button type="submit" class="bottone btn-add">Salva Modifiche</button>
</form>
<a href="magazzino.php" class="bottone">Torna al Magazzino</a>
</body>
</html>
