<?php
session_start();

/* donnéeeess pour test */
$jeux_wishlist = [
    [
        'id_jeu' => 3,
        'titre' => 'Cyberpunk 2077',
        'description' => 'Un jeu de rôle d\'action en monde ouvert se déroulant dans la mégalopole de Night City.',
        'prix' => 29.99,
        'image' => 'logo.jpg',
        'plateforme' => 'PC'
    ]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Liste d'Envies - Digital Games</title>
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
            <a href="panier.php" class="cart-btn">🛒 Panier</a>
        </div>
    </nav>

    <div class="container" style="min-height: 60vh; padding: 40px 20px;">
        <h1 style="color: #fff; border-bottom: 2px solid #ff4757; padding-bottom: 10px; margin-bottom: 30px;">❤️ Ma Liste d'Envies</h1>

        <div class="games-grid">
            <?php if (count($jeux_wishlist) > 0): ?>
                <?php foreach ($jeux_wishlist as $jeu): ?>
                    <div class="game-card">
                        <div class="card-image">
                            <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="<?php echo htmlspecialchars($jeu['titre']); ?>">
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <div class="price-row" style="margin-top: 15px;">
                                <span class="price"><?php echo number_format($jeu['prix'], 2); ?> €</span>
                            </div>
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <button class="btn-hero" style="flex: 1; padding: 10px; font-size: 14px; border:none; cursor:pointer;">🛒 Ajouter</button>
                                <button style="flex: 1; padding: 10px; font-size: 14px; background: #333; color: white; border:none; border-radius: 4px; cursor:pointer;">🗑️ Retirer</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #b3b3b3; font-size: 18px;">Votre liste d'envies est vide pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-col">
                <img src="assets/img/logo.jpg" alt="Logo Digital Games" class="footer-logo">
                <p>Votre boutique N°1 de clés CD officielles. Livraison instantanée, prix imbattables et paiements 100% sécurisés.</p>
            </div>
            <div class="footer-col">
                <h3>Liens Rapides</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="#">Catalogue PC</a></li>
                    <li><a href="panier.php">Mon Panier</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Informations</h3>
                <ul>
                    <li><a href="mentions-legales.php">Mentions Légales</a></li>
                    <li><a href="cgv.php">Conditions Générales de Vente</a></li>
                    <li><a href="contact.php">Contactez-nous</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Paiement Sécurisé</h3>
                <div class="payment-icons">
                    <span>💳 Carte Bancaire</span>
                    <span>🅿️ PayPal</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Digital Games. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>