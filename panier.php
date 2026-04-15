<?php
session_start();
require 'db.php';

// --- GESTION DES ACTIONS ---
if (isset($_GET['action']) && isset($_GET['id_jeu'])) {
    $id_cible = (int)$_GET['id_jeu'];

    if ($_GET['action'] === 'supprimer') {
        unset($_SESSION['panier'][$id_cible]);
    }
    elseif ($_GET['action'] === 'mettre_cote') {
        unset($_SESSION['panier'][$id_cible]);
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO wishlist (id_user, id_jeu) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $id_cible]);
        } else {
            $_SESSION['wishlist'][$id_cible] = true;
        }
    }
    elseif ($_GET['action'] === 'ajouter_panier') {
        $_SESSION['panier'][$id_cible] = 1; // On rajoute au panier
        
        // On le retire de la wishlist
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id_user = ? AND id_jeu = ?");
            $stmt->execute([$_SESSION['user_id'], $id_cible]);
        } else {
            unset($_SESSION['wishlist'][$id_cible]);
        }
    }
    
    header('Location: panier.php');
    exit();
}

$message_promo = '';
if (isset($_POST['appliquer_promo'])) {
    $code_saisi = strtoupper($_POST['code_promo']);
    $stmt = $pdo->prepare("SELECT * FROM code_promo WHERE code = ?");
    $stmt->execute([$code_saisi]);
    $promo = $stmt->fetch();
    if ($promo) {
        $date_jour = date('Y-m-d');
        if ($promo['date_expiration'] && $promo['date_expiration'] < $date_jour) {
            $message_promo = "❌ Ce code a expiré.";
        } elseif ($promo['nb_utilisations'] >= $promo['max_utilisations']) {
            $message_promo = "❌ Ce code n'est plus disponible.";
        } else {
            $_SESSION['promo'] = $promo;
            $message_promo = "✅ Code appliqué (-" . $promo['reduction_pourcentage'] . "%) !";
        }
    } else {
        $message_promo = "❌ Code invalide.";
    }
}

$jeux_panier = [];
$sous_total = 0;
if (!empty($_SESSION['panier'])) {
    $ids = array_keys($_SESSION['panier']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $pdo->prepare("SELECT j.*, c.nom_cat FROM jeu j JOIN categorie c ON j.id_cat = c.id_cat WHERE id_jeu IN ($placeholders)");
    $stmt->execute($ids);
    $jeux_panier = $stmt->fetchAll();
    foreach ($jeux_panier as $jeu) {
        $sous_total += $jeu['prix'] * $_SESSION['panier'][$jeu['id_jeu']];
    }
}

$jeux_cote = [];
$ids_cote = [];

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id_jeu FROM wishlist WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $ids_cote = $stmt->fetchAll(PDO::FETCH_COLUMN);
} elseif (!empty($_SESSION['wishlist'])) {
    $ids_cote = array_keys($_SESSION['wishlist']);
}

if (!empty($ids_cote)) {
    $placeholders = str_repeat('?,', count($ids_cote) - 1) . '?';
    $stmt = $pdo->prepare("SELECT j.*, c.nom_cat FROM jeu j JOIN categorie c ON j.id_cat = c.id_cat WHERE id_jeu IN ($placeholders)");
    $stmt->execute($ids_cote);
    $jeux_cote = $stmt->fetchAll();
}

$total = $sous_total;
$montant_reduction = 0;
if (isset($_SESSION['promo']) && !empty($_SESSION['panier'])) {
    $montant_reduction = ($sous_total * $_SESSION['promo']['reduction_pourcentage']) / 100;
    $total = $sous_total - $montant_reduction;
}
$_SESSION['total_a_payer'] = $total;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">
    <nav>
        <div class="logo-container"><a href="index.php"><img src="assets/img/logo.jpg" alt="Logo" class="site-logo"></a></div>
        <div class="nav-links"><a href="index.php">Accueil</a> <a href="contact.php">Contact</a></div>
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" class="active">👤 <?php echo htmlspecialchars($_SESSION['pseudo']); ?></a>
                <a href="deconnexion.php" style="color: #ff4757; margin-left: 15px;">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="active">👤 Connexion</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h2 style="font-size: 32px; margin-bottom: 30px; border-left: 5px solid #ff4757; padding-left: 15px;">Mon Panier</h2>

        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            
            <div style="flex: 2; min-width: 300px;">
                <?php if (empty($jeux_panier)): ?>
                    <div style="background: #1a1c24; padding: 40px; border-radius: 8px; text-align: center; border: 1px solid #2a2c35;">
                        <h3 style="color: #b3b3b3;">Votre panier est vide.</h3>
                        <a href="index.php" class="btn-hero" style="display: inline-block; margin-top: 20px; text-decoration: none; background: #3498db; padding: 10px 20px; color: white; border-radius: 4px;">Explorer le catalogue</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($jeux_panier as $jeu): $qte = $_SESSION['panier'][$jeu['id_jeu']]; ?>
                        <div style="background: #1a1c24; border: 1px solid #2a2c35; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" style="width: 70px; height: 90px; object-fit: cover; border-radius: 4px;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0; font-size: 20px;"><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                                <div style="display: flex; gap: 15px; margin-top: 10px;">
                                    <a href="panier.php?action=mettre_cote&id_jeu=<?php echo $jeu['id_jeu']; ?>" style="color: #f1c40f; text-decoration: none; font-size: 14px;">⭐ Mettre de côté</a>
                                    <a href="panier.php?action=supprimer&id_jeu=<?php echo $jeu['id_jeu']; ?>" style="color: #ff4757; text-decoration: none; font-size: 14px;">🗑️ Retirer</a>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 20px; font-weight: bold; color: #2ecc71;"><?php echo number_format($jeu['prix'] * $qte, 2); ?> €</div>
                                <div style="color: #666; font-size: 14px;">Qté: <?php echo $qte; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="flex: 1; min-width: 300px;">
                <div style="background: #1a1c24; padding: 30px; border-radius: 8px; border: 1px solid #2a2c35;">
                    <h3 style="margin-top: 0;">Total</h3>
                    <div style="display: flex; justify-content: space-between; margin: 20px 0;">
                        <span>Sous-total</span>
                        <span><?php echo number_format($sous_total, 2); ?> €</span>
                    </div>

                    <form action="panier.php" method="POST" style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <input type="text" name="code_promo" placeholder="Code promo" style="flex: 1; padding: 10px; background: #0b0c10; border: 1px solid #333; color: white; border-radius: 4px;">
                        <button type="submit" name="appliquer_promo" style="padding: 10px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">OK</button>
                    </form>

                    <?php if (isset($_SESSION['promo']) && !empty($jeux_panier)): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 20px; color: #2ecc71;">
                            <span>Réduction (-<?php echo $_SESSION['promo']['reduction_pourcentage']; ?>%)</span>
                            <span>-<?php echo number_format($montant_reduction, 2); ?> €</span>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; font-size: 24px; font-weight: bold; border-top: 1px solid #333; padding-top: 20px;">
                        <span>Total</span>
                        <span style="color: #2ecc71;"><?php echo number_format($total, 2); ?> €</span>
                    </div>

                    <?php if (!empty($jeux_panier)): ?>
                        <a href="process_paiement.php" style="display: block; width: 100%; background: #ff4757; color: white; text-align: center; padding: 15px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-top: 30px;">PASSER À LA CAISSE</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($jeux_cote)): ?>
            <div style="margin-top: 60px;">
                <h2 style="font-size: 24px; color: #f1c40f; margin-bottom: 20px; border-bottom: 1px solid #2a2c35; padding-bottom: 10px;">Articles mis de côté</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                    <?php foreach ($jeux_cote as $item): ?>
                        <div style="background: #1a1c24; border: 1px solid #2a2c35; border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 15px;">
                            <img src="assets/img/<?php echo htmlspecialchars($item['image']); ?>" style="width: 50px; height: 65px; object-fit: cover; border-radius: 4px;">
                            <div style="flex: 1;">
                                <h4 style="margin: 0; font-size: 16px;"><?php echo htmlspecialchars($item['titre']); ?></h4>
                                <div style="color: #2ecc71; font-weight: bold; margin: 5px 0;"><?php echo number_format($item['prix'], 2); ?> €</div>
                                <a href="panier.php?action=ajouter_panier&id_jeu=<?php echo $item['id_jeu']; ?>" style="color: #3498db; text-decoration: none; font-size: 13px; font-weight: bold;">+ Remettre au panier</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>