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
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-md mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Registra Nuovo Utente</h1>
    <?php if (isset($msg)) echo $msg; ?>
    <form method="post" action="registrazioneUtente.php" class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
        <label for="username" class="font-semibold text-blue-900">Username:</label>
        <input type="text" name="username" id="username" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="password" class="font-semibold text-blue-900">Password:</label>
        <input type="password" name="password" id="password" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="ruolo" class="font-semibold text-blue-900">Ruolo:</label>
        <select name="ruolo" id="ruolo" class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="Utente">Utente</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit" class="bg-green-700 hover:bg-green-900 text-white px-4 py-2 rounded transition">Registra Utente</button>
    </form>
    <a href="index.php" class="mt-6 inline-block bg-gray-400 hover:bg-gray-600 text-white px-4 py-2 rounded transition">Torna alla Home</a>
</main>
</body>
</html>
