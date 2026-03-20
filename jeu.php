<?php
session_start();

// FAUX Elden ring
$jeu = [
    'titre' => 'Elden Ring',
    'description' => 'Plongez dans l\'Entre-Terre et devenez le Seigneur d\'Elden dans ce jeu de rôle d\'action épique développé par FromSoftware. Explorez un monde ouvert immense, affrontez des boss redoutables et découvrez des mystères insondables.',
    'prix' => 41.99,
    'image' => 'elden.jpg',
    'plateforme' => 'PC (Steam)',
    'editeur' => 'Bandai Namco',
    'date_sortie' => '25 Fév 2022'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $jeu['titre']; ?> - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav>
        <div class="logo-container">
            <a href="index.php"><img src="assets/img/logo.jpg" alt="Logo Digital Games" class="site-logo"></a>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Rechercher...">
            <button>🔍</button>
        </div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="#">Catalogue PC</a>
            <button id="theme-toggle" class="nav-theme-btn">Mode Clair</button>
            <a href="contact.php">Contact</a>
        </div>
        <div class="user-actions">
            <a href="#">👤 Compte</a>
            <a href="panier.php" class="cart-btn">🛒 Panier (0)</a>
        </div>
    </nav>

    <div class="container game-details-page" style="margin-top: 40px; margin-bottom: 60px;">
        <div class="game-details-grid">
            
            <div class="game-image-large">
                <img src="assets/img/<?php echo $jeu['image']; ?>" alt="<?php echo $jeu['titre']; ?>" style="width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
            </div>

            <div class="game-info-large">
                <h1 style="font-size: 42px; margin-bottom: 10px; color: #fff;"><?php echo $jeu['titre']; ?></h1>
                
                <div class="game-meta" style="color: #b3b3b3; margin-bottom: 25px; font-size: 16px;">
                    <span style="margin-right: 15px;">🎮 <strong>Plateforme:</strong> <?php echo $jeu['plateforme']; ?></span>
                    <span style="margin-right: 15px;">🏢 <strong>Éditeur:</strong> <?php echo $jeu['editeur']; ?></span>
                    <span>📅 <strong>Sortie:</strong> <?php echo $jeu['date_sortie']; ?></span>
                </div>

                <div class="game-buy-box" style="background: var(--bg-panel, #1a1c24); padding: 25px; border-radius: 8px; border: 1px solid #2a2c35; margin-bottom: 30px;">
                    <h2 style="font-size: 36px; color: #ff4757; margin-top: 0; margin-bottom: 15px;"><?php echo number_format($jeu['prix'], 2); ?> €</h2>
                    <p style="color: #2ecc71; margin-bottom: 20px;">✅ En stock - Livraison instantanée par email</p>
                    <button class="btn-hero" style="width: 100%; font-size: 18px; padding: 15px; border: none; cursor: pointer;">🛒 Ajouter au panier</button>
                </div>

                <div class="game-description">
                    <h3 style="color: #fff; margin-bottom: 15px; border-bottom: 1px solid #333; padding-bottom: 10px;">À propos du jeu</h3>
                    <p style="color: #b3b3b3; line-height: 1.6;"><?php echo $jeu['description']; ?></p>
                </div>
            </div>

        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-bottom" style="text-align: center; color: white; padding: 20px;">
            <p>&copy; <?php echo date('Y'); ?> Digital Games. Projet BTS - Tous droits réservés.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>