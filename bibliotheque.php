<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) { header('Location: connexion.php'); exit(); }

$message = '';
$type_message = '';

if (isset($_POST['activer'])) {
    $cle_saisie = trim($_POST['cle_cd']);

    // On récupère l'id_jeu ET la date de sortie pour vérifier si la précommande est activable
    $stmt = $pdo->prepare("
        SELECT co.id_jeu, j.date_sortie, j.titre
        FROM contenir co
        JOIN jeu j ON j.id_jeu = co.id_jeu
        WHERE co.cle_cd = ?
    ");
    $stmt->execute([$cle_saisie]);
    $jeu_concerne = $stmt->fetch();

    if ($jeu_concerne) {
        $maintenant = date('Y-m-d H:i:s');

        // Bloquer si le jeu est une précommande et pas encore sorti
        if (!empty($jeu_concerne['date_sortie']) && $jeu_concerne['date_sortie'] > $maintenant) {
            $date_affichee = date('d/m/Y à H\hi', strtotime($jeu_concerne['date_sortie']));
            $message       = "⏳ Ce jeu (\"" . htmlspecialchars($jeu_concerne['titre']) . "\") sort le <strong style='color:#f39c12;'>{$date_affichee}</strong>. L'activation sera possible à partir de cette date.";
            $type_message  = "#f39c12";
        } else {
            $stmtCheck = $pdo->prepare("SELECT * FROM bibliotheque WHERE cle_cd = ?");
            $stmtCheck->execute([$cle_saisie]);
            if ($stmtCheck->fetch()) {
                $message      = "❌ Cette clé a déjà été activée.";
                $type_message = "#ff4757";
            } else {
                $pdo->prepare("INSERT INTO bibliotheque (id_user, id_jeu, cle_cd) VALUES (?, ?, ?)")
                    ->execute([$_SESSION['user_id'], $jeu_concerne['id_jeu'], $cle_saisie]);
                $message      = "✅ Jeu activé avec succès ! Il est maintenant dans votre bibliothèque.";
                $type_message = "#2ecc71";
            }
        }
    } else {
        $message      = "❌ Clé CD invalide ou introuvable.";
        $type_message = "#ff4757";
    }
}

$stmtBiblio = $pdo->prepare("
    SELECT j.titre, j.image, c.nom_cat, b.date_activation 
    FROM bibliotheque b 
    JOIN jeu j ON b.id_jeu = j.id_jeu 
    JOIN categorie c ON j.id_cat = c.id_cat
    WHERE b.id_user = ? 
    ORDER BY b.date_activation DESC
");
$stmtBiblio->execute([$_SESSION['user_id']]);
$mes_jeux = $stmtBiblio->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma Bibliothèque - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <?php include 'navbar.php'; ?>

    <div class="container" style="padding: 40px; max-width: 1000px; margin: auto;">
        
        <div style="background: #1a1c24; border: 1px solid #333; padding: 30px; border-radius: 8px; margin-bottom: 40px; text-align: center;">
            <h2 style="margin-top: 0;">🔑 Activer un produit</h2>
            <p style="color: #b3b3b3;">Saisissez votre clé CD pour débloquer votre jeu.</p>
            
            <form action="bibliotheque.php" method="POST" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
                <input type="text" name="cle_cd" placeholder="Ex: ABCD-1234-WXYZ" required style="width: 300px; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #0f1014; color: white; text-align: center; font-size: 18px; letter-spacing: 2px;">
                <button type="submit" name="activer" class="btn-hero" style="border: none; cursor: pointer;">ACTIVER</button>
            </form>

            <?php if($message): ?>
                <p style="color: <?php echo $type_message; ?>; font-weight: bold; margin-top: 15px;"><?php echo $message; ?></p>
            <?php endif; ?>
        </div>

        <h2 style="border-bottom: 2px solid #3498db; padding-bottom: 10px;">📚 Mes Jeux (<?php echo count($mes_jeux); ?>)</h2>
        
        <?php if (empty($mes_jeux)): ?>
            <p style="color: #b3b3b3; text-align: center; padding: 40px;">Aucun jeu dans votre bibliothèque pour le moment.</p>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                <?php foreach ($mes_jeux as $jeu): ?>
                    <div style="background: #1a1c24; border-radius: 8px; overflow: hidden; border: 1px solid #2a2c35;">
                        <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                        <div style="padding: 15px; text-align: center;">
                            <h3 style="margin: 0 0 5px 0; font-size: 18px;"><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <span style="font-size: 12px; color: #b3b3b3;">Activé le <?php echo date('d/m/Y', strtotime($jeu['date_activation'])); ?></span><br>
                            <button style="background: #2ecc71; color: white; border: none; padding: 8px 15px; border-radius: 4px; margin-top: 15px; width: 100%; cursor: pointer;">▶ JOUER</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <?php include 'footer.php'; ?>
</body>
</html>