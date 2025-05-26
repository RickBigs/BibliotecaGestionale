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
    <title>Login Biblioteca</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main style="max-width:600px;margin:auto;">
    <h1 id="title-login">Login</h1>
    <?php if (isset($msg)) echo $msg; ?>
    <form id="form-login" method="post" action="login.php">
        <label for="usernamez">Username:</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" class="bottone btn-add">Accedi</button>
    </form>

</main>
</body>
</html>
