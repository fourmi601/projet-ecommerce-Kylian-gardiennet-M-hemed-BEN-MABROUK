<?php
session_start();
require 'db.php';

// SÉCURITÉ : On vérifie que c'est bien l'admin qui lance ce script !
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Accès refusé. Vous devez être Admin.");
}

echo "<h1>🔄 Synchronisation avec Steam en cours...</h1>";

// 1. On cherche tous les jeux qui ont un ID Steam renseigné
$stmt = $pdo->query("SELECT id_jeu, titre, id_steam FROM jeu WHERE id_steam IS NOT NULL");
$jeux = $stmt->fetchAll();

foreach ($jeux as $jeu) {
    $id_jeu = $jeu['id_jeu'];
    $id_steam = $jeu['id_steam'];
    $titre = htmlspecialchars($jeu['titre']);
    
    echo "<p>Recherche des avis pour <strong>{$titre}</strong> (AppID: {$id_steam})... ";

    // 2. On interroge l'API officielle de Steam
    $url_api = "https://store.steampowered.com/appreviews/{$id_steam}?json=1&language=all&purchase_type=all";
    
    // On récupère la réponse de Steam
    $reponse = @file_get_contents($url_api); // Le @ cache les erreurs si Steam bug
    
    if ($reponse) {
        $data = json_decode($reponse, true); // On transforme le texte en tableau PHP
        
        // 3. On vérifie si Steam a bien renvoyé des avis
        if (isset($data['query_summary']) && $data['query_summary']['total_reviews'] > 0) {
            
            $total_avis = $data['query_summary']['total_reviews'];
            $avis_positifs = $data['query_summary']['total_positive'];
            
            // On calcule le vrai pourcentage !
            $pourcentage = round(($avis_positifs / $total_avis) * 100);
            
            // 4. On met à jour NOTRE base de données avec la vraie note
            $update = $pdo->prepare("UPDATE jeu SET note_steam = ? WHERE id_jeu = ?");
            $update->execute([$pourcentage, $id_jeu]);
            
            echo "<span style='color: green;'>✅ Succès ! Note mise à jour : {$pourcentage}%</span></p>";
        } else {
            echo "<span style='color: orange;'>⚠️ Aucun avis trouvé sur Steam pour ce jeu.</span></p>";
        }
    } else {
        echo "<span style='color: red;'>❌ Erreur de connexion à Steam.</span></p>";
    }
    
    // Petite pause de 1 seconde pour ne pas que Steam nous bloque (Rate Limit)
    sleep(1); 
}

echo "<h2>🎉 Synchronisation terminée avec succès !</h2>";
echo "<a href='admin.php' style='padding: 10px; background: #3498db; color: white; text-decoration: none; border-radius: 4px;'>Retour à l'Admin</a>";
?>