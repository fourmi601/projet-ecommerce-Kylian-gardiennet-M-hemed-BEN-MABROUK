<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Digital Games</title>
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
            <a href="contact.php" class="active">Contact</a>
        </div>

       <div class="user-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" style="color: #2ecc71; font-weight: bold;">⚙️ Admin</a>
        <?php endif; ?>

        <a href="#" class="active">👤 Salut <?php echo htmlspecialchars($_SESSION['pseudo']); ?></a>
        <a href="deconnexion.php" style="color: #ff4757;">Déconnexion</a>
        
    <?php else: ?>
        <a href="connexion.php" class="active">👤 Compte</a>
    <?php endif; ?>
    
    <a href="panier.php" class="cart-btn">🛒 Panier</a>
</div>
    </nav>

    <div class="container" style="max-width: 800px; margin-top: 50px; margin-bottom: 50px;">
        <h2 class="section-title">Contactez-nous</h2>
        <p style="color: #b3b3b3; margin-bottom: 30px;">Une question sur une commande ou un jeu ? Envoyez-nous un message et nous vous répondrons dans les plus brefs délais.</p>
 
        <div class="contact-wrapper" style="background: var(--bg-panel, #1a1c24); padding: 40px; border-radius: 8px; border: 1px solid #2a2c35;">
            
    
            <form action="https://formspree.io/f/mdawnegg" method="POST">
                
                <div style="margin-bottom: 20px;">
                    <label for="nom" style="color: #b3b3b3; display: block; margin-bottom: 8px;">Votre Nom</label>
                    <input type="text" id="nom" name="nom" required style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="email" style="color: #b3b3b3; display: block; margin-bottom: 8px;">Votre Email</label>
                    <input type="email" id="email" name="email" required style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 30px;">
                    <label for="message" style="color: #b3b3b3; display: block; margin-bottom: 8px;">Votre Message</label>
                    <textarea id="message" name="message" rows="6" required style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box; resize: vertical;"></textarea>
                </div>

                <button type="submit" class="btn-hero" style="width: 100%; border: none; cursor: pointer;">Envoyer le message</button>
            </form>
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
                    <li><a href="#">Promotions</a></li>
                    <li><a href="panier.php">Mon Panier</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Informations</h3>
                <ul>
                    <li><a href="mentions-legales.php">Mentions Légales</a></li>
                    <li><a href="cgv.php">Conditions Générales de Vente</a></li>
                    <li><a href="#">Politique de Confidentialité</a></li>
                    <li><a href="contact.php">Contactez-nous</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Paiement Sécurisé</h3>
                <div class="payment-icons">
                    <span>Carte Bancaire</span>
                    <span>PayPal</span>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Digital Games. Projet BTS - Tous droits réservés.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>