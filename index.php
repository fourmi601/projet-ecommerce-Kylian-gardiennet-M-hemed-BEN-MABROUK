<?php
session_start();

/* vide : 
https://youtu.be/37KohMnlP7Q?si=fMdK7PtGlx2lzufJ
*/
  

/* a retire apres base de données */
$jeux = [
    [
        'id_jeu' => 1,
        'titre' => 'Elden Ring',
        'description' => 'Plongez dans l\'Entre-Terre et devenez le Seigneur d\'Elden dans ce jeu de rôle d\'action épique.',
        'prix' => 41.99,
        'image' => 'elden.jpg',
        'plateforme' => 'PC'
    ],
    [
        'id_jeu' => 2,
        'titre' => 'EA Sports FC 24',
        'description' => 'Vivez l\'expérience ultime de football avec plus de 19 000 joueurs sous licence.',
        'prix' => 34.99,
        'image' => 'fc24.jpg',
        'plateforme' => 'PC'
    ],
    [
        'id_jeu' => 3,
        'titre' => 'Cyberpunk 2077',
        'description' => 'Un jeu de rôle d\'action en monde ouvert se déroulant dans la mégalopole de Night City.',
        'prix' => 29.99,
        'image' => 'logo.jpg',
        'plateforme' => 'PC'
    ]
];

/* fin a retire apres base de données */
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Games - Clés CD Officielles</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <nav>
        <div class="logo-container">
            <img src="assets/img/logo.jpg" alt="Logo Digital Games" class="site-logo">
        </div>
        
        <div class="search-box">
            <input type="text" placeholder="Rechercher...">
            <button>🔍</button>
        </div>

        <div class="nav-links">
            <a href="index.php" class="active">Accueil</a>
            <a href="#">Catalogue PC</a>
            <button id="theme-toggle" class="nav-theme-btn">Mode Clair</button>
            <a href="#">Promos</a>
            <a href="contact.php">Contact</a>
        </div>

        <div class="user-actions">
            <a href="#">👤 Compte</a>
            <a href="panier.php" class="cart-btn">🛒 Panier (0)</a>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h1>CLÉS CD OFFICIELLES <br> <span class="highlight">LIVRAISON INSTANTANÉE</span></h1>
            <p>Le meilleur du gaming, moins cher, tout de suite.</p>
            <a href="#catalogue" class="btn-hero">VOIR LES OFFRES</a>
        </div>
    </header>

    <section id="catalogue" class="container">
        <div class="section-header">
            <h2 class="section-title">Nouveautés & Tendances</h2>
        </div>

        <div class="games-grid">
            <?php if (count($jeux) > 0): ?>
                <?php foreach ($jeux as $jeu): ?>
                    <div class="game-card">
                        <div class="card-image">
                            <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="<?php echo htmlspecialchars($jeu['titre']); ?>">
                            <span class="platform-tag">PC</span>
                        </div>
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <p class="desc"><?php echo substr(htmlspecialchars($jeu['description']), 0, 40) . '...'; ?></p>
                            <div class="price-row">
                                <span class="price"><?php echo number_format($jeu['prix'], 2); ?> €</span>
                                <button class="btn-add">Ajouter</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:white;">Aucun jeu trouvé dans la base de données. Vérifiez phpMyAdmin.</p>
            <?php endif; ?>
        </div>
    </section>

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