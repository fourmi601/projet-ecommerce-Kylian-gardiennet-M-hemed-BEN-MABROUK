<?php
// retour banque → verif token → génère clés CD → insert commande/contenir/histo
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: erreur_paiement.php?code=non_connecte&message=' . urlencode("Vous devez être connecté pour accéder à la confirmation de commande."));
    exit();
}
if (empty($_SESSION['panier'])) {
    header('Location: erreur_paiement.php?code=panier_vide&message=' . urlencode("Le panier est vide ou a déjà été traité."));
    exit();
}

$status       = $_GET['status'] ?? '';
$token_banque = $_GET['token']  ?? '';

if ($status !== 'success' || empty($token_banque)) {
    $msg = ($status === 'cancel')
        ? "Vous avez annulé le paiement sur l'interface bancaire."
        : "La transaction n'a pas été validée par la banque (statut : " . htmlspecialchars($status ?: 'absent') . ").";
    header('Location: erreur_paiement.php?code=token_invalide&message=' . urlencode($msg));
    exit();
}

$id_user    = $_SESSION['user_id'];
$total      = $_SESSION['total_a_payer'];
$date_achat = date('Y-m-d H:i:s');
$cles_generees = [];

try {
    $pdo->prepare("INSERT INTO commande (date_achat, prix_total, id_user) VALUES (?, ?, ?)")->execute([$date_achat, $total, $id_user]);
    $id_commande = $pdo->lastInsertId();

    function genererCleCD() {
        return strtoupper(bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)));
    }

    $ids = array_keys($_SESSION['panier']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $jeux = $pdo->prepare("SELECT id_jeu, titre, image, prix FROM jeu WHERE id_jeu IN ($placeholders)");
    $jeux->execute($ids);
    
    $stmtContenir = $pdo->prepare("INSERT INTO contenir (id_jeu, id_commande, prix_achat, cle_cd) VALUES (?, ?, ?, ?)");
    $stmtHisto    = $pdo->prepare("INSERT INTO historique_ventes (id_jeu, prix_paye, date_vente) VALUES (?, ?, NOW())");
    $stmtVentes   = $pdo->prepare("UPDATE jeu SET ventes = ventes + 1 WHERE id_jeu = ?");

    foreach ($jeux->fetchAll() as $jeu) {
        $quantite = $_SESSION['panier'][$jeu['id_jeu']];
        for ($i = 0; $i < $quantite; $i++) {
            $cle_unique = genererCleCD();
            $prix_final = $jeu['prix'];
            $stmtContenir->execute([$jeu['id_jeu'], $id_commande, $prix_final, $cle_unique]);
            $stmtHisto->execute([$jeu['id_jeu'], $prix_final]);
            $stmtVentes->execute([$jeu['id_jeu']]);
            $cles_generees[] = ['titre' => $jeu['titre'], 'image' => $jeu['image'], 'cle' => $cle_unique];
        }
    }

    unset($_SESSION['panier']); 
    unset($_SESSION['total_a_payer']); 
    if(isset($_SESSION['promo'])) unset($_SESSION['promo']);

} catch (Exception $e) {
    header('Location: erreur_paiement.php?code=commande_echec&message=' . urlencode("Erreur technique lors de l'enregistrement de la commande.") . '&contexte=' . urlencode($e->getMessage()));
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Achats - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .cle-floutee { filter: blur(6px); cursor: pointer; transition: 0.3s; user-select: none; }
        .cle-floutee:hover { filter: blur(2px); }
        .cle-visible { filter: none; color: #2ecc71; user-select: all; cursor: default; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container" style="max-width: 800px; margin: 50px auto; background: #1a1c24; padding: 40px; border-radius: 8px;">
        <h1 style="color: #2ecc71; text-align: center;">✅ Commande N°<?php echo $id_commande; ?> réussie !</h1>
        
        <p style="text-align: center; color: #666; font-size: 14px; margin-top: -10px;">
            Réf. transaction : <?php echo substr(htmlspecialchars($token_banque), 0, 8); ?>****
        </p>

        <p style="text-align: center; color: #b3b3b3;">Cliquez sur la zone floutée pour révéler et copier votre clé CD.</p>

        <div style="margin-top: 40px;">
            <?php foreach($cles_generees as $item): ?>
                <div style="display: flex; align-items: center; background: #0f1014; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #333;">
                    <img src="assets/img/<?php echo $item['image']; ?>" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px; margin-right: 20px;">
                    <div style="flex: 1;">
                        <h3 style="margin: 0; font-size: 20px;"><?php echo htmlspecialchars($item['titre']); ?></h3>
                    </div>
                    <div style="background: #1a1c24; border: 1px dashed #666; padding: 10px 20px; border-radius: 4px; text-align: center;">
                        <span style="font-size: 12px; color: #666; display: block;">CLÉ D'ACTIVATION</span>
                        <strong class="cle-floutee" id="cle-<?php echo $item['cle']; ?>" onclick="copierCle('<?php echo $item['cle']; ?>', this)" style="font-size: 20px; letter-spacing: 2px;">
                            <?php echo $item['cle']; ?>
                        </strong>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px; display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
            <a href="bibliotheque.php" style="text-decoration:none; padding:14px 28px; background:#2ecc71; color:white; border-radius:6px; font-weight:bold; font-size:16px;">ACTIVER DANS MA BIBLIOTHÈQUE</a>
            <a href="facture.php?id=<?php echo $id_commande; ?>" target="_blank" style="text-decoration:none; padding:14px 28px; background:#0055cc; color:white; border-radius:6px; font-weight:bold; font-size:16px;">🧾 TÉLÉCHARGER LA FACTURE</a>
        </div>
    </div>

    <script>
        function copierCle(cle, element) {
            navigator.clipboard.writeText(cle).then(() => {
                element.className = 'cle-visible';
                element.innerText = cle + " (Copié !)";
                setTimeout(() => { element.innerText = cle; }, 2000);
            });
        }
    </script>
</body>
</html>