<?php

require_once 'db.php'; 

/*jeu base données*/
try {
    $sql = "SELECT * FROM jeu";
    $query = $pdo->query($sql);
    $jeux = $query->fetchAll();
} catch (Exception $e) {
    $jeux = []; /* test jeu*/
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
            <a href="#">Promos</a>
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

</body>
</html>