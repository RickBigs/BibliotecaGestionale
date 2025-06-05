<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Controllo se l'utente è autenticato, altrimenti reindirizzo alla pagina di login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Includo la connessione al database
require_once 'connessione.php';

// 1. Controllo se è stato passato l'id dell'autore da modificare
if (!isset($_GET['id_upd'])) {
    echo "<p>Errore: ID autore non specificato.</p>";
    exit;
}

$id_autore = (int)$_GET['id_upd'];

// 2. Recupero i dati dell'autore da modificare
$stmt = $conn->prepare("SELECT * FROM autori WHERE id_autore = ?");
$stmt->bind_param("i", $id_autore);
$stmt->execute();
$result_autore = $stmt->get_result();

if ($result_autore->num_rows != 1) {
    echo "<p>Errore: autore non trovato.</p>";
    exit;
}

$autore = $result_autore->fetch_assoc();

// 3. Se è stato inviato il form di modifica
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nominativo = trim($_POST["nominativo"]);
    $descrizione = isset($_POST["descrizione"]) ? trim($_POST["descrizione"]) : null;

    // Controllo se il nominativo è vuoto
    if ($nominativo === "") {
        header('Location: autori.php?error=1');
        exit;
    }

    // Query di aggiornamento autore
    $stmt_upd = $conn->prepare("UPDATE autori SET nominativo = ?, descrizione = ? WHERE id_autore = ?");
    $stmt_upd->bind_param("ssi", $nominativo, $descrizione, $id_autore);

    if ($stmt_upd->execute()) {
        header('Location: autori.php?success=1');
    } else {
        header('Location: autori.php?error=1');
    }

    $stmt_upd->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <title>Modifica Autore</title>
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Modifica Autore</h1>
    <form method="post" action="modificaAutore.php?id_upd=<?php echo $id_autore; ?>" class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
        <label for="nominativo" class="font-semibold text-blue-900">Nominativo Autore:</label>
        <input type="text" name="nominativo" id="nominativo" value="<?php echo htmlspecialchars($autore['nominativo']); ?>" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="descrizione" class="font-semibold text-blue-900">Storia/Breve descrizione:</label>
        <textarea name="descrizione" id="descrizione" maxlength="255" rows="3" class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"><?php echo isset($autore['descrizione']) ? htmlspecialchars($autore['descrizione']) : ''; ?></textarea>
        <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">Salva Modifiche</button>
    </form>
</main>
</body>
</html>
