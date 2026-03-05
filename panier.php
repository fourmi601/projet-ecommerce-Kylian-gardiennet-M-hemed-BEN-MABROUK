<?php
session_start(); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav>
        <div class="logo-container"><a href="index.php"><img src="assets/img/logo.jpg" alt="Logo" class="site-logo"></a></div>
        <div class="nav-links"><a href="index.php">Accueil</a> <a href="contact.php">Contact</a></div>
        <button id="theme-toggle" class="nav-theme-btn">Mode Clair</button>
    </nav>

    <div class="container">
        <h2 class="section-title">Mon Panier</h2>

        <div class="cart-container">
            <div class="cart-items">
                
                <div class="cart-item">
                    <img src="assets/img/elden.jpg" alt="Elden Ring">
                    <div class="cart-item-info">
                        <h3>Elden Ring</h3>
                        <span class="platform-tag">PC</span>
                    </div>
                    <div class="qty-box">
                        <button>-</button>
                        <span>1</span>
                        <button>+</button>
                    </div>
                    <div class="item-price">41.99 €</div>
                    <button class="btn-remove">🗑️</button>
                </div>

                <div class="cart-item">
                    <img src="assets/img/fc24.jpg" alt="FC 24">
                    <div class="cart-item-info">
                        <h3>EA Sports FC 24</h3>
                        <span class="platform-tag">PC</span>
                    </div>
                    <div class="qty-box">
                        <button>-</button>
                        <span>1</span>
                        <button>+</button>
                    </div>
                    <div class="item-price">30.99 €</div>
                    <button class="btn-remove">🗑️</button>
                </div>

            </div>

            <div class="cart-summary">
                <h3>Résumé de la commande</h3>
                <hr style="border-color: #333; margin: 15px 0;">
                <div class="price-row">
                    <span>Total (2 articles) :</span>
                    <span class="price">72.98 €</span>
                </div>
                <a href="https://buy.stripe.com/test_fZucN49uZ5zf6UG0u13gk00" class="btn-hero checkout-btn" style="display: block; margin-top: 20px; background-color: #635bff;">PASSER AU PAIEMENT SÉCURISÉ</a>
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