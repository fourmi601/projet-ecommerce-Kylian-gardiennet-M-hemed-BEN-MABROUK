<?php
// facture PDF — accès restreint au proprio de la commande (ou admin)
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$id_commande = (int)($_GET['id'] ?? 0);
if (!$id_commande) { header('Location: mes_commandes.php'); exit(); }

// Récupérer la commande (uniquement si elle appartient à cet utilisateur, sauf admin)
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$sql_cmd = $is_admin
    ? "SELECT c.*, u.pseudo, u.email FROM commande c JOIN utilisateur u ON u.id_user = c.id_user WHERE c.id_commande = ?"
    : "SELECT c.*, u.pseudo, u.email FROM commande c JOIN utilisateur u ON u.id_user = c.id_user WHERE c.id_commande = ? AND c.id_user = ?";
$stmtCmd = $pdo->prepare($sql_cmd);
$is_admin ? $stmtCmd->execute([$id_commande]) : $stmtCmd->execute([$id_commande, $_SESSION['user_id']]);
$commande = $stmtCmd->fetch();

if (!$commande) { header('Location: mes_commandes.php'); exit(); }

// Lignes de la commande
$items = $pdo->prepare("
    SELECT co.cle_cd, co.prix_achat, j.titre, j.image, c.nom_cat
    FROM contenir co
    JOIN jeu j ON j.id_jeu = co.id_jeu
    JOIN categorie c ON c.id_cat = j.id_cat
    WHERE co.id_commande = ?
");
$items->execute([$id_commande]);
$lignes = $items->fetchAll();

$num_facture = 'DG-' . str_pad($id_commande, 6, '0', STR_PAD_LEFT);
$date_fmt    = date('d/m/Y', strtotime($commande['date_achat']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture <?php echo $num_facture; ?> - Digital Games</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Fond de page sombre (UI autour de la facture) ── */
        body {
            font-family: 'Rajdhani', sans-serif;
            background: #0a0b0f;
            color: #e0e0e0;
            min-height: 100vh;
        }

        .screen-bar {
            background: #13151e;
            border-bottom: 1px solid #252836;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: #0055cc; color: white; border: none;
            padding: 10px 22px; border-radius: 6px;
            font-family: inherit; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: .2s;
        }
        .btn-print:hover { background: #1a6fff; }
        .btn-back { color: #9aa0b4; text-decoration: none; font-size: 14px; margin-right: 16px; }

        .invoice-wrap {
            max-width: 820px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }

        /* ══════════════════════════════════════════════
           LA FACTURE = document papier, TOUJOURS clair
           (écran, PDF et impression cohérents)
        ══════════════════════════════════════════════ */
        .invoice {
            background: #ffffff;
            border-radius: 12px;
            padding: 48px 52px;
            color: #1a1c24;
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }

        /* ── En-tête ── */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 28px;
            border-bottom: 2px solid #e1e5ec;
        }
        .inv-logo img { height: 100px; border-radius: 6px; }
        .inv-logo p   { font-size: 13px; color: #6b7280; margin-top: 8px; line-height: 1.5; }

        .inv-meta { text-align: right; }
        .inv-meta h1 { font-size: 34px; font-weight: 700; color: #1a1c24; letter-spacing: .08em; }
        .inv-meta .inv-num  { font-size: 17px; color: #0055cc; font-weight: 700; margin: 6px 0 4px; }
        .inv-meta .inv-date { font-size: 14px; color: #6b7280; }

        /* ── Parties vendeur / client ── */
        .inv-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 36px;
        }
        .inv-box {
            background: #f8f9fb;
            border: 1px solid #e1e5ec;
            border-radius: 8px;
            padding: 18px 22px;
        }
        .inv-box h3     { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; color: #6b7280; margin-bottom: 10px; }
        .inv-box p      { font-size: 15px; line-height: 1.7; color: #374151; }
        .inv-box strong { color: #1a1c24; font-weight: 700; }

        /* ── Tampon PAYÉ ── */
        .inv-paid-stamp {
            display: inline-block;
            border: 3px solid #16a34a;
            color: #16a34a;
            padding: 6px 20px;
            border-radius: 6px;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: .15em;
            transform: rotate(-4deg);
            margin-bottom: 16px;
        }

        /* ── Tableau articles ── */
        .inv-table { width: 100%; border-collapse: collapse; margin-bottom: 28px; }
        .inv-table th {
            font-size: 11px; text-transform: uppercase; letter-spacing: .08em;
            color: #6b7280; padding: 10px 14px; text-align: left;
            border-bottom: 2px solid #e1e5ec; background: #f8f9fb;
        }
        .inv-table th:last-child { text-align: right; }
        .inv-table td {
            padding: 14px; border-bottom: 1px solid #f0f2f5;
            vertical-align: middle; font-size: 15px; color: #1a1c24;
        }
        .inv-table td:last-child { text-align: right; font-weight: 700; color: #16a34a; }
        .inv-table tr:last-child td { border-bottom: none; }
        .inv-table .cle-cd { font-family: monospace; font-size: 12px; color: #6b7280; display: block; margin-top: 4px; }

        /* ── Totaux ── */
        .inv-total { margin-left: auto; width: 290px; }
        .inv-total-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; font-size: 15px; color: #6b7280;
            border-bottom: 1px solid #f0f2f5;
        }
        .inv-total-row:last-child {
            border-bottom: none; font-size: 19px; font-weight: 700;
            color: #1a1c24; padding-top: 14px;
        }
        .inv-total-row span:last-child { color: #16a34a; }

        /* ── Séparateur avant totaux ── */
        .inv-sep { border: none; border-top: 1px solid #e1e5ec; margin: 10px 0 18px; }

        /* ── Pied de page ── */
        .inv-footer {
            margin-top: 40px; padding-top: 24px;
            border-top: 1px solid #e1e5ec;
            text-align: center; font-size: 13px;
            color: #9ca3af; line-height: 1.7;
        }
        .inv-footer strong { color: #374151; }

        /* ── Impression ── */
        @media print {
            @page { size: A4; margin: 15mm 12mm; }
            body        { background: #fff !important; }
            .screen-bar { display: none !important; }
            .invoice-wrap { margin: 0; padding: 0; max-width: 100%; }
            .invoice    { box-shadow: none; border-radius: 0; padding: 0; }
        }
    </style>
</head>
<body>

<!-- Barre navigation (masquée à l'impression) -->
<div class="screen-bar">
    <div>
        <a href="mes_commandes.php" class="btn-back">← Mes commandes</a>
        <span style="color:#9aa0b4; font-size:14px;">Facture <?php echo $num_facture; ?></span>
    </div>
    <div style="display:flex; gap:10px;">
        <button class="btn-print" id="btn-dl" onclick="telechargerPDF()">⬇️ Télécharger PDF</button>
        <button class="btn-print" onclick="window.print()" style="background:#374151;">🖨️ Imprimer</button>
    </div>
</div>

<!-- Facture -->
<div class="invoice-wrap">
<div class="invoice">

    <!-- En-tête -->
    <div class="inv-header">
        <div class="inv-logo">
            <img src="assets/img/logo.jpg" alt="Digital Games">
            <p>Boutique de clés CD officielles<br>contact@digitalgames.fr</p>
        </div>
        <div class="inv-meta">
            <h1>FACTURE</h1>
            <div class="inv-num"><?php echo $num_facture; ?></div>
            <div class="inv-date">Émise le <?php echo $date_fmt; ?></div>
        </div>
    </div>

    <!-- Parties -->
    <div class="inv-parties">
        <div class="inv-box">
            <h3>Vendeur</h3>
            <p>
                <strong>Digital Games SAS</strong><br>
                Boutique de clés CD<br>
                contact@digitalgames.fr
            </p>
        </div>
        <div class="inv-box">
            <h3>Client</h3>
            <p>
                <strong><?php echo htmlspecialchars($commande['pseudo']); ?></strong><br>
                <?php echo htmlspecialchars($commande['email']); ?><br>
                Commande du <?php echo $date_fmt; ?>
            </p>
        </div>
    </div>

    <!-- Tampon PAYÉ -->
    <div style="text-align:right; margin-bottom:20px;">
        <span class="inv-paid-stamp">PAYÉ ✓</span>
    </div>

    <!-- Tableau articles -->
    <table class="inv-table">
        <thead>
            <tr>
                <th style="width:50%">Article</th>
                <th>Catégorie</th>
                <th>Clé d'activation</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lignes as $l): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($l['titre']); ?></strong></td>
                <td style="color:#9aa0b4;"><?php echo htmlspecialchars($l['nom_cat']); ?></td>
                <td><span class="cle-cd"><?php echo htmlspecialchars($l['cle_cd']); ?></span></td>
                <td><?php echo number_format($l['prix_achat'], 2); ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="inv-total">
        <?php $sous_total = array_sum(array_column($lignes, 'prix_achat')); ?>
        <div class="inv-total-row">
            <span>Sous-total</span>
            <span><?php echo number_format($sous_total, 2); ?> €</span>
        </div>
        <div class="inv-total-row">
            <span>TVA (0% — clés numériques)</span>
            <span>0,00 €</span>
        </div>
        <div class="inv-total-row">
            <span>Total payé</span>
            <span><?php echo number_format($commande['prix_total'], 2); ?> €</span>
        </div>
    </div>

    <!-- Pied de facture -->
    <div class="inv-footer">
        <p>Merci pour votre achat sur <strong>Digital Games</strong>.</p>
        <p>Cette facture tient lieu de justificatif de paiement. Conservez-la pour vos archives.</p>
        <p style="margin-top:8px; font-size:12px;">Digital Games SAS · Projet BTS · N° de commande <?php echo $num_facture; ?></p>
    </div>

</div>
</div>

<script>
function telechargerPDF() {
    const btn = document.getElementById('btn-dl');
    btn.innerHTML = '⏳ Génération…';
    btn.disabled = true;

    html2pdf()
        .set({
            margin:      [10, 10, 10, 10],
            filename:    'Facture-<?php echo $num_facture; ?>.pdf',
            image:       { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
            jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' }
        })
        .from(document.querySelector('.invoice'))
        .save()
        .then(() => {
            btn.innerHTML = '✅ Téléchargé !';
            setTimeout(() => {
                btn.innerHTML = '⬇️ Télécharger PDF';
                btn.disabled = false;
            }, 2500);
        });
}
</script>
</body>
</html>
