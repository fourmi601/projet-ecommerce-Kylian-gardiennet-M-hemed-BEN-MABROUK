<?php
session_start();
require 'db.php';

// Sécurité
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$id_user = $_SESSION['user_id'];

// On va chercher les jeux que l'utilisateur a mis dans sa wishlist
$stmt = $pdo->prepare("
    SELECT j.*, c.nom_cat 
    FROM wishlist w 
    JOIN jeu j ON w.id_jeu = j.id_jeu 
    JOIN categorie c ON j.id_cat = c.id_cat 
    WHERE w.id_user = ? 
    ORDER BY w.date_ajout DESC
");
$stmt->execute([$id_user]);
$mes_favoris = $stmt->fetchAll();

// On calcule le nombre d'articles dans le panier pour la navbar
$nb_articles = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma Wishlist - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <nav style="padding: 20px; background: #1a1c24; display: flex; justify-content: space-between; align-items: center;">
        <div class="logo-container"><a href="index.php"><img src="assets/img/logo.jpg" alt="Logo" class="site-logo" style="height: 125px;"></a></div>
        <div class="user-actions" style="display: flex; gap: 15px; align-items: center;">
            <a href="mon_compte.php" style="color: white; text-decoration: none;">👤 Mon Compte</a>
            <a href="panier.php" class="cart-btn" style="background: #00439C; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;">🛒 Panier (<?php echo $nb_articles; ?>)</a>
        </div>
    </nav>

    <div class="container" style="padding: 40px; max-width: 1200px; margin: auto;">
        <h1 style="border-bottom: 2px solid #ff4757; padding-bottom: 10px; margin-bottom: 30px;">❤️ Ma Liste de Souhaits</h1>

        <?php if (empty($mes_favoris)): ?>
            <div style="text-align: center; padding: 50px; background: #1a1c24; border-radius: 8px;">
                <p style="color: #b3b3b3; font-size: 18px;">Votre liste de souhaits est vide.</p>
                <a href="index.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Retourner au catalogue pour découvrir des jeux</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                
                <?php foreach ($mes_favoris as $jeu): ?>
                    <div class="game-card" style="background: #1a1c24; border-radius: 8px; overflow: hidden; border: 1px solid #2a2c35; position: relative;">
                        
                        <a href="ajouter_wishlist.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" class="btn-wishlist">❤️</a>

                        <div class="card-image">
                            <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                        </div>
                        
                        <div class="card-info" style="padding: 15px;">
                            <h3 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                <div>
                                    <?php if ($jeu['prix_solde'] > 0): ?>
                                        <span style="text-decoration: line-through; color: #ff4757; font-size: 14px; margin-right: 5px;"><?php echo number_format($jeu['prix'], 2); ?>€</span>
                                        <span style="font-size: 20px; font-weight: bold; color: #2ecc71;"><?php echo number_format($jeu['prix_solde'], 2); ?>€</span>
                                    <?php else: ?>
                                        <span style="font-size: 20px; font-weight: bold; color: #2ecc71;"><?php echo number_format($jeu['prix'], 2); ?>€</span>
                                    <?php endif; ?>
                                </div>
                                <a href="ajouter_panier.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" style="background: #ff4757; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px;">Ajouter</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        <?php endif; ?>
    </div>
</body>
</html>