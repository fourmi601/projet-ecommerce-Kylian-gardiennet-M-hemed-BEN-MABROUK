<?php
// monitoring : admin = global / tiers = ses jeux seulement //
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'tiers'])) {
    header('Location: index.php');
    exit();
}

$is_admin  = ($_SESSION['role'] === 'admin');
$id_user   = $_SESSION['user_id'];
$where_jeu = $is_admin ? "1=1" : "j.id_vendeur = " . (int)$id_user;

// Filtre période //
$periode = $_GET['periode'] ?? 'all';
$date_sql = match($periode) {
    '7j'  => "AND v.date_vente >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
    '30j' => "AND v.date_vente >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
    default => ""
};
$titre_periode = match($periode) {
    '7j'  => "7 derniers jours",
    '30j' => "30 derniers jours",
    default => "Toutes les périodes"
};

//  1. Globaux //
$statsGlobales = $pdo->query("
    SELECT COUNT(v.id_vente) AS total_vendu, COALESCE(SUM(v.prix_paye),0) AS ca_total
    FROM historique_ventes v JOIN jeu j ON v.id_jeu = j.id_jeu
    WHERE $where_jeu $date_sql
")->fetch();

// ── 2. Commandes (admin) / tiers filtrés //
if ($is_admin) {
    $nb_commandes   = $pdo->query("SELECT COUNT(*) FROM commande")->fetchColumn();
    $nb_utilisateurs = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='client'")->fetchColumn();
    $nb_jeux_total  = $pdo->query("SELECT COUNT(*) FROM jeu")->fetchColumn();
    $panier_moyen   = $pdo->query("SELECT AVG(prix_total) FROM commande")->fetchColumn();
} else {
    $nb_commandes   = $pdo->query("
        SELECT COUNT(DISTINCT co.id_commande) FROM contenir co
        JOIN jeu j ON co.id_jeu = j.id_jeu WHERE j.id_vendeur = $id_user
    ")->fetchColumn();
    $nb_utilisateurs = null;
    $nb_jeux_total   = $pdo->query("SELECT COUNT(*) FROM jeu WHERE id_vendeur = $id_user")->fetchColumn();
    $panier_moyen    = null;
}

// ── 3. Top 5 jeux //
$topJeux = $pdo->query("
    SELECT j.titre, COUNT(v.id_vente) AS ventes, SUM(v.prix_paye) AS ca
    FROM historique_ventes v JOIN jeu j ON v.id_jeu = j.id_jeu
    WHERE $where_jeu $date_sql
    GROUP BY j.id_jeu, j.titre ORDER BY ventes DESC LIMIT 5
")->fetchAll();

// ── 4. Ventes par catégorie //
$statsCat = $pdo->query("
    SELECT c.nom_cat, COUNT(v.id_vente) AS total, SUM(v.prix_paye) AS ca
    FROM historique_ventes v JOIN jeu j ON v.id_jeu = j.id_jeu
    JOIN categorie c ON j.id_cat = c.id_cat
    WHERE $where_jeu $date_sql GROUP BY c.id_cat, c.nom_cat HAVING total > 0
")->fetchAll();



$limit_days = ($periode === '7j') ? 7 : 30;
$caJours = $pdo->query("
    SELECT DATE(v.date_vente) AS jour, SUM(v.prix_paye) AS ca
    FROM historique_ventes v JOIN jeu j ON v.id_jeu = j.id_jeu
    WHERE $where_jeu AND v.date_vente >= DATE_SUB(NOW(), INTERVAL $limit_days DAY)
    GROUP BY jour ORDER BY jour ASC
")->fetchAll();

//  Der ventes //
$dernieresVentes = $pdo->query("
    SELECT j.titre, j.image, v.prix_paye, v.date_vente,
           u.pseudo AS acheteur
    FROM historique_ventes v
    JOIN jeu j ON v.id_jeu = j.id_jeu
    JOIN contenir co ON co.id_jeu = j.id_jeu
    JOIN commande cmd ON cmd.id_commande = co.id_commande
    JOIN utilisateur u ON u.id_user = cmd.id_user
    WHERE $where_jeu $date_sql
    GROUP BY v.id_vente
    ORDER BY v.date_vente DESC LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Monitoring - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .stat-card { background:#13151e; border:1px solid #252836; border-radius:10px; padding:22px 26px; }
        .stat-label { font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:#9aa0b4; margin-bottom:6px; }
        .stat-value { font-size:30px; font-weight:700; line-height:1; }
        .stat-sub   { font-size:13px; color:#9aa0b4; margin-top:6px; }
        .chart-box  { background:#13151e; border:1px solid #252836; border-radius:10px; padding:22px; }
        .chart-title{ font-size:15px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin:0 0 18px; color:#9aa0b4; }
        .vente-row  { display:flex; align-items:center; gap:14px; padding:10px 0; border-bottom:1px solid #1e2130; }
        .vente-row:last-child { border-bottom:none; }
        body.light-theme .stat-card,
        body.light-theme .chart-box { background:#fff; border-color:#e1e5ec; }
        body.light-theme .stat-label,
        body.light-theme .stat-sub,
        body.light-theme .chart-title { color:#6b7280; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div style="max-width:1280px; margin:40px auto; padding:0 24px;">

        
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <div>
                <h1 style="margin:0; font-size:26px;">📊 <?php echo $is_admin ? 'Monitoring Global' : 'Mes Stats Vendeur'; ?></h1>
                <span style="color:#9aa0b4; font-size:14px;"><?php echo $titre_periode; ?></span>
            </div>
            <form action="admin_stats.php" method="GET" style="display:flex; gap:10px; align-items:center;">
                <select name="periode" style="padding:9px 14px; background:#13151e; border:1px solid #333; color:white; border-radius:6px; font-family:inherit;">
                    <option value="all" <?php if($periode==='all') echo 'selected'; ?>>Toutes les ventes</option>
                    <option value="30j" <?php if($periode==='30j') echo 'selected'; ?>>30 derniers jours</option>
                    <option value="7j"  <?php if($periode==='7j')  echo 'selected'; ?>>7 derniers jours</option>
                </select>
                <button type="submit" style="background:#0055cc; color:white; border:none; padding:9px 18px; border-radius:6px; font-weight:700; cursor:pointer;">Filtrer</button>
                <?php if ($is_admin): ?>
                    <a href="admin.php" style="color:#9aa0b4; text-decoration:none; font-size:14px; margin-left:8px;">← Admin</a>
                <?php else: ?>
                    <a href="vendeur.php" style="color:#9aa0b4; text-decoration:none; font-size:14px; margin-left:8px;">← Mon tableau de bord</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($statsGlobales['total_vendu'] == 0 && count($topJeux) === 0): ?>
            <div class="stat-card" style="text-align:center; padding:50px; border-color:#ff4757;">
                <p style="font-size:40px; margin:0 0 12px;">📭</p>
                <h2 style="color:#ff4757; margin:0 0 8px;">Aucune donnée sur cette période</h2>
                <p style="color:#9aa0b4;">Les ventes confirmées apparaîtront ici en temps réel.</p>
            </div>
        <?php else: ?>

        <!-- KPIs -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:28px;">
            <div class="stat-card">
                <div class="stat-label">Chiffre d'affaires</div>
                <div class="stat-value" style="color:#2ecc71;"><?php echo number_format($statsGlobales['ca_total'],2); ?> €</div>
                <div class="stat-sub"><?php echo $titre_periode; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Clés vendues</div>
                <div class="stat-value" style="color:#3498db;"><?php echo $statsGlobales['total_vendu']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Commandes</div>
                <div class="stat-value" style="color:#9b59b6;"><?php echo $nb_commandes; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><?php echo $is_admin ? 'Clients' : 'Jeux en vente'; ?></div>
                <div class="stat-value" style="color:#f39c12;"><?php echo $is_admin ? $nb_utilisateurs : $nb_jeux_total; ?></div>
            </div>
            <?php if ($is_admin && $panier_moyen > 0): ?>
            <div class="stat-card">
                <div class="stat-label">Panier moyen</div>
                <div class="stat-value" style="color:#2ecc71;"><?php echo number_format($panier_moyen,2); ?> €</div>
            </div>
            <?php endif; ?>
        </div>

    
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:28px;">

            <div class="chart-box">
                <p class="chart-title">Évolution du CA (<?php echo $limit_days; ?> jours)</p>
                <canvas id="chartCA" style="max-height:260px;"></canvas>
            </div>

            <div class="chart-box">
                <p class="chart-title">Ventes par catégorie</p>
                <canvas id="chartCat" style="max-height:260px;"></canvas>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px;">

            <div class="chart-box">
                <p class="chart-title">Top 5 jeux</p>
                <canvas id="chartJeux" style="max-height:220px;"></canvas>
            </div>

            <div class="chart-box">
                <p class="chart-title">Dernières ventes</p>
                <?php if (empty($dernieresVentes)): ?>
                    <p style="color:#9aa0b4; text-align:center; margin-top:30px;">Aucune vente récente.</p>
                <?php else: ?>
                    <?php foreach ($dernieresVentes as $v): ?>
                    <div class="vente-row">
                        <img src="assets/img/<?php echo htmlspecialchars($v['image']); ?>" style="width:38px;height:50px;object-fit:cover;border-radius:4px;flex-shrink:0;">
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:14px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($v['titre']); ?></div>
                            <div style="font-size:12px;color:#9aa0b4;"><?php echo htmlspecialchars($v['acheteur']); ?> · <?php echo date('d/m H:i', strtotime($v['date_vente'])); ?></div>
                        </div>
                        <div style="font-weight:700;color:#2ecc71;font-size:15px;"><?php echo number_format($v['prix_paye'],2); ?>€</div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <script>
        const chartColors = ['#3498db','#ff4757','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e74c3c'];
        const gridColor  = '#1e2130';
        const textColor  = '#9aa0b4';
        const defaults   = Chart.defaults;
        defaults.color   = textColor;

        // Évolution CA //
        new Chart(document.getElementById('chartCA'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($caJours,'jour')); ?>,
                datasets: [{
                    label: 'CA (€)',
                    data: <?php echo json_encode(array_column($caJours,'ca')); ?>,
                    borderColor: '#2ecc71', backgroundColor: 'rgba(46,204,113,.12)',
                    fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#2ecc71'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: gridColor } },
                    y: { beginAtZero: true, grid: { color: gridColor } }
                }
            }
        });

        // Top 5 jeux //
        new Chart(document.getElementById('chartJeux'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($topJeux,'titre')); ?>,
                datasets: [{ label: 'Ventes', data: <?php echo json_encode(array_column($topJeux,'ventes')); ?>, backgroundColor: '#3498db', borderRadius: 5 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, grid: { color: gridColor } }, y: { grid: { display: false } } }
            }
        });

        // Donut catégories //
        new Chart(document.getElementById('chartCat'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($statsCat,'nom_cat')); ?>,
                datasets: [{ data: <?php echo json_encode(array_column($statsCat,'total')); ?>, backgroundColor: chartColors, borderWidth: 0, hoverOffset: 6 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 12, boxWidth: 12 } } }
            }
        });
        </script>

        <?php endif; ?>
    </div>
</body>
</html>
