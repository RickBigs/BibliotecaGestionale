<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connessione.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<link rel="stylesheet" href="styles.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>
<?php if (isset($_SESSION['username'])): ?>
    <div>
        <span class="user-badge"><span class="user-icon">👤</span><?php echo htmlspecialchars($_SESSION['username']); ?> <span style="opacity:0.7;font-size:0.95em;">(<?php echo $_SESSION['ruolo']; ?>)</span></span>
        <a href="logout.php" class="bottone" style="background:#c0392b;">Logout</a>
        <?php if ($_SESSION['ruolo'] === 'Admin'): ?>
            <a href="registrazioneUtente.php" class="bottone" style="background:#2d5f5d;">Registra Utente</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
    <header>
        <h1 id="title-header">Gestione Biblioteca</h1>
        <nav id="navbar">
            <ul>
                <li><a href="index.php">Homepage</a></li>
                <li><a href="categoria.php">Categorie</a></li>
                <li><a href="autori.php">Gestione Autori</a></li>
                <li><a href="libri.php">Gestione Libri</a></li>
                <li><a href="magazzino.php">Gestione Magazzino</a></li>
                <li><a href="statistiche.php">Statistiche & Contabilità</a></li>
            </ul>
        </nav>
    </header>
</body>
</html>
