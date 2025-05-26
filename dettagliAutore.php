<?php
// Avvio la sessione per gestire l'autenticazione
session_start();
// Includo la connessione al database
require_once 'connessione.php';

// Verifico se l'ID dell'autore è stato passato tramite GET
if (!isset($_GET['id'])) {
    echo "<p>Errore: ID autore non specificato.</p>";
    exit;
}

// Recupero l'ID dell'autore dalla query string
$id_autore = (int)$_GET['id'];

// Preparo la query per selezionare i dettagli dell'autore
$sql = "SELECT * FROM autori WHERE id_autore = $id_autore";
$result = $conn->query($sql);

// Verifico se è stato trovato un autore con quell'ID
if ($result->num_rows != 1) {
    echo "<p>Autore non trovato.</p>";
    exit;
}

// Recupero i dati dell'autore
$autore = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettagli Autore</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>
<h1>Dettagli Autore</h1>
<div class="table-wrapper">
<table id="dettagliAutoreTable">
    <tr><th>Codice</th><td><?php echo $autore['id_autore']; ?></td></tr>
    <tr><th>Nominativo</th><td><?php echo htmlspecialchars($autore['nominativo']); ?></td></tr>
    <?php
    // Mostro la descrizione dell'autore se disponibile
    if (isset($autore['descrizione'])) {
        echo '<tr><th>Storia</th><td>' . htmlspecialchars($autore['descrizione']) . '</td></tr>';
    }
    ?>
</table>
</div>
<a href="autori.php" class="bottone">Torna all'elenco autori</a>
</body>
</html>
