<?php
// toggle wishlist → vérifie le referer pour éviter un open redirect
require_once 'security.php';
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = [
        'type'    => 'wishlist',
        'message' => '❤️ Connectez-vous pour ajouter des jeux à votre wishlist.',
        'retour'  => safe_redirect($_SERVER['HTTP_REFERER'] ?? '', 'catalogue.php')
    ];
    header('Location: connexion.php');
    exit();
}

if (isset($_GET['id_jeu'])) {
    $id_user = $_SESSION['user_id'];
    $id_jeu = $_GET['id_jeu'];

    $stmt = $pdo->prepare("SELECT * FROM wishlist WHERE id_user = ? AND id_jeu = ?");
    $stmt->execute([$id_user, $id_jeu]);

    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM wishlist WHERE id_user = ? AND id_jeu = ?")->execute([$id_user, $id_jeu]);
    } else {
        $pdo->prepare("INSERT INTO wishlist (id_user, id_jeu) VALUES (?, ?)")->execute([$id_user, $id_jeu]);
    }
}

// safe_redirect valide que l'URL appartient au même domaine (anti open redirect)
$retour = safe_redirect($_SERVER['HTTP_REFERER'] ?? '', 'catalogue.php');
header('Location: ' . $retour);
exit();
?>