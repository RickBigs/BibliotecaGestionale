<?php
// Avvio la sessione per gestire l'autenticazione
session_start();
// Includo la connessione al database
require_once 'connessione.php';

// Reindirizzo l'utente alla home se è già loggato
if (isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Gestisco il submit del modulo di login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    // Preparo la query per cercare l'utente nel database
    $stmt = $conn->prepare("SELECT id, username, password, ruolo FROM utenti WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    // Controllo se è stato trovato un utente con lo username fornito
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verifico la corrispondenza della password
        if (password_verify($password, $user['password'])) {
            // Password corretta, avvio la sessione
            session_start();
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['ruolo'] = $user['ruolo'];
            header('Location: index.php');
            exit;
        } else {
            // Password errata
            $msg = "<div class='alert-error'>Password errata.</div>";
        }
    } else {
        // Utente non trovato
        $msg = "<div class='alert-error'>Utente non trovato.</div>";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Login Biblioteca</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
<main class="flex flex-col items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white rounded-lg shadow p-8">
        <h1 id="title-login" class="text-2xl font-bold text-blue-900 mb-6 text-center">Login</h1>
        <?php if (isset($msg)) echo $msg; ?>
        <form id="form-login" method="post" action="login.php" class="flex flex-col gap-4">
            <label for="username" class="font-semibold text-blue-900">Username:</label>
            <input type="text" name="username" id="username" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
            <label for="password" class="font-semibold text-blue-900">Password:</label>
            <input type="password" name="password" id="password" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
            <button type="submit" class="bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">Accedi</button>
        </form>
    </div>
</main>
</body>
</html>
