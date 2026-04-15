<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header('Location: index.php'); exit(); }

// 1. Chiffre d'affaires total et Ventes totales
$statsGlobales = $pdo->query("SELECT SUM(ventes) as total_vendu, SUM(ventes * prix) as ca_total FROM jeu")->fetch();

// 2. Top 5 des jeux les plus vendus
$topJeux = $pdo->query("SELECT titre, ventes FROM jeu ORDER BY ventes DESC LIMIT 5")->fetchAll();

// 3. Ventes par catégorie
$statsCat = $pdo->query("
    SELECT c.nom_cat, SUM(j.ventes) as total 
    FROM jeu j 
    JOIN categorie c ON j.id_cat = c.id_cat 
    GROUP BY c.nom_cat
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Monitoring - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">
    <?php include 'navbar.php'; ?>
    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h1 style="border-left: 5px solid #2ecc71; padding-left: 15px;">📈 Monitoring de la Boutique</h1>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0;">
            <div style="background: #1a1c24; padding: 20px; border-radius: 8px; border: 1px solid #2a2c35; text-align: center;">
                <span style="color: #b3b3b3; text-transform: uppercase; font-size: 14px;">Chiffre d'Affaires</span>
                <h2 style="color: #2ecc71; font-size: 32px;"><?php echo number_format($statsGlobales['ca_total'], 2); ?> €</h2>
            </div>
            <div style="background: #1a1c24; padding: 20px; border-radius: 8px; border: 1px solid #2a2c35; text-align: center;">
                <span style="color: #b3b3b3; text-transform: uppercase; font-size: 14px;">Total Clés Vendues</span>
                <h2 style="color: #3498db; font-size: 32px;"><?php echo $statsGlobales['total_vendu']; ?></h2>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div style="background: #1a1c24; padding: 25px; border-radius: 8px;">
                <h3>Top 5 des ventes (Jeux)</h3>
                <canvas id="chartJeux"></canvas>
            </div>
            <div style="background: #1a1c24; padding: 25px; border-radius: 8px;">
                <h3>Ventes par Catégorie</h3>
                <canvas id="chartCat"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Graphique Barres (Jeux)
        new Chart(document.getElementById('chartJeux'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($topJeux, 'titre')); ?>,
                datasets: [{ label: 'Ventes', data: <?php echo json_encode(array_column($topJeux, 'ventes')); ?>, backgroundColor: '#3498db' }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#333' } } } }
        });

        // Graphique Camembert (Catégories)
        new Chart(document.getElementById('chartCat'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($statsCat, 'nom_cat')); ?>,
                datasets: [{ data: <?php echo json_encode(array_column($statsCat, 'total')); ?>, backgroundColor: ['#ff4757', '#2ecc71', '#f1c40f', '#3498db', '#9b59b6'] }]
            }
        });
    </script>
</body>
</html>