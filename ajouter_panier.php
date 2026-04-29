<?php
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

$retour = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'catalogue.php';
header('Location: ' . $retour);
exit();
