<?php
session_start();
require 'db.php';

$nb_articles = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;
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

$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom_cat ASC")->fetchAll();
$plateformes = $pdo->query("SELECT * FROM plateforme ORDER BY nom_plateforme ASC")->fetchAll();

$sql = "SELECT DISTINCT j.*, c.nom_cat FROM jeu j 
        JOIN categorie c ON j.id_cat = c.id_cat 
        LEFT JOIN jeu_plateforme jp ON j.id_jeu = jp.id_jeu 
        WHERE 1=1";
$params = [];

if (!empty($_GET['categorie'])) {
    $sql .= " AND j.id_cat = ?";
    $params[] = $_GET['categorie'];
}

if (!empty($_GET['plateforme'])) {
    $sql .= " AND jp.id_plateforme = ?";
    $params[] = $_GET['plateforme'];
}

if (!empty($_GET['search'])) {
    $recherche = trim($_GET['search']);
    $recherche = preg_replace('/\s+/', ' ', $recherche);

    $mots = explode(' ', $recherche);
    foreach ($mots as $mot) {
        $sql .= " AND j.titre LIKE ?";
        $params[] = '%' . $mot . '%';
    }
}

$sql .= " ORDER BY j.titre ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jeux = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catalogue - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <nav style="padding: 20px; background: #1a1c24; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #2a2c35;">
        <div class="logo-container"><a href="index.php"><img src="assets/img/logo.jpg" alt="Logo" class="site-logo" style="height: 40px;"></a></div>
        
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="catalogue.php" class="active" style="color: #3498db; font-weight: bold;">Catalogue</a>
        </div>

        <div class="user-actions" style="display: flex; align-items: center; gap: 15px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="wishlist.php" style="position: relative; text-decoration: none; font-size: 24px; display: inline-block;">
                    🔔
                    <?php if ($nb_promos_wishlist > 0): ?>
                        <span style="position: absolute; top: -5px; right: -8px; background: #ff4757; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.5);"><?php echo $nb_promos_wishlist; ?></span>
                    <?php endif; ?>
                </a>
                <a href="mon_compte.php" style="color: white; text-decoration: none;">👤 Mon Compte</a>
            <?php else: ?>
                <a href="connexion.php" style="color: white; text-decoration: none;">👤 Connexion</a>
            <?php endif; ?>
            
            <a href="panier.php" class="cart-btn" style="background: #00439C; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none; font-weight: bold;">🛒 Panier (<?php echo $nb_articles; ?>)</a>
        </div>
    </nav>

    <div class="container" style="padding: 40px; max-width: 1400px; margin: auto; display: flex; gap: 40px; align-items: flex-start;">
        
        <aside style="width: 280px; background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35; position: sticky; top: 20px;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px; font-size: 22px;">🔍 Filtrer les jeux</h2>
            
            <form action="catalogue.php" method="GET" style="display: flex; flex-direction: column; gap: 20px; margin-top: 20px;">
                
                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Recherche par nom</label>
                    <input type="text" name="search" placeholder="Ex: Elden Ring..." value="<?php echo $_GET['search'] ?? ''; ?>" style="width: 100%; padding: 10px; background: #0f1014; border: 1px solid #333; color: white; border-radius: 4px;">
                </div>

                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Catégorie (Genre)</label>
                    <select name="categorie" style="width: 100%; padding: 10px; background: #0f1014; border: 1px solid #333; color: white; border-radius: 4px;">
                        <option value="">Toutes les catégories</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id_cat']; ?>" <?php if(isset($_GET['categorie']) && $_GET['categorie'] == $cat['id_cat']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['nom_cat']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Plateforme</label>
                    <select name="plateforme" style="width: 100%; padding: 10px; background: #0f1014; border: 1px solid #333; color: white; border-radius: 4px;">
                        <option value="">Toutes les plateformes</option>
                        <?php foreach($plateformes as $plat): ?>
                            <option value="<?php echo $plat['id_plateforme']; ?>" <?php if(isset($_GET['plateforme']) && $_GET['plateforme'] == $plat['id_plateforme']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($plat['nom_plateforme']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" style="background: #ff4757; color: white; border: none; padding: 12px; border-radius: 4px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px;">APPLIQUER LES FILTRES</button>
                <a href="catalogue.php" style="text-align: center; color: #b3b3b3; text-decoration: none; font-size: 14px; padding-top: 5px;">Réinitialiser les filtres</a>
            </form>
        </aside>

        <main style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">
                <h1 style="margin: 0;">🎮 Catalogue Complet</h1>
                <span style="color: #b3b3b3; font-size: 16px;"><strong><?php echo count($jeux); ?></strong> jeu(x) trouvé(s)</span>
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
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: #1a1c24; border-radius: 8px;">
                        <h3 style="color: #ff4757; margin-bottom: 10px;">Aucun jeu ne correspond à vos critères</h3>
                        <p style="color: #b3b3b3;">Essayez de modifier vos filtres ou de réinitialiser votre recherche.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    </div>
</body>
</html>