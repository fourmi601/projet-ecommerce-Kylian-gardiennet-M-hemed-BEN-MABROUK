<?php
session_start();
require 'db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: catalogue.php');
    exit();
}

$id_jeu = (int)$_GET['id'];

// On récupère le jeu
// On récupère le jeu ET les infos du vendeur s'il y en a un
$stmt = $pdo->prepare("
    SELECT j.*, c.nom_cat, u.pseudo AS nom_vendeur 
    FROM jeu j 
    LEFT JOIN categorie c ON j.id_cat = c.id_cat 
    LEFT JOIN utilisateur u ON j.id_vendeur = u.id_user
    WHERE j.id_jeu = ?
");
$stmt->execute([$id_jeu]);
$jeu = $stmt->fetch();

if (!$jeu) {
    header('Location: catalogue.php');
    exit();
}

// Vérifier si le jeu est en wishlist pour cet utilisateur
$est_en_wishlist = false;
if (isset($_SESSION['user_id'])) {
    $checkW = $pdo->prepare("SELECT id_jeu FROM wishlist WHERE id_user = ? AND id_jeu = ?");
    $checkW->execute([$_SESSION['user_id'], $id_jeu]);
    if ($checkW->fetch()) {
        $est_en_wishlist = true;
    }
}

// LOGIQUE DE PRÉCOMMANDE
$est_precommande = false;
$date_actuelle = date('Y-m-d H:i:s');
if (!empty($jeu['date_sortie']) && $jeu['date_sortie'] > $date_actuelle) {
    $est_precommande = true;
}

// TRADUCTION NOTE STEAM
$steam_pourcentage = $jeu['note_steam'];

if ($steam_pourcentage === null || $steam_pourcentage == 0) {
    $steam_texte = 'Aucun avis';
    $steam_couleur = '#8f98a0';
    $steam_pourcentage = '-';
} elseif ($steam_pourcentage >= 95) {
    $steam_texte = 'Extrêmement positives';
    $steam_couleur = '#66c0f4';
} elseif ($steam_pourcentage >= 80) {
    $steam_texte = 'Très positives';
    $steam_couleur = '#66c0f4';
} elseif ($steam_pourcentage >= 70) {
    $steam_texte = 'Plutôt positives';
    $steam_couleur = '#66c0f4';
} elseif ($steam_pourcentage >= 40) {
    $steam_texte = 'Variables';
    $steam_couleur = '#b9a074';
} elseif ($steam_pourcentage >= 20) {
    $steam_texte = 'Plutôt négatives';
    $steam_couleur = '#a34c25';
} else {
    $steam_texte = 'Extrêmement négatives';
    $steam_couleur = '#a34c25';
}

// Gestion de l'avis client
$message_avis = '';
if (isset($_POST['submit_avis']) && isset($_SESSION['user_id'])) {
    $note = (float)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    
    if ($note >= 1 && $note <= 5 && !empty($commentaire)) {
        $check = $pdo->prepare("SELECT id_avis FROM avis WHERE id_jeu = ? AND id_user = ?");
        $check->execute([$id_jeu, $_SESSION['user_id']]);
        if ($check->fetch()) {
            $message_avis = "<div style='color:#ff4757; margin-bottom:15px;'>❌ Vous avez déjà donné votre avis.</div>";
        } else {
            $insert = $pdo->prepare("INSERT INTO avis (id_jeu, id_user, note, commentaire) VALUES (?, ?, ?, ?)");
            $insert->execute([$id_jeu, $_SESSION['user_id'], $note, $commentaire]);
            $message_avis = "<div style='color:#2ecc71; margin-bottom:15px;'>✅ Avis publié avec succès !</div>";
        }
    }
}

// On récupère les avis
$stmtAvis = $pdo->prepare("SELECT a.*, u.pseudo FROM avis a JOIN utilisateur u ON a.id_user = u.id_user WHERE a.id_jeu = ? ORDER BY a.date_avis DESC");
$stmtAvis->execute([$id_jeu]);
$les_avis = $stmtAvis->fetchAll();

$moyenne_joueurs = 0;
if (count($les_avis) > 0) {
    $total_notes = array_sum(array_column($les_avis, 'note'));
    $moyenne_joueurs = round($total_notes / count($les_avis), 1);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($jeu['titre']); ?> - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dg-star-box { display: inline-block; position: relative; font-size: 24px; color: #444; line-height: 1; font-family: Arial, sans-serif; }
        .dg-star-box::before { content: "★★★★★"; }
        .dg-star-fill { position: absolute; top: 0; left: 0; white-space: nowrap; overflow: hidden; color: #f1c40f; }
        .dg-star-fill::before { content: "★★★★★"; }
        .timer-box { display: flex; gap: 10px; margin-top: 15px; }
        .time-block { background: #0b0c10; border-radius: 4px; padding: 10px; text-align: center; min-width: 50px; border: 1px solid #333; }
        .time-value { font-size: 24px; font-weight: bold; color: #f39c12; display: block; }
        .time-label { font-size: 11px; color: #b3b3b3; text-transform: uppercase; }
    </style>
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <?php include 'navbar.php'; ?>

    <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        
        <div style="display: flex; gap: 40px; background: #1a1c24; padding: 30px; border-radius: 12px; border: 1px solid #2a2c35; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 300px;">
                <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="Cover" style="width: 100%; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            </div>

            <div style="flex: 2; min-width: 300px; display: flex; flex-direction: column;">
                
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <span style="color: #3498db; font-weight: bold; text-transform: uppercase;"><?php echo htmlspecialchars($jeu['nom_cat'] ?? 'PC'); ?></span>
                        <h1 style="margin: 10px 0; font-size: 42px;"><?php echo htmlspecialchars($jeu['titre']); ?></h1>
                        <div style="margin-bottom: 15px;">
    <?php if (!empty($jeu['nom_vendeur'])): ?>
        <span style="background: #f39c12; color: #1a1c24; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
            🏪 Vendu par : <?php echo htmlspecialchars($jeu['nom_vendeur']); ?>
        </span>
    <?php else: ?>
         <span style="background: #3498db; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
            ✅ Vendu par Digital Games
        </span>
    <?php endif; ?>
</div>
                        <div style="color: #b3b3b3; font-size: 16px; margin-bottom: 20px;">
                            Date de sortie : 
                            <strong style="color: white;">
                                <?php echo !empty($jeu['date_sortie']) ? date('d/m/Y', strtotime($jeu['date_sortie'])) : 'Non annoncée'; ?>
                            </strong>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <div style="background: #171a21; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #2a475e; min-width: 160px; display: flex; flex-direction: column; justify-content: center;">
                        <span style="font-size: 18px; font-weight: bold; color: <?php echo $steam_couleur; ?>; display: block; margin-bottom: 5px;">
                            <?php echo $steam_texte; ?>
                        </span>
                        <?php if($steam_pourcentage !== '-'): ?>
                            <span style="font-size: 13px; color: #8f98a0;"><?php echo $steam_pourcentage; ?>% d'avis positifs</span>
                        <?php endif; ?>
                        <span style="display: block; font-size: 11px; color: #4b6a81; text-transform: uppercase; margin-top: 8px; border-top: 1px solid #2a475e; padding-top: 8px;">
                            ÉVALUATIONS STEAM
                        </span>
                    </div>
                    
                    <div style="background: #2a2c35; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #333; min-width: 140px;">
                        <?php $pourcentage_etoiles_global = ($moyenne_joueurs / 5) * 100; ?>
                        <div style="margin-bottom: 5px;">
                            <div class="dg-star-box" title="<?php echo $moyenne_joueurs; ?>/5">
                                <div class="dg-star-fill" style="width: <?php echo $pourcentage_etoiles_global; ?>%;"></div>
                            </div>
                        </div>
                        <span style="font-size: 20px; font-weight: bold; color: #fff;">
                            <?php echo $moyenne_joueurs > 0 ? $moyenne_joueurs . '<span style="font-size:14px; color:#b3b3b3;">/5</span>' : '-'; ?>
                        </span>
                        <span style="display: block; font-size: 11px; color: #b3b3b3; text-transform: uppercase; margin-top: 5px;">Avis du site</span>
                    </div>
                </div>

                <p style="color: #b3b3b3; font-size: 18px; line-height: 1.6; flex-grow: 1;">
                    <?php echo nl2br(htmlspecialchars($jeu['description'])); ?>
                </p>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #333; display: flex; justify-content: space-between; align-items: flex-end;">
                    
                    <div>
                        <?php if($est_precommande && $jeu['prix'] > 0): ?>
                            <span style="background: #f39c12; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">PRÉCOMMANDE</span>
                            <div class="timer-box" data-date="<?php echo $jeu['date_sortie']; ?>">
                                <div class="time-block"><span class="time-value jours">00</span><span class="time-label">Jours</span></div>
                                <div class="time-block"><span class="time-value heures">00</span><span class="time-label">Hrs</span></div>
                                <div class="time-block"><span class="time-value minutes">00</span><span class="time-label">Min</span></div>
                                <div class="time-block"><span class="time-value secondes">00</span><span class="time-label">Sec</span></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 20px;">
                        
                        <?php if ($jeu['prix'] > 0): // SI LE JEU A UN PRIX ?>
                            <span style="font-size: 36px; font-weight: bold; color: #2ecc71;">
                                <?php echo number_format($jeu['prix_solde'] > 0 ? $jeu['prix_solde'] : $jeu['prix'], 2); ?> €
                            </span>
                            
                            <div style="display: flex; gap: 10px;">
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <a href="ajouter_wishlist.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" style="background: #2a2c35; border: 1px solid <?php echo $est_en_wishlist ? '#ff4757' : '#333'; ?>; color: <?php echo $est_en_wishlist ? '#ff4757' : 'white'; ?>; padding: 15px; border-radius: 6px; text-decoration: none; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: 0.2s;" title="Wishlist">
                                        <?php echo $est_en_wishlist ? '❤️' : '🤍'; ?>
                                    </a>
                                <?php endif; ?>

                                <a href="ajouter_panier.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" style="background: <?php echo $est_precommande ? '#f39c12' : '#ff4757'; ?>; color: white; padding: 15px 30px; border-radius: 6px; text-decoration: none; font-size: 20px; font-weight: bold; transition: 0.2s;">
                                    🛒 <?php echo $est_precommande ? 'PRÉCOMMANDER' : 'AJOUTER'; ?>
                                </a>
                            </div>

                        <?php else: // SI LE JEU EST A 0€ ?>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <a href="ajouter_wishlist.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" style="background: #2a2c35; border: 1px solid <?php echo $est_en_wishlist ? '#ff4757' : '#333'; ?>; color: <?php echo $est_en_wishlist ? '#ff4757' : 'white'; ?>; padding: 15px; border-radius: 6px; text-decoration: none; font-size: 20px; display: flex; align-items: center; justify-content: center; transition: 0.2s;" title="Wishlist">
                                        <?php echo $est_en_wishlist ? '❤️' : '🤍'; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <span style="font-size: 28px; font-weight: bold; color: #f39c12; margin-right: 15px;">⏳ Bientôt disponible</span>
                                <button disabled style="background: #2a2c35; color: #666; padding: 15px 30px; border-radius: 6px; border: 1px solid #333; font-size: 20px; font-weight: bold; cursor: not-allowed;">
                                    INDISPONIBLE
                                </button>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 60px; display: flex; gap: 40px; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 300px;">
                <h2 style="font-size: 24px; margin-bottom: 20px; border-left: 5px solid #3498db; padding-left: 15px;">Avis de la communauté (<?php echo count($les_avis); ?>)</h2>
                <?php if (empty($les_avis)): ?>
                    <p style=\"color: #b3b3b3; background: #1a1c24; padding: 20px; border-radius: 8px;\">Aucun avis pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($les_avis as $avis): ?>
                        <div style="background: #1a1c24; padding: 20px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #2a2c35;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <strong style="color: #3498db;">👤 <?php echo htmlspecialchars($avis['pseudo']); ?></strong>
                                <?php $pourcentage_avis = ($avis['note'] / 5) * 100; ?>
                                <div class="dg-star-box" style="font-size: 18px;">
                                    <div class="dg-star-fill" style="width: <?php echo $pourcentage_avis; ?>%;"></div>
                                </div>
                            </div>
                            <p style="color: #e0e0e0; margin: 0; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($avis['commentaire'])); ?></p>
                            <span style="display: block; margin-top: 10px; font-size: 12px; color: #666;">Posté le <?php echo date('d/m/Y', strtotime($avis['date_avis'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div style="flex: 1; min-width: 300px;">
                <div style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35; position: sticky; top: 20px;">
                    <h3>Publier un avis</h3>
                    <?php echo $message_avis; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form action="jeu.php?id=<?php echo $id_jeu; ?>" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                            <select name="note" required style="padding: 10px; background: #0b0c10; border: 1px solid #333; color: white;">
                                <option value="5">5/5 - Parfait</option>
                                <option value="4.5">4.5/5 - Excellent</option>
                                <option value="4">4/5 - Très bon</option>
                                <option value="3.5">3.5/5 - Bon</option>
                                <option value="3">3/5 - Moyen</option>
                                <option value="2">2/5 - Décevant</option>
                                <option value="1">1/5 - Mauvais</option>
                            </select>
                            <textarea name="commentaire" required rows="4" placeholder="Votre avis..." style="padding: 10px; background: #0b0c10; border: 1px solid #333; color: white;"></textarea>
                            <button type="submit" name="submit_avis" style="background: #ff4757; color: white; padding: 12px; border: none; font-weight: bold; cursor: pointer;">ENVOYER</button>
                        </form>
                    <?php else: ?>
                        <p style="color: #b3b3b3; font-size: 14px;">Connectez-vous pour donner votre avis.</p>
                        <a href="connexion.php" style="display: block; text-align: center; background: #3498db; color: white; padding: 10px; text-decoration: none; font-weight: bold; border-radius: 4px;">SE CONNECTER</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if($est_precommande && $jeu['prix'] > 0): ?>
    <script>
        function updateTimers() {
            const box = document.querySelector('.timer-box');
            if(!box) return;
            const now = new Date().getTime();
            const releaseDate = new Date(box.getAttribute('data-date')).getTime();
            const distance = releaseDate - now;

            if (distance > 0) {
                box.querySelector('.jours').innerText = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
                box.querySelector('.heures').innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
                box.querySelector('.minutes').innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
                box.querySelector('.secondes').innerText = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');
            } else {
                box.innerHTML = '<span style="color: #2ecc71; font-weight: bold;">DISPONIBLE !</span>';
            }
        }
        setInterval(updateTimers, 1000);
        updateTimers();
    </script>
    <?php endif; ?>
</body>
</html>