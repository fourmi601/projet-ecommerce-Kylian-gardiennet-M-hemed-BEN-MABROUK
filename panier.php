<?php
// panier : $_SESSION['panier'] = [id_jeu => qté], total → $_SESSION['total_a_payer']
session_start();
require 'db.php';

if (isset($_GET['action']) && isset($_GET['id_jeu'])) {
    $id_cible = (int)$_GET['id_jeu'];

    if ($_GET['action'] === 'augmenter') {
        if (isset($_SESSION['panier'][$id_cible])) {
            $_SESSION['panier'][$id_cible]++;
        }
    } elseif ($_GET['action'] === 'diminuer') {
        if (isset($_SESSION['panier'][$id_cible])) {
            $_SESSION['panier'][$id_cible]--;
            if ($_SESSION['panier'][$id_cible] <= 0) {
                unset($_SESSION['panier'][$id_cible]);
            }
        }
    } elseif ($_GET['action'] === 'supprimer') {
        unset($_SESSION['panier'][$id_cible]);
    } elseif ($_GET['action'] === 'mettre_cote') {
        unset($_SESSION['panier'][$id_cible]);
        if (isset($_SESSION['user_id'])) {
            $pdo->prepare("INSERT IGNORE INTO wishlist (id_user, id_jeu) VALUES (?, ?)")->execute([$_SESSION['user_id'], $id_cible]);
        } else {
            $_SESSION['wishlist'][$id_cible] = true;
        }
    } elseif ($_GET['action'] === 'ajouter_panier') {
        $_SESSION['panier'][$id_cible] = 1;
        if (isset($_SESSION['user_id'])) {
            $pdo->prepare("DELETE FROM wishlist WHERE id_user = ? AND id_jeu = ?")->execute([$_SESSION['user_id'], $id_cible]);
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
        // Si le jeu est en solde, on prend le prix soldé
        $prix_effectif = ($jeu['prix_solde'] > 0) ? $jeu['prix_solde'] : $jeu['prix'];
        $sous_total += $prix_effectif * $_SESSION['panier'][$jeu['id_jeu']];
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
<body>
    <?php include 'navbar.php'; ?>

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
                        <div style="background:#1a1c24; border:1px solid #2a2c35; border-radius:8px; padding:18px 20px; display:flex; align-items:center; gap:18px; margin-bottom:16px;">
                            <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>"
                                 style="width:65px; height:85px; object-fit:cover; border-radius:5px; flex-shrink:0;">
                            <div style="flex:1; min-width:0;">
                                <h3 style="margin:0 0 8px; font-size:18px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                    <?php echo htmlspecialchars($jeu['titre']); ?>
                                </h3>
                                <div style="font-size:14px; color:#9aa0b4; margin-bottom:10px;">
                                    <?php echo number_format($jeu['prix'], 2); ?> € / unité
                                </div>
                                <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                                    <!-- Boutons quantité -->
                                    <div style="display:flex; align-items:center; gap:0; background:#0a0b0f; border:1px solid #333; border-radius:6px; overflow:hidden;">
                                        <a href="panier.php?action=diminuer&id_jeu=<?php echo $jeu['id_jeu']; ?>"
                                           style="padding:6px 14px; color:white; text-decoration:none; font-size:18px; font-weight:bold; transition:.15s;"
                                           onmouseover="this.style.background='#ff4757'" onmouseout="this.style.background=''">−</a>
                                        <span style="padding:6px 14px; font-size:16px; font-weight:700; border-left:1px solid #333; border-right:1px solid #333; min-width:36px; text-align:center;">
                                            <?php echo $qte; ?>
                                        </span>
                                        <a href="panier.php?action=augmenter&id_jeu=<?php echo $jeu['id_jeu']; ?>"
                                           style="padding:6px 14px; color:white; text-decoration:none; font-size:18px; font-weight:bold; transition:.15s;"
                                           onmouseover="this.style.background='#2ecc71'" onmouseout="this.style.background=''">+</a>
                                    </div>
                                    <a href="panier.php?action=mettre_cote&id_jeu=<?php echo $jeu['id_jeu']; ?>"
                                       style="color:#f1c40f; text-decoration:none; font-size:13px;">⭐ Mettre de côté</a>
                                    <a href="panier.php?action=supprimer&id_jeu=<?php echo $jeu['id_jeu']; ?>"
                                       style="color:#ff4757; text-decoration:none; font-size:13px;">🗑 Retirer</a>
                                </div>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <div style="font-size:22px; font-weight:700; color:#2ecc71; white-space:nowrap;">
                                    <?php echo number_format($jeu['prix'] * $qte, 2, ',', ' '); ?> €
                                </div>
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

    <?php include 'footer.php'; ?>
</body>
</html>