<?php
session_start();
require 'db.php';

$date_actuelle = date('Y-m-d H:i:s');

// Récupère tous les jeux dont la date de sortie est dans le futur
$stmt = $pdo->prepare("
    SELECT j.*, c.nom_cat
    FROM jeu j
    LEFT JOIN categorie c ON j.id_cat = c.id_cat
    WHERE j.date_sortie > ?
    ORDER BY j.date_sortie ASC
");
$stmt->execute([$date_actuelle]);
$jeux_futurs = $stmt->fetchAll();

// Wishlist de l'utilisateur connecté (pour afficher le bon état du cœur)
$ma_wishlist = [];
if (isset($_SESSION['user_id'])) {
    $stmtW = $pdo->prepare("SELECT id_jeu FROM wishlist WHERE id_user = ?");
    $stmtW->execute([$_SESSION['user_id']]);
    $ma_wishlist = $stmtW->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prochaines Sorties — Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* Cartes précommande */
        .preorder-card {
            background: #13151e;
            border: 1px solid #252836;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: transform .2s, border-color .2s;
        }
        .preorder-card:hover {
            transform: translateY(-4px);
            border-color: #f39c12;
        }
        .preorder-card .card-thumb {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .preorder-card .card-body {
            padding: 18px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        /* Badge précommande */
        .badge-preorder-tag {
            background: #f39c12;
            color: #1a1c24;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            display: inline-block;
            margin-bottom: 10px;
        }

        /* Timer */
        .timer-box { display: flex; gap: 8px; margin: 12px 0; }
        .time-block {
            background: #0a0b0f;
            border: 1px solid #252836;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
            min-width: 50px;
        }
        .time-value { font-size: 22px; font-weight: 700; color: #f39c12; display: block; }
        .time-label { font-size: 10px; color: #9aa0b4; text-transform: uppercase; }

        /* Bouton wishlist sur la carte */
        .btn-wishlist-preorder {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,.65);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 18px;
            cursor: pointer;
            transition: background .15s, transform .15s;
            z-index: 5;
        }
        .btn-wishlist-preorder:hover { background: rgba(0,0,0,.85); transform: scale(1.1); }

        /* Mode clair */
        body.light-theme .preorder-card  { background: #fff; border-color: #e1e5ec; }
        body.light-theme .preorder-card:hover { border-color: #f39c12; }
        body.light-theme .time-block     { background: #f8f9fb; border-color: #e1e5ec; }
        body.light-theme .time-value     { color: #d97706; }
        body.light-theme .time-label     { color: #6b7280; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container" style="max-width:1200px; margin:40px auto; padding:0 20px;">

        <h1 style="font-size:34px; border-left:5px solid #f39c12; padding-left:16px; margin-bottom:8px;">
            ⏳ Prochaines Sorties
        </h1>
        <p style="color:#9aa0b4; margin-bottom:32px; padding-left:21px;">
            <?php echo count($jeux_futurs); ?> jeu<?php echo count($jeux_futurs) > 1 ? 'x' : ''; ?> à venir —
            précommandez maintenant, recevez la clé le jour de la sortie.
        </p>

        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(290px, 1fr)); gap:24px;">

            <?php if (empty($jeux_futurs)): ?>
                <div style="grid-column:1/-1; text-align:center; padding:60px 20px; color:#9aa0b4;">
                    <p style="font-size:48px; margin:0 0 12px;">🎮</p>
                    <p style="font-size:18px;">Aucune sortie prévue pour le moment. Revenez bientôt !</p>
                </div>

            <?php else: ?>
                <?php foreach ($jeux_futurs as $jeu):
                    $en_wishlist = in_array($jeu['id_jeu'], $ma_wishlist);
                ?>
                <div class="preorder-card">

                    <!-- Bouton wishlist (cœur) -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="ajouter_wishlist.php?id_jeu=<?php echo $jeu['id_jeu']; ?>"
                           class="btn-wishlist-preorder"
                           title="<?php echo $en_wishlist ? 'Retirer de la wishlist' : 'Ajouter à la wishlist'; ?>">
                            <?php echo $en_wishlist ? '❤️' : '🤍'; ?>
                        </a>
                    <?php endif; ?>

                    <!-- Image cliquable → fiche jeu -->
                    <a href="jeu.php?id=<?php echo $jeu['id_jeu']; ?>">
                        <img class="card-thumb"
                             src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>"
                             alt="<?php echo htmlspecialchars($jeu['titre']); ?>">
                    </a>

                    <div class="card-body">
                        <span class="badge-preorder-tag">Précommande</span>

                        <!-- Titre cliquable → fiche jeu -->
                        <a href="jeu.php?id=<?php echo $jeu['id_jeu']; ?>"
                           style="text-decoration:none; color:inherit;">
                            <h3 style="margin:0 0 6px; font-size:20px; line-height:1.3;">
                                <?php echo htmlspecialchars($jeu['titre']); ?>
                            </h3>
                        </a>

                        <span style="color:#3498db; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; display:block;">
                            <?php echo htmlspecialchars($jeu['nom_cat'] ?? 'PC'); ?>
                        </span>

                        <span style="color:#9aa0b4; font-size:13px;">
                            Sortie prévue le <strong style="color:#f39c12;">
                                <?php echo date('d/m/Y', strtotime($jeu['date_sortie'])); ?>
                            </strong>
                        </span>

                        <!-- Compte à rebours -->
                        <div class="timer-box" data-date="<?php echo $jeu['date_sortie']; ?>">
                            <div class="time-block"><span class="time-value jours">--</span><span class="time-label">Jours</span></div>
                            <div class="time-block"><span class="time-value heures">--</span><span class="time-label">Hrs</span></div>
                            <div class="time-block"><span class="time-value minutes">--</span><span class="time-label">Min</span></div>
                            <div class="time-block"><span class="time-value secondes">--</span><span class="time-label">Sec</span></div>
                        </div>

                        <!-- Prix + boutons -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:auto; padding-top:14px; border-top:1px solid #1e2130;">
                            <span style="font-size:22px; font-weight:700; color:#2ecc71;">
                                <?php if ($jeu['prix'] > 0): ?>
                                    <?php echo number_format($jeu['prix'], 2); ?> €
                                <?php else: ?>
                                    <span style="color:#9aa0b4; font-size:14px;">Prix à venir</span>
                                <?php endif; ?>
                            </span>

                            <?php if ($jeu['prix'] > 0): ?>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <a href="jeu.php?id=<?php echo $jeu['id_jeu']; ?>"
                                       style="background:#2a2c35; color:#ccc; text-decoration:none; padding:9px 12px; border-radius:5px; font-size:13px; border:1px solid #333; transition:.15s;"
                                       onmouseover="this.style.borderColor='#f39c12';this.style.color='white'"
                                       onmouseout="this.style.borderColor='#333';this.style.color='#ccc'">
                                        Voir le jeu
                                    </a>
                                    <a href="ajouter_panier.php?id_jeu=<?php echo $jeu['id_jeu']; ?>"
                                       style="background:#f39c12; color:#1a1c24; text-decoration:none; padding:9px 14px; border-radius:5px; font-weight:700; font-size:13px; transition:.15s;"
                                       onmouseover="this.style.background='#e67e22'"
                                       onmouseout="this.style.background='#f39c12'">
                                        🕐 Précommander
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="jeu.php?id=<?php echo $jeu['id_jeu']; ?>"
                                   style="background:#2a2c35; color:#ccc; text-decoration:none; padding:9px 14px; border-radius:5px; font-size:13px; border:1px solid #333;">
                                    En savoir plus
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

    <script>
    // Compte à rebours pour chaque carte
    function updateTimers() {
        var now = new Date().getTime();
        document.querySelectorAll('.timer-box').forEach(function(box) {
            var target   = new Date(box.getAttribute('data-date')).getTime();
            var distance = target - now;

            if (distance > 0) {
                box.querySelector('.jours').innerText    = Math.floor(distance / 86400000).toString().padStart(2, '0');
                box.querySelector('.heures').innerText   = Math.floor((distance % 86400000) / 3600000).toString().padStart(2, '0');
                box.querySelector('.minutes').innerText  = Math.floor((distance % 3600000) / 60000).toString().padStart(2, '0');
                box.querySelector('.secondes').innerText = Math.floor((distance % 60000) / 1000).toString().padStart(2, '0');
            } else {
                box.innerHTML = '<span style="color:#2ecc71; font-weight:700;">DISPONIBLE !</span>';
            }
        });
    }
    setInterval(updateTimers, 1000);
    updateTimers();
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
