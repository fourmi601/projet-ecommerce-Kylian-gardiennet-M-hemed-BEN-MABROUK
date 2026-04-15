<?php
require 'db.php'; 

$recherche = $_GET['q'] ?? '';

if (!empty($recherche)) {
    try {
        $stmt = $pdo->prepare("
            SELECT j.id_jeu, j.titre, j.image, j.prix, c.nom_cat 
            FROM jeu j
            LEFT JOIN categorie c ON j.id_cat = c.id_cat
            WHERE j.titre LIKE ? 
            LIMIT 5
        ");
        $stmt->execute(["%$recherche%"]);
        $jeux = $stmt->fetchAll();

        if (count($jeux) > 0) {
            foreach ($jeux as $jeu) {
                $cat = $jeu['nom_cat'] ? htmlspecialchars($jeu['nom_cat']) : 'PC';
                echo '
                <a href="jeu.php?id=' . $jeu['id_jeu'] . '" ...' . urlencode($jeu['titre']) . '" 
                   style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #3a3b40; text-decoration: none; color: white; background: #2e2f33;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="assets/img/' . htmlspecialchars($jeu['image']) . '" style="width: 85px; height: 45px; object-fit: cover; border-radius: 5px;">
                        <div style="display: flex; flex-direction: column;">
                            <strong style="font-size: 15px;">' . htmlspecialchars($jeu['titre']) . '</strong>
                            <span style="color: #9aa0a6; font-size: 12px;">' . $cat . '</span>
                        </div>
                    </div>
                    <div style="font-weight: bold; font-size: 15px;">' . number_format($jeu['prix'], 2) . ' €</div>
                </a>';
            }
            echo '<a href="catalogue.php?search=' . urlencode($recherche) . '" style="display: block; text-align: center; padding: 10px; background: #ff4757; color: white; text-decoration: none; font-weight: bold;">Voir tous les résultats</a>';
        } else {
            echo '<div style="padding: 15px; background: #2e2f33; color: #b3b3b3; text-align: center;">Aucun résultat.</div>';
        }
    } catch (Exception $e) { echo "Erreur BDD"; }
}