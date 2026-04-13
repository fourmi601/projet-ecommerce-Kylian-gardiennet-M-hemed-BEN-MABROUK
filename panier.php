<?php
session_start();
require 'db.php'; // On se connecte à la BDD
 
// --- 1. GESTION DU CODE PROMO ---
$message_promo = '';
if (isset($_POST['appliquer_promo'])) {
    $code_saisi = strtoupper($_POST['code_promo']);
    $stmt = $pdo->prepare("SELECT * FROM code_promo WHERE code = ?");
    $stmt->execute([$code_saisi]);
    $promo = $stmt->fetch();
 
    if ($promo) {
        $date_jour = date('Y-m-d');
        // Vérification des tes nouvelles conditions !
        if ($promo['date_expiration'] && $promo['date_expiration'] < $date_jour) {
            $message_promo = "❌ Ce code a expiré.";
        } elseif ($promo['nb_utilisations'] >= $promo['max_utilisations']) {
            $message_promo = "❌ Ce code n'est plus disponible (limite atteinte).";
        } else {
            $_SESSION['promo'] = $promo; // Code valide ! On le garde en session
            $message_promo = "✅ Code appliqué (-" . $promo['reduction_pourcentage'] . "%) !";
        }
    } else {
        $message_promo = "❌ Code invalide.";
    }
}
 
// --- 2. RÉCUPÉRATION DES VRAIS JEUX DU PANIER ---
$jeux_panier = [];
$sous_total = 0;
 
if (!empty($_SESSION['panier'])) {
    // Si le panier n'est pas vide, on récupère les ID des jeux (ex: "1, 4, 5")
    $ids = array_keys($_SESSION['panier']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?'; // Prépare les "?" pour le SQL
   
    $stmt = $pdo->prepare("SELECT j.*, c.nom_cat FROM jeu j JOIN categorie c ON j.id_cat = c.id_cat WHERE id_jeu IN ($placeholders)");
    $stmt->execute($ids);
    $jeux_panier = $stmt->fetchAll();
 
    // On calcule le sous-total
    foreach ($jeux_panier as $jeu) {
        $quantite = $_SESSION['panier'][$jeu['id_jeu']];
        $sous_total += $jeu['prix'] * $quantite;
    }
}
 
// --- 3. CALCULS FINAUX ---
$total = $sous_total;
$montant_reduction = 0;
if (isset($_SESSION['promo'])) {
    $montant_reduction = ($sous_total * $_SESSION['promo']['reduction_pourcentage']) / 100;
    $total = $sous_total - $montant_reduction;
}
 
// On sauvegarde le total final dans la session pour la page de paiement !
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
<body style="background: #0b0c10; color: white;">
    <nav>
        <div class="logo-container"><a href="index.php"><img src="assets/img/logo.jpg" alt="Logo" class="site-logo"></a></div>
        <div class="nav-links"><a href="index.php">Accueil</a> <a href="contact.php">Contact</a></div>
        <div class="user-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="#" class="active">👤 <?php echo $_SESSION['pseudo']; ?></a>
            <?php else: ?>
                <a href="connexion.php" class="active">👤 Connexion</a>
            <?php endif; ?>
        </div>
    </nav>
 
    <div class="container">
        <h2 class="section-title">Mon Panier</h2>
 
        <div class="cart-container" style="display: flex; gap: 40px; flex-wrap: wrap;">
           
            <div class="cart-items" style="flex: 2; min-width: 300px;">
                <?php if (empty($jeux_panier)): ?>
                    <div style="background: #1a1c24; padding: 40px; border-radius: 8px; text-align: center;">
                        <h3 style="color: #b3b3b3;">Votre panier est tristement vide 😢</h3>
                        <a href="index.php" class="btn-hero" style="display: inline-block; margin-top: 20px; text-decoration: none;">Voir le catalogue</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($jeux_panier as $jeu):
                        $quantite = $_SESSION['panier'][$jeu['id_jeu']];
                    ?>
                    <div class="cart-item" style="background: #1a1c24; border: 1px solid #2a2c35; border-radius: 8px; padding: 15px; display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                        <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="Jeu" style="width: 80px; height: 100px; object-fit: cover; border-radius: 4px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0; font-size: 20px;"><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <span style="color: #3498db; font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($jeu['nom_cat']); ?></span>
                        </div>
                        <div>
                            <span style="background: #2a2c35; padding: 5px 10px; border-radius: 4px;">Qté: <?php echo $quantite; ?></span>
                        </div>
                        <div style="font-size: 18px; font-weight: bold; color: #2ecc71;">
                            <?php echo number_format($jeu['prix'] * $quantite, 2); ?> €
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
 
            <div class="cart-summary" style="flex: 1; min-width: 300px; background: #1a1c24; padding: 30px; border-radius: 8px; border: 1px solid #2a2c35; height: fit-content;">
                <h3 style="margin-top: 0;">Résumé de la commande</h3>
                <hr style="border-color: #333; margin: 15px 0;">
               
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <span>Sous-total :</span>
                    <span><?php echo number_format($sous_total, 2); ?> €</span>
                </div>
 
                <form action="panier.php" method="POST" style="display: flex; gap: 10px; margin: 20px 0;">
                    <input type="text" name="code_promo" placeholder="Avez-vous un code ?" style="flex:1; padding: 10px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white;">
                    <button type="submit" name="appliquer_promo" style="background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer;">OK</button>
                </form>
                <?php if (!empty($message_promo)): ?>
                    <p style="color: <?php echo strpos($message_promo, '✅') !== false ? '#2ecc71' : '#ff4757'; ?>; font-size: 14px; margin-top: -10px; margin-bottom: 15px;">
                        <?php echo $message_promo; ?>
                    </p>
                <?php endif; ?>
 
                <?php if (isset($_SESSION['promo'])): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; color: #2ecc71;">
                        <span>Promo (<?php echo $_SESSION['promo']['code']; ?>) :</span>
                        <span>- <?php echo number_format($montant_reduction, 2); ?> €</span>
                    </div>
                <?php endif; ?>
 
                <hr style="border-color: #333; margin: 15px 0;">
               
                <div style="display: flex; justify-content: space-between; font-size: 22px; font-weight: bold; margin-bottom: 25px;">
                    <span>Total :</span>
                    <span style="color: #2ecc71;"><?php echo number_format($total, 2); ?> €</span>
                </div>
               
                <?php if (!empty($jeux_panier)): ?>
                   <a href="process_paiement.php" style="display: block; width: 100%; background: #ff4757; color: white; text-align: center; padding: 15px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 18px;">VALIDER LE PANIER</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>