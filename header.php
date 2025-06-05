<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connessione.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestione Biblioteca</title>
</head>
<body>
<header>
    <div class="header-container">
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
        
        <?php if (isset($_SESSION['username'])): ?>
        <div class="user-controls">
            <div class="user-badge">
                <span class="user-icon">👤</span>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <span class="user-role">(<?php echo $_SESSION['ruolo']; ?>)</span>
            </div>
            <a href="logout.php" class="btn btn-logout">Logout</a>
            <?php if ($_SESSION['ruolo'] === 'Admin'): ?>
                <a href="registrazioneUtente.php" class="btn btn-admin">Registra Utente</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</header>
</body>
</html>