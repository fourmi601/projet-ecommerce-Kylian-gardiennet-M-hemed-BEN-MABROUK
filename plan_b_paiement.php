<?php
/*
 * Plan B paiement
 * On arrive ici quand la banque Ecotech est inaccessible (timeout).
 * Le panier est intact en session, donc l'utilisateur peut soit réessayer,
 * soit "réserver" sa commande pour revenir plus tard.
 *
 * Pour la réservation on stocke dans la table commande avec statut = 'en_attente'.
 * À toi d'ajouter la colonne si elle n'existe pas encore :
 *   ALTER TABLE commande ADD COLUMN statut VARCHAR(20) DEFAULT 'payee';
 */

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

// Si le panier est vide, pas grand chose à faire ici
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit();
}

$message     = '';
$id_reserve  = null;
$total       = $_SESSION['total_a_payer'] ?? 0;

// === Action : réserver la commande ===
if (isset($_POST['reserver'])) {
    try {
        // Vérifie si la colonne statut existe, sinon on la crée à la volée
        // (un peu brutal mais ça évite de toucher à phpMyAdmin)
        try {
            $pdo->query("SELECT statut FROM commande LIMIT 1");
        } catch (PDOException $e) {
            $pdo->exec("ALTER TABLE commande ADD COLUMN statut VARCHAR(20) DEFAULT 'payee'");
        }

        $pdo->prepare("INSERT INTO commande (date_achat, prix_total, id_user, statut) VALUES (NOW(), ?, ?, 'en_attente')")
            ->execute([$total, $_SESSION['user_id']]);

        $id_reserve = $pdo->lastInsertId();

        // On vide le panier maintenant qu'on a la réservation
        unset($_SESSION['panier']);
        unset($_SESSION['total_a_payer']);

        $message = 'ok';

    } catch (PDOException $e) {
        // La colonne statut n'existe peut-être pas et ALTER a foiré — on insère sans
        try {
            $pdo->prepare("INSERT INTO commande (date_achat, prix_total, id_user) VALUES (NOW(), ?, ?)")
                ->execute([$total, $_SESSION['user_id']]);
            $id_reserve = $pdo->lastInsertId();
            unset($_SESSION['panier'], $_SESSION['total_a_payer']);
            $message = 'ok';
        } catch (PDOException $e2) {
            $message = 'erreur';
        }
    }
}

// Récupérer les articles du panier pour les afficher
$jeux_panier = [];
if (!empty($_SESSION['panier'])) {
    $ids = array_keys($_SESSION['panier']);
    $ph  = implode(',', array_fill(0, count($ids), '?'));
    $st  = $pdo->prepare("SELECT id_jeu, titre, prix FROM jeu WHERE id_jeu IN ($ph)");
    $st->execute($ids);
    $jeux_panier = $st->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement indisponible — Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .planb-card {
            max-width: 640px; margin: 50px auto; padding: 0 20px;
        }
        .planb-box {
            background: #13151e; border: 1px solid #252836;
            border-radius: 12px; padding: 36px;
        }
        .planb-icon { font-size: 56px; text-align: center; margin-bottom: 16px; }
        .planb-title { font-size: 26px; font-weight: 700; text-align: center; margin: 0 0 8px; }
        .planb-sub   { color: #9aa0b4; text-align: center; margin: 0 0 28px; font-size: 15px; }
        .planb-btn {
            display: block; width: 100%; padding: 14px;
            border: none; border-radius: 7px; font-family: inherit;
            font-size: 16px; font-weight: 700; cursor: pointer;
            text-align: center; text-decoration: none;
            margin-bottom: 12px; transition: opacity .15s;
        }
        .planb-btn:hover { opacity: .88; }
        .planb-btn-primary   { background: #0055cc; color: #fff; }
        .planb-btn-secondary { background: #1e2130; color: #ccc; border: 1px solid #252836; }
        .planb-btn-warn      { background: #f39c12; color: #1a1c24; }
        .planb-separator { text-align: center; color: #3a3b45; margin: 16px 0; font-size: 13px; }
        .planb-cart { background: #0a0b0f; border-radius: 8px; padding: 16px; margin-bottom: 24px; }
        .planb-cart-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 15px; border-bottom: 1px solid #1e2130; }
        .planb-cart-row:last-child { border: none; font-weight: 700; color: #2ecc71; margin-top: 4px; }
        /* Compte à rebours "réessayer dans" */
        #planb-countdown { font-size: 28px; font-weight: 700; color: #f39c12; text-align: center; margin: 10px 0; }
        body.light-theme .planb-box { background: #fff; border-color: #e1e5ec; }
        body.light-theme .planb-cart { background: #f8f9fb; }
        body.light-theme .planb-cart-row { border-color: #f0f2f5; color: #2c3348; }
        body.light-theme .planb-sub { color: #6b7280; }
        body.light-theme .planb-btn-secondary { background: #f0f2f5; color: #4b5563; border-color: #d0d5e0; }
        body.light-theme #planb-countdown { color: #d97706; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="planb-card">

        <?php if ($message === 'ok' && $id_reserve): ?>
            <!-- ====== RÉSERVATION CONFIRMÉE ====== -->
            <div class="planb-box">
                <div class="planb-icon">✅</div>
                <h1 class="planb-title" style="color:#2ecc71;">Commande réservée !</h1>
                <p class="planb-sub">
                    Votre commande <strong>#<?php echo $id_reserve; ?></strong> est enregistrée.
                    Dès que le service de paiement est de nouveau disponible, tu pourras la finaliser depuis
                    <a href="mes_commandes.php" style="color:#0055cc;">Mes commandes</a>.
                </p>
                <p style="text-align:center; color:#9aa0b4; font-size:14px;">
                    Tes articles sont conservés, rien n'est débité pour l'instant.
                </p>
                <a href="index.php" class="planb-btn planb-btn-primary" style="margin-top:20px;">
                    Retour à l'accueil
                </a>
                <a href="mes_commandes.php" class="planb-btn planb-btn-secondary">
                    Voir mes commandes
                </a>
            </div>

        <?php elseif ($message === 'erreur'): ?>
            <!-- ====== ERREUR RÉSERVATION ====== -->
            <div class="planb-box">
                <div class="planb-icon">😕</div>
                <h1 class="planb-title" style="color:#ff4757;">Oups…</h1>
                <p class="planb-sub">Impossible d'enregistrer la réservation pour le moment. Ton panier est toujours là.</p>
                <a href="panier.php" class="planb-btn planb-btn-primary">Retour au panier</a>
            </div>

        <?php else: ?>
            <!-- ====== PAGE PRINCIPALE ====== -->
            <div class="planb-box">
                <div class="planb-icon">🔌</div>
                <h1 class="planb-title">Service de paiement indisponible</h1>
                <p class="planb-sub">
                    La banque Ecotech ne répond pas en ce moment.
                    C'est probablement temporaire — on réessaye dans quelques minutes.
                </p>

                <!-- Récap du panier -->
                <?php if (!empty($jeux_panier)): ?>
                <div class="planb-cart">
                    <?php foreach ($jeux_panier as $j): ?>
                        <div class="planb-cart-row">
                            <span><?php echo htmlspecialchars($j['titre']); ?>
                                  × <?php echo $_SESSION['panier'][$j['id_jeu']]; ?></span>
                            <span><?php echo number_format($j['prix'] * $_SESSION['panier'][$j['id_jeu']], 2); ?> €</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="planb-cart-row">
                        <span>Total</span>
                        <span><?php echo number_format($total, 2); ?> €</span>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Compte à rebours avant de réessayer -->
                <p style="text-align:center; color:#9aa0b4; font-size:14px; margin:0 0 4px;">
                    Réessai automatique dans
                </p>
                <div id="planb-countdown">5:00</div>

                <!-- Option 1 : réessayer maintenant -->
                <a href="paiement.php" class="planb-btn planb-btn-primary">
                    ↩ Réessayer le paiement maintenant
                </a>

                <div class="planb-separator">— ou —</div>

                <!-- Option 2 : réserver et revenir plus tard -->
                <form method="POST" action="plan_b_paiement.php">
                    <button type="submit" name="reserver" class="planb-btn planb-btn-warn">
                        📋 Réserver ma commande et payer plus tard
                    </button>
                </form>

                <div class="planb-separator">— ou —</div>

                <!-- Option 3 : retour au panier -->
                <a href="panier.php" class="planb-btn planb-btn-secondary">
                    ← Retour au panier
                </a>

                <p style="text-align:center; font-size:13px; color:#6b7280; margin-top:20px;">
                    Ton panier reste sauvegardé dans tous les cas.
                    Si le problème persiste, <a href="contact.php" style="color:#0055cc;">contacte-nous</a>.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Compte à rebours de 5 minutes avant de proposer de réessayer automatiquement
    var el = document.getElementById('planb-countdown');
    if (el) {
        var secondes = 300; // 5 minutes
        var timer = setInterval(function() {
            secondes--;
            var m = Math.floor(secondes / 60);
            var s = secondes % 60;
            el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
            if (secondes <= 0) {
                clearInterval(timer);
                // On tente automatiquement de relancer le paiement
                window.location.href = 'paiement.php';
            }
        }, 1000);
    }
    </script>
</body>
</html>
