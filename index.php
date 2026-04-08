<?php
session_start();
if (isset($_GET['vider'])) { unset($_SESSION['panier']); header('Location: index.php'); exit(); }
require 'db.php'; 

$nb_articles = 0;
if (isset($_SESSION['panier'])) {
    $nb_articles = count($_SESSION['panier']); // COUNT compte les jeux différents !
}
/* vide : 
https://youtu.be/37KohMnlP7Q?si=fMdK7PtGlx2lzufJ
*/
  

$stmt = $pdo->query("
    SELECT jeu.*, categorie.nom_cat 
    FROM jeu 
    JOIN categorie ON jeu.id_cat = categorie.id_cat 
    ORDER BY id_jeu DESC
");
$jeux = $stmt->fetchAll();

$ma_wishlist = [];
$nb_promos_wishlist = 0;

if (isset($_SESSION['user_id'])) {
    $stmtWish = $pdo->prepare("SELECT id_jeu FROM wishlist WHERE id_user = ?");
    $stmtWish->execute([$_SESSION['user_id']]);
    $ma_wishlist = $stmtWish->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($ma_wishlist)) {
        $ids = implode(',', $ma_wishlist);
        $stmtSolde = $pdo->query("SELECT COUNT(*) FROM jeu WHERE id_jeu IN ($ids) AND prix_solde > 0");
        $nb_promos_wishlist = $stmtSolde->fetchColumn(); 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Games - Clés CD Officielles</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                
               <a href="wishlist.php" style="position: relative; text-decoration: none; font-size: 24px; margin-right: 20px; display: inline-block;">
    🔔
    <?php if ($nb_promos_wishlist > 0): ?>
        <span style="position: absolute; top: -5px; right: -8px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.5);">
            <?php echo $nb_promos_wishlist; ?>
        </span>
    <?php endif; ?>
</a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" style="color: #2ecc71; font-weight: bold;">⚙️ Admin</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'tiers'): ?>
                    <a href="vendeur.php" style="color: #f39c12; font-weight: bold;">🏪 Espace Vendeur</a>
                <?php endif; ?>

                <a href="mon_compte.php" class="active">👤 Salut <?php echo htmlspecialchars($_SESSION['pseudo'] ?? 'Membre'); ?></a>
                <a href="deconnexion.php" style="color: #ff4757;">Déconnexion</a>
                
            <?php else: ?>
                <a href="connexion.php" class="active">👤 Compte</a>
            <?php endif; ?>
            
            <a href="panier.php" class="cart-btn">🛒 Panier (<?php echo $nb_articles; ?>)</a>
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
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php $est_favori = in_array($jeu['id_jeu'], $ma_wishlist); ?>
                            <a href="ajouter_wishlist.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" class="btn-wishlist">
                                <?php echo $est_favori ? '❤️' : '🤍'; ?>
                            </a>
                        <?php endif; ?>

                        <div class="card-image">
                            <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="<?php echo htmlspecialchars($jeu['titre']); ?>">
                            <span class="platform-tag"><?php echo htmlspecialchars($jeu['nom_cat']); ?></span>
                        </div>
                        
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <p class="desc"><?php echo substr(htmlspecialchars($jeu['description']), 0, 40) . '...'; ?></p>
                            
                            <div class="price-row" style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                
                                <div>
                                    <?php if ($jeu['prix_solde'] > 0): ?>
                                        <span style="text-decoration: line-through; color: #ff4757; font-size: 14px; margin-right: 5px;"><?php echo number_format($jeu['prix'], 2); ?>€</span>
                                        <span class="price" style="font-size: 20px; font-weight: bold; color: #2ecc71;"><?php echo number_format($jeu['prix_solde'], 2); ?>€</span>
                                    <?php else: ?>
                                        <span class="price" style="font-size: 20px; font-weight: bold; color: #2ecc71;"><?php echo number_format($jeu['prix'], 2); ?>€</span>
                                    <?php endif; ?>
                                </div>
                                
                                <a href="ajouter_panier.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" class="btn-add" style="background: #ff4757; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; transition: 0.3s;">
                                    Ajouter
                                </a>
                            </div>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:white; text-align:center;">Aucun jeu trouvé dans la base de données. Vérifiez phpMyAdmin.</p>
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
    <script src="assets/js/main.js"></script>

   <?php if ($nb_promos_wishlist > 0): ?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            showCloseButton: true, // ❌ LA PETITE CROIX EST ICI !
            timer: 7000, // Je l'ai mis sur 7 secondes
            timerProgressBar: true,
            background: '#1a1c24',
            color: '#fff',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'info',
            title: 'Promotions en cours !',
            text: 'Vous avez <?php echo $nb_promos_wishlist; ?> jeu(x) de votre Wishlist en solde !'
        });
    </script>
    <?php endif; ?>

</body>
</html>
</body>
</html>