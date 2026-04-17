<?php
session_start();
require 'db.php';

// Autoriser Admins ET Tiers (Vendeurs)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'tiers'])) {
    header('Location: index.php'); 
    exit(); 
}

$is_admin = ($_SESSION['role'] === 'admin');
$id_user = $_SESSION['user_id'];

// L'admin voit tout, le vendeur ne voit que ses jeux
$where_clause = $is_admin ? "1=1" : "j.id_vendeur = " . (int)$id_user;

// --- FILTRE PAR PÉRIODE ---
$periode_sql = "";
$titre_periode = "Global";

if (isset($_GET['periode'])) {
    if ($_GET['periode'] == '7j') {
        $periode_sql = " AND v.date_vente >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $titre_periode = "7 derniers jours";
    } elseif ($_GET['periode'] == '30j') {
        $periode_sql = " AND v.date_vente >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $titre_periode = "30 derniers jours";
    }
}

// 1. VRAI Chiffre d'affaires et Ventes totales (basé sur l'historique)
$sql_global = "
    SELECT 
        COUNT(v.id_vente) as total_vendu, 
        COALESCE(SUM(v.prix_paye), 0) as ca_total 
    FROM historique_ventes v 
    JOIN jeu j ON v.id_jeu = j.id_jeu 
    WHERE $where_clause $periode_sql
";
$statsGlobales = $pdo->query($sql_global)->fetch();

// 2. Top 5 des jeux les plus vendus sur la période
$sql_top = "
    SELECT j.titre, COUNT(v.id_vente) as ventes 
    FROM historique_ventes v 
    JOIN jeu j ON v.id_jeu = j.id_jeu 
    WHERE $where_clause $periode_sql 
    GROUP BY j.id_jeu, j.titre 
    ORDER BY ventes DESC 
    LIMIT 5
";
$topJeux = $pdo->query($sql_top)->fetchAll();

// 3. Ventes par catégorie sur la période
$sql_cat = "
    SELECT c.nom_cat, COUNT(v.id_vente) as total 
    FROM historique_ventes v 
    JOIN jeu j ON v.id_jeu = j.id_jeu 
    JOIN categorie c ON j.id_cat = c.id_cat 
    WHERE $where_clause $periode_sql 
    GROUP BY c.id_cat, c.nom_cat 
    HAVING total > 0
";
$statsCat = $pdo->query($sql_cat)->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Monitoring - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">
    
    <nav style="padding: 20px; background: #1a1c24; display: flex; justify-content: space-between;">
        <a href="mon_compte.php" style="color: #3498db; text-decoration: none; font-weight: bold;">← RETOUR À MON COMPTE</a>
        <span>Connecté en tant que : <strong><?php echo $_SESSION['pseudo']; ?></strong> (<?php echo strtoupper($_SESSION['role']); ?>)</span>
    </nav>

    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #9b59b6; padding-bottom: 10px; margin-bottom: 30px;">
            <h1 style="margin: 0; color: #9b59b6;">📊 Monitoring <?php echo !$is_admin ? "Vendeur" : "Global"; ?></h1>
            
            <div>
                <form action="admin_stats.php" method="GET" style="display: flex; gap: 10px;">
                    <select name="periode" style="padding: 8px; background: #1a1c24; border: 1px solid #333; color: white; border-radius: 4px;">
                        <option value="all" <?php if(!isset($_GET['periode']) || $_GET['periode'] == 'all') echo 'selected'; ?>>Toutes les ventes</option>
                        <option value="30j" <?php if(isset($_GET['periode']) && $_GET['periode'] == '30j') echo 'selected'; ?>>30 derniers jours</option>
                        <option value="7j" <?php if(isset($_GET['periode']) && $_GET['periode'] == '7j') echo 'selected'; ?>>7 derniers jours</option>
                    </select>
                    <button type="submit" style="background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Filtrer</button>
                </form>
            </div>
        </div>

        <?php if($statsGlobales['total_vendu'] == 0): ?>
            <div style="background: #1a1c24; padding: 30px; border-radius: 8px; border: 1px solid #ff4757; text-align: center; margin-top: 30px;">
                <h2 style="color: #ff4757;">Aucune donnée disponible</h2>
                <p style="color: #b3b3b3;">Aucune vente n'a été réalisée sur la période sélectionnée (<?php echo $titre_periode; ?>).</p>
            </div>
        <?php else: ?>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 30px 0;">
                <div style="background: #1a1c24; padding: 20px; border-radius: 8px; border: 1px solid #2a2c35; text-align: center;">
                    <span style="color: #b3b3b3; text-transform: uppercase; font-size: 14px;">Chiffre d'Affaires (<?php echo $titre_periode; ?>)</span>
                    <h2 style="color: #2ecc71; font-size: 32px; margin: 10px 0 0 0;"><?php echo number_format($statsGlobales['ca_total'], 2); ?> €</h2>
                </div>
                <div style="background: #1a1c24; padding: 20px; border-radius: 8px; border: 1px solid #2a2c35; text-align: center;">
                    <span style="color: #b3b3b3; text-transform: uppercase; font-size: 14px;">Total Clés Vendues</span>
                    <h2 style="color: #3498db; font-size: 32px; margin: 10px 0 0 0;"><?php echo $statsGlobales['total_vendu']; ?></h2>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                <div style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
                    <h3>Top 5 des ventes (Jeux)</h3>
                    <canvas id="chartJeux"></canvas>
                </div>
                <div style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
                    <h3>Ventes par Catégorie</h3>
                    <canvas id="chartCat"></canvas>
                </div>
            </div>

            <script>
                new Chart(document.getElementById('chartJeux'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_column($topJeux, 'titre')); ?>,
                        datasets: [{ label: 'Ventes', data: <?php echo json_encode(array_column($topJeux, 'ventes')); ?>, backgroundColor: '#3498db', borderRadius: 4 }]
                    },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#333' } }, x: { grid: { display: false } } } }
                });

                new Chart(document.getElementById('chartCat'), {
                    type: 'doughnut',
                    data: {
                        labels: <?php echo json_encode(array_column($statsCat, 'nom_cat')); ?>,
                        datasets: [{ data: <?php echo json_encode(array_column($statsCat, 'total')); ?>, backgroundColor: ['#ff4757', '#2ecc71', '#f1c40f', '#3498db', '#9b59b6'], borderWidth: 0 }]
                    },
                    options: { plugins: { legend: { labels: { color: 'white' } } } }
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>