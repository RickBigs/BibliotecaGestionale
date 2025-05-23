<?php
session_start();
require_once 'connessione.php';
$stmt = $conn->prepare("SELECT id FROM utenti WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt2 = $conn->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $username, $hash, $ruolo);
    $stmt2->execute();
    $stmt2->close();
}
$stmt->close();
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'Admin') {
    header('Location: index.php?error=permesso');
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ruolo = $_POST['ruolo'] === 'Admin' ? 'Admin' : 'Utente';
    if ($username && $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO utenti (username, password, ruolo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hash, $ruolo);
        if ($stmt->execute()) {
            $msg = "<div class='alert-success'>Utente registrato con successo!</div>";
        } else {
            $msg = "<div class='alert-error'>Errore durante la registrazione: " . htmlspecialchars($conn->error) . "</div>";
        }
        $stmt->close();
    } else {
        $msg = "<div class='alert-error'>Compila tutti i campi.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Registrazione Utente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>
<h1>Registra Nuovo Utente</h1>
<?php if (isset($msg)) echo $msg; ?>
<form method="post" action="registrazioneUtente.php" style="max-width:400px;margin:auto;">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required>
    <label for="ruolo">Ruolo:</label>
    <select name="ruolo" id="ruolo">
        <option value="Utente">Utente</option>
        <option value="Admin">Admin</option>
    </select>
    <button type="submit" class="bottone btn-add">Registra Utente</button>
</form>
<a href="index.php" class="bottone">Torna alla Home</a>
</body>
</html>
