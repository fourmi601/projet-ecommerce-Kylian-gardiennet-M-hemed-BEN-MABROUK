<?php
// ajoute un jeu au panier session, puis redirige sur la page d'origine
require_once 'security.php';
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if (isset($_GET['id_jeu'])) {
    $id = (int)$_GET['id_jeu'];
    if ($id > 0) {
        $_SESSION['panier'][$id] = isset($_SESSION['panier'][$id]) ? $_SESSION['panier'][$id] + 1 : 1;
    }
}

// safe_redirect : n'accepte que les URLs du même domaine (anti open redirect)
$retour = safe_redirect($_SERVER['HTTP_REFERER'] ?? '', 'catalogue.php');
header('Location: ' . $retour);
exit();
