<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions Légales - Digital Games</title>
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

    <div class="container" style="max-width: 900px; margin-top: 40px; margin-bottom: 60px;">
        <h2 class="section-title">Mentions Légales</h2>

        <div style="color: #b3b3b3; line-height: 1.8;">
            
            <h3 style="color: var(--text-white); margin-top: 30px;">1. Informations sur l'entreprise</h3>
            <p>
                <strong>Raison sociale :</strong> Digital Games SARL<br>
                <strong>Adresse :</strong> 123 Rue du Gaming, 75001 Paris, France<br>
                <strong>Téléphone :</strong> +33 1 23 45 67 89<br>
                <strong>Email :</strong> contact@digitalgames.fr<br>
                <strong>SIRET :</strong> 12 345 678 901 234<br>
                <strong>Gérant :</strong> Jean Dupont
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">2. Responsable du site et publication</h3>
            <p>
                Directeur de la publication : Jean Dupont<br>
                Le site est hébergé par OVH, situé à Roubaix, France.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">3. Propriété intellectuelle</h3>
            <p>
                Tous les contenus du site (textes, images, logos, vidéos) sont la propriété exclusive de Digital Games ou de ses partenaires, 
                et sont protégés par les lois sur la propriété intellectuelle. Toute reproduction ou utilisation sans autorisation écrite 
                préalable est interdite.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">4. Limitation de responsabilité</h3>
            <p>
                Digital Games décline toute responsabilité pour les dommages directs ou indirects résultant de l'accès ou de l'utilisation du site. 
                Les jeux vendus sont fournis en l'état. Les clés de produit sont générées automatiquement après paiement validation. 
                Aucun remboursement n'est possible après l'activation de la clé.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">5. Données personnelles</h3>
            <p>
                Conformément à la loi RGPD, les données collectées lors de votre achat ou inscription sont utilisées uniquement 
                pour traiter votre commande et vous envoyer des informations relatives à vos achats. Vous pouvez à tout moment 
                demander l'accès, la modification ou la suppression de vos données personnelles en contactant contact@digitalgames.fr.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">6. Cookies</h3>
            <p>
                Le site utilise des cookies pour améliorer l'expérience utilisateur et analyser le trafic. En continuant à naviguer sur le site, 
                vous acceptez l'utilisation de cookies. Vous pouvez modifier vos paramètres de cookies dans les paramètres de votre navigateur.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">7. Liens externes</h3>
            <p>
                Digital Games n'est pas responsable des contenus des sites externes vers lesquels renvoient les liens présents sur ce site.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">8. Entente de droit</h3>
            <p>
                Les présentes mentions légales sont régies par la loi française. 
                Tous les litiges seront soumis à la juridiction des tribunaux français.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">9. Modification des conditions</h3>
            <p>
                Digital Games se réserve le droit de modifier ces mentions légales à tout moment. 
                Les modifications entreront en vigueur dès leur publication sur le site.
            </p>

            <p style="margin-top: 40px; text-align: center; color: var(--text-grey);">
                Dernière mise à jour :  <?php echo date('d/m/Y'); ?>
            </p>
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
                    <span>💳 Carte Bancaire</span>
                    <span>🅿️ PayPal</span>
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
