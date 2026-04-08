<?php
session_start();

// Si le panier n'existe pas encore, on le crée (c'est un tableau vide)
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Si on a bien reçu l'ID d'un jeu via l'URL
if (isset($_GET['id_jeu'])) {
    $id = $_GET['id_jeu'];
    
    // Si le jeu n'est pas encore dans le panier, on l'ajoute avec une quantité de 1
    if (!isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id] = 1;
    } else {
        // S'il y est déjà, on augmente la quantité de 1
        $_SESSION['panier'][$id]++;
    }
}

// Une fois ajouté, on renvoie le client sur l'accueil (ou là où il était)
header('Location: index.php');
exit();
?>