<?php
session_start();
require 'db.php';

// ?vider → reset panier session
if (isset($_GET['vider'])) {
    unset($_SESSION['panier']);
    header('Location: index.php');
    exit();
}

$nb_articles = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;

$stmt = $pdo->query("
    SELECT jeu.*, categorie.nom_cat 
    FROM jeu 
    LEFT JOIN categorie ON jeu.id_cat = categorie.id_cat 
    ORDER BY jeu.ventes DESC, jeu.id_jeu DESC
");
$jeux = $stmt->fetchAll();

$ma_wishlist = [];
$nb_promos_wishlist = 0;
$nb_sorties_recentes = 0;

if (isset($_SESSION['user_id'])) {
    $stmtWish = $pdo->prepare("SELECT id_jeu FROM wishlist WHERE id_user = ?");
    $stmtWish->execute([$_SESSION['user_id']]);
    $ma_wishlist = $stmtWish->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($ma_wishlist)) {
        $ids = implode(',', array_map('intval', $ma_wishlist));
        $nb_promos_wishlist = $pdo->query("SELECT COUNT(*) FROM jeu WHERE id_jeu IN ($ids) AND prix_solde > 0")->fetchColumn();
        $nb_sorties_recentes = $pdo->query("SELECT COUNT(*) FROM jeu WHERE id_jeu IN ($ids) AND date_sortie <= NOW() AND date_sortie > DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php include 'navbar.php'; ?>

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
            <?php if (count($jeux) > 0):
                $now = date('Y-m-d H:i:s');
                foreach ($jeux as $jeu):
                    $est_precommande = !empty($jeu['date_sortie']) && $jeu['date_sortie'] > $now;
                    $est_indispo     = $jeu['prix'] <= 0;
                    $est_tiers       = !empty($jeu['id_vendeur']);
            ?>
                    <div class="game-card<?php echo $est_precommande ? ' card-preorder' : ''; ?>">

                        <?php if ($est_precommande): ?>
                            <span class="badge-preorder">PRÉCOMMANDE</span>
                        <?php elseif ($est_indispo): ?>
                            <span class="badge-soon">BIENTÔT</span>
                        <?php endif; ?>

                        <?php if ($est_tiers): ?>
                            <span class="badge-tiers">TIERS</span>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php $est_favori = in_array($jeu['id_jeu'], $ma_wishlist); ?>
                            <a href="ajouter_wishlist.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" class="btn-wishlist">
                                <?php echo $est_favori ? '❤️' : '🤍'; ?>
                            </a>
                        <?php endif; ?>

                        <div class="card-image">
                            <a href="jeu.php?id=<?php echo $jeu['id_jeu']; ?>">
                                <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="<?php echo htmlspecialchars($jeu['titre']); ?>">
                            </a>
                            <span class="platform-tag"><?php echo htmlspecialchars($jeu['nom_cat']); ?></span>
                        </div>

                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <p class="desc"><?php echo substr(htmlspecialchars($jeu['description']), 0, 50) . '…'; ?></p>

                            <div class="price-row">
                                <?php if ($est_indispo): ?>
                                    <span style="font-size:14px; color:#f39c12; font-weight:bold;">⏳ À venir</span>
                                    <button disabled class="btn-add" style="opacity:.45; cursor:not-allowed;">Indisponible</button>
                                <?php else: ?>
                                    <div>
                                        <?php if ($jeu['prix_solde'] > 0): ?>
                                            <span style="text-decoration:line-through; color:#ff4757; font-size:13px; margin-right:4px;"><?php echo number_format($jeu['prix'], 2); ?>€</span>
                                            <span class="price"><?php echo number_format($jeu['prix_solde'], 2); ?>€</span>
                                        <?php else: ?>
                                            <span class="price"><?php echo number_format($jeu['prix'], 2); ?>€</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="ajouter_panier.php?id_jeu=<?php echo $jeu['id_jeu']; ?>"
                                       class="btn-add<?php echo $est_precommande ? ' btn-preorder' : ''; ?>">
                                        <?php echo $est_precommande ? '🕐 Précommander' : 'Ajouter'; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
            <?php endforeach; ?>
            <?php else: ?>
                <p style="color:white; text-align:center;">Aucun jeu trouvé dans la base de données.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <?php if ($nb_promos_wishlist > 0 || $nb_sorties_recentes > 0): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($nb_sorties_recentes > 0): ?>
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success',
            title: 'Nouveauté Wishlist !',
            text: '<?php echo (int)$nb_sorties_recentes; ?> jeu(x) de votre liste vient de sortir !',
            showConfirmButton: false, timer: 8000,
            background: '#1a1c24', color: '#fff'
        });
        <?php endif; ?>
        <?php if ($nb_promos_wishlist > 0): ?>
        Swal.mixin({
            toast: true, position: 'bottom-end', showConfirmButton: false,
            showCloseButton: true, timer: 7000, timerProgressBar: true,
            background: '#1a1c24', color: '#fff'
        }).fire({
            icon: 'info',
            title: 'Promotions en cours !',
            text: 'Vous avez <?php echo (int)$nb_promos_wishlist; ?> jeu(x) de votre Wishlist en solde !'
        });
        <?php endif; ?>
    });
    </script>
    <?php endif; ?>

</body>
</html>
</html>