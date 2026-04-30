<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: connexion.php'); exit(); }

// date_sortie incluse pour savoir si la clé précommande est encore verrouillée
$stmt = $pdo->prepare("
    SELECT c.id_commande, c.date_achat, c.prix_total,
           j.titre, j.image, j.date_sortie, co.cle_cd
    FROM commande c
    JOIN contenir co ON c.id_commande = co.id_commande
    JOIN jeu j ON co.id_jeu = j.id_jeu
    WHERE c.id_user = ?
    ORDER BY c.date_achat DESC
");
$stmt->execute([$_SESSION['user_id']]);
$resultats = $stmt->fetchAll();

$now = date('Y-m-d H:i:s');
$commandes = [];
foreach ($resultats as $row) {
    $id_cmd = $row['id_commande'];
    if (!isset($commandes[$id_cmd])) {
        $commandes[$id_cmd] = ['date' => $row['date_achat'], 'total' => $row['prix_total'], 'jeux' => []];
    }
    $commandes[$id_cmd]['jeux'][] = [
        'titre'          => $row['titre'],
        'image'          => $row['image'],
        'cle'            => $row['cle_cd'],
        'est_precommande'=> !empty($row['date_sortie']) && $row['date_sortie'] > $now,
        'date_sortie'    => $row['date_sortie'],
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
<body>
    <?php include 'navbar.php'; ?>

    <div class="container" style="padding: 40px; max-width: 900px; margin: auto;">
        <h2 style="font-size: 32px; margin-bottom: 30px; border-left: 5px solid #0055cc; padding-left: 15px;">📦 Mes commandes</h2>

        <?php if (empty($commandes)): ?>
            <div style="background: #1a1c24; padding: 40px; border-radius: 8px; text-align: center; border: 1px solid #2a2c35;">
                <p style="color: #b3b3b3; font-size: 18px;">Vous n'avez encore passé aucune commande.</p>
                <a href="catalogue.php" class="btn-hero" style="display:inline-block; margin-top:20px; text-decoration:none; padding:10px 24px;">Explorer le catalogue</a>
            </div>
        <?php else: ?>
            <?php foreach ($commandes as $id_cmd => $cmd): ?>
                <div style="background: #1a1c24; border: 1px solid #2a2c35; border-radius: 8px; margin-bottom: 30px; overflow: hidden;">

                    <div style="background: #2a2c35; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span style="color: #9aa0b4; font-size: 13px; text-transform: uppercase; letter-spacing: .06em;">Commande effectuée le</span><br>
                            <strong><?php echo date('d/m/Y à H:i', strtotime($cmd['date'])); ?></strong>
                        </div>
                        <div>
                            <span style="color: #9aa0b4; font-size: 13px; text-transform: uppercase; letter-spacing: .06em;">Total</span><br>
                            <strong style="color: #2ecc71;"><?php echo number_format($cmd['total'], 2); ?> €</strong>
                        </div>
                        <div>
                            <span style="color: #9aa0b4; font-size: 13px; text-transform: uppercase; letter-spacing: .06em;">N° de commande</span><br>
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
                            <div style="display:flex; align-items:center; border-bottom:1px solid #2a2c35; padding-bottom:16px; margin-bottom:16px; gap:18px;">
                                <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>"
                                     style="width:55px; height:75px; object-fit:cover; border-radius:5px; flex-shrink:0;">
                                <div style="flex:1; min-width:0;">
                                    <h3 style="margin:0 0 5px; font-size:17px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        <?php echo htmlspecialchars($jeu['titre']); ?>
                                    </h3>
                                    <?php if (!$jeu['est_precommande']): ?>
                                        <a href="bibliotheque.php" style="color:#3498db; font-size:13px; text-decoration:none;">
                                            Activer dans ma bibliothèque →
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Clé toujours visible, mais activation bloquée si précommande -->
                                <div style="text-align:center; flex-shrink:0;">
                                    <div style="background:#0f1014; padding:10px 16px; border-radius:6px; border:1px dashed <?php echo $jeu['est_precommande'] ? '#f39c12' : '#444'; ?>;">
                                        <span style="font-size:11px; color:<?php echo $jeu['est_precommande'] ? '#f39c12' : '#666'; ?>; display:block; text-transform:uppercase; letter-spacing:.06em; font-weight:700;">
                                            <?php echo $jeu['est_precommande'] ? 'CLÉ PRÉCOMMANDE' : 'CLÉ CD'; ?>
                                        </span>
                                        <strong style="color:white; letter-spacing:1px; cursor:pointer; font-size:14px;"
                                                onclick="copierCle('<?php echo htmlspecialchars($jeu['cle']); ?>', this)"
                                                title="Cliquez pour copier">
                                            <?php echo htmlspecialchars($jeu['cle']); ?> 📋
                                        </strong>
                                        <?php if ($jeu['est_precommande']): ?>
                                            <span style="display:block; font-size:11px; color:#f39c12; margin-top:4px;">
                                                ⏳ Activation le <?php echo date('d/m/Y', strtotime($jeu['date_sortie'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function copierCle(cle, el) {
        navigator.clipboard.writeText(cle).then(function() {
            var orig = el.innerHTML;
            el.innerHTML = cle + ' ✅ Copié !';
            el.style.color = '#2ecc71';
            setTimeout(function() { el.innerHTML = orig; el.style.color = ''; }, 2000);
        });
    }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
