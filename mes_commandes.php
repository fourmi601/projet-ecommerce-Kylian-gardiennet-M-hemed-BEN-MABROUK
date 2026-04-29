<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: connexion.php'); exit(); }

$stmt = $pdo->prepare("
    SELECT c.id_commande, c.date_achat, c.prix_total, j.titre, j.image, co.cle_cd 
    FROM commande c 
    JOIN contenir co ON c.id_commande = co.id_commande 
    JOIN jeu j ON co.id_jeu = j.id_jeu 
    WHERE c.id_user = ? 
    ORDER BY c.date_achat DESC
");
$stmt->execute([$_SESSION['user_id']]);
$resultats = $stmt->fetchAll();

// On trie les résultats pour regrouper les jeux par numéro de commande
$commandes = [];
foreach ($resultats as $row) {
    $id_cmd = $row['id_commande'];
    if (!isset($commandes[$id_cmd])) {
        $commandes[$id_cmd] = [
            'date' => $row['date_achat'],
            'total' => $row['prix_total'],
            'jeux' => []
        ];
    }
    $commandes[$id_cmd]['jeux'][] = [
        'titre' => $row['titre'],
        'image' => $row['image'],
        'cle' => $row['cle_cd']
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Commandes - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <?php include 'navbar.php'; ?>

    <div class="container" style="padding: 40px; max-width: 900px; margin: auto;">
        <h1 style="border-bottom: 2px solid #3498db; padding-bottom: 10px;">📦 Historique de mes commandes</h1>

        <?php if (empty($commandes)): ?>
            <p style="color: #b3b3b3; text-align: center; padding: 40px; font-size: 18px;">Vous n'avez passé aucune commande.</p>
        <?php else: ?>
            <?php foreach ($commandes as $id_cmd => $cmd): ?>
                <div style="background: #1a1c24; border: 1px solid #333; border-radius: 8px; margin-bottom: 30px; overflow: hidden;">
                    
                    <div style="background: #2a2c35; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="color: #b3b3b3; font-size: 14px;">COMMANDE EFFECTUÉE LE</span><br>
                            <strong><?php echo date('d/m/Y à H:i', strtotime($cmd['date'])); ?></strong>
                        </div>
                        <div>
                            <span style="color: #b3b3b3; font-size: 14px;">TOTAL</span><br>
                            <strong style="color: #2ecc71;"><?php echo number_format($cmd['total'], 2); ?> €</strong>
                        </div>
                        <div>
                            <span style="color: #b3b3b3; font-size: 14px;">N° DE COMMANDE</span><br>
                            <strong>#<?php echo $id_cmd; ?></strong>
                        </div>
                        <div>
                            <a href="facture.php?id=<?php echo $id_cmd; ?>" target="_blank"
                               style="background:#0055cc; color:white; padding:8px 16px; border-radius:6px; text-decoration:none; font-weight:700; font-size:14px;">
                                🧾 Facture
                            </a>
                        </div>
                    </div>

                    <div style="padding: 20px;">
                        <?php foreach ($cmd['jeux'] as $jeu): ?>
                            <div style="display: flex; align-items: center; border-bottom: 1px solid #333; padding-bottom: 15px; margin-bottom: 15px;">
                                <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" style="width: 60px; height: 80px; object-fit: cover; border-radius: 4px; margin-right: 20px;">
                                <div style="flex: 1;">
                                    <h3 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                                    <a href="bibliotheque.php" style="color: #3498db; font-size: 14px; text-decoration: none;">Aller activer ce jeu →</a>
                                </div>
                                <div style="background: #0f1014; padding: 10px; border-radius: 4px; border: 1px dashed #666; text-align: center;">
                                    <span style="font-size: 12px; color: #666; display: block;">CLÉ À COPIER</span>
                                    <strong style="color: white; letter-spacing: 1px; cursor: pointer;" onclick="navigator.clipboard.writeText('<?php echo $jeu['cle']; ?>'); alert('Clé copiée !');">
                                        <?php echo $jeu['cle']; ?> 📋
                                    </strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>