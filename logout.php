z<?php
// Avvio la sessione per gestire l'autenticazione
session_start();
// Distruggo la sessione per disconnettere l'utente
session_unset();
session_destroy();
// Reindirizzo alla pagina di login
header('Location: login.php');
exit;
?>
