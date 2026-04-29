<?php
// Toggle wishlist : si le jeu y est déjà on l'enlève, sinon on l'ajoute.
// Appelé directement depuis un lien <a href="ajouter_wishlist.php?id_jeu=X">
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
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

$retour = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'catalogue.php';
header('Location: ' . $retour);
exit();
?>