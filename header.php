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
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>body, input, select, textarea, button { font-family: 'Poppins', Arial, sans-serif !important; }</style>
<style>
.user-badge {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(90deg, #2d5f5d 60%, #1abc9c 100%);
    color: #fff;
    font-weight: 600;
    border-radius: 20px;
    padding: 0.4rem 1.2rem 0.4rem 0.8rem;
    margin-right: 1rem;
    font-size: 1rem;
    box-shadow: 0 2px 8px rgba(44,62,80,0.08);
    letter-spacing: 0.5px;
    gap: 0.5rem;
}
.user-badge .user-icon {
    background: #fff;
    color: #2d5f5d;
    border-radius: 50%;
    width: 1.7em;
    height: 1.7em;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1em;
    margin-right: 0.5em;
}
.bottone-elimina {
    background: #e74c3c !important;
    color: #fff !important;
    border: none;
    border-radius: 5px;
    padding: 0.4rem 1rem;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
    font-weight: 500;
}
.bottone-elimina:hover {
    background: #c0392b !important;
}
</style>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>
<?php if (isset($_SESSION['username'])): ?>
    <div style="position: absolute; top: 1rem; right: 2rem; display: flex; align-items: center; gap: 0.5rem;">
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
