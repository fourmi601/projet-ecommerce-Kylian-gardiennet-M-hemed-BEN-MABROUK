<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['panier'])) { header('Location: index.php'); exit(); }

$id_user = $_SESSION['user_id'];
$total = $_SESSION['total_a_payer'];
$date_achat = date('Y-m-d H:i:s');
$cles_generees = []; // On va stocker les clés ici pour les afficher

try {
    $pdo->prepare("INSERT INTO commande (date_achat, prix_total, id_user) VALUES (?, ?, ?)")->execute([$date_achat, $total, $id_user]);
    $id_commande = $pdo->lastInsertId();

    function genererCleCD() { return strtoupper(substr(md5(uniqid()), 0, 4) . '-' . substr(md5(uniqid()), 4, 4) . '-' . substr(md5(uniqid()), 8, 4)); }

    $ids = array_keys($_SESSION['panier']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $jeux = $pdo->prepare("SELECT id_jeu, titre, image, prix FROM jeu WHERE id_jeu IN ($placeholders)");
    $jeux->execute($ids);
    
    $stmtContenir = $pdo->prepare("INSERT INTO contenir (id_jeu, id_commande, prix_achat, cle_cd) VALUES (?, ?, ?, ?)");

    foreach ($jeux->fetchAll() as $jeu) {
        $quantite = $_SESSION['panier'][$jeu['id_jeu']];
        for ($i = 0; $i < $quantite; $i++) {
            $cle_unique = genererCleCD();
            $stmtContenir->execute([$jeu['id_jeu'], $id_commande, $jeu['prix'], $cle_unique]);
            
            // On garde les infos pour les afficher en bas !
            $cles_generees[] = ['titre' => $jeu['titre'], 'image' => $jeu['image'], 'cle' => $cle_unique];
        }
    }

    unset($_SESSION['panier']); unset($_SESSION['total_a_payer']); if(isset($_SESSION['promo'])) unset($_SESSION['promo']);

} catch (Exception $e) { die("Erreur : " . $e->getMessage()); }
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
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">
    <div class="container" style="max-width: 800px; margin: 50px auto; background: #1a1c24; padding: 40px; border-radius: 8px;">
        <h1 style="color: #2ecc71; text-align: center;">✅ Commande N°<?php echo $id_commande; ?> réussie !</h1>
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
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="bibliotheque.php" class="btn-hero" style="text-decoration: none;">ACTIVER MON JEU DANS MA BIBLIOTHÈQUE</a>
        </div>
    </div>

    <script>
        function copierCle(cle, element) {
            navigator.clipboard.writeText(cle).then(() => {
                element.className = 'cle-visible'; // Enlève le flou
                element.innerText = cle + " (Copié !)"; // Affiche copié
                setTimeout(() => { element.innerText = cle; }, 2000);
            });
        }
    </script>
</body>
</html>