<?php
session_start();
require 'db.php';

$date_actuelle = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("
    SELECT j.*, c.nom_cat 
    FROM jeu j 
    LEFT JOIN categorie c ON j.id_cat = c.id_cat 
    WHERE j.date_sortie > ? 
    ORDER BY j.date_sortie ASC
");
$stmt->execute([$date_actuelle]);
$jeux_futurs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prochaines Sorties - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        .timer-box {
            display: flex; gap: 10px; margin-top: 15px;
        }
        .time-block {
            background: #2a2c35; border-radius: 4px; padding: 5px 10px; text-align: center; min-width: 45px;
        }
        .time-value { font-size: 20px; font-weight: bold; color: #f1c40f; display: block; }
        .time-label { font-size: 10px; color: #b3b3b3; text-transform: uppercase; }
        .preorder-badge {
            background: #f39c12; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 10px; display: inline-block;
        }
    </style>
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <?php include 'navbar.php'; ?>

    <div class="container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h1 style="font-size: 36px; border-left: 5px solid #f39c12; padding-left: 15px; margin-bottom: 30px;">⏳ Prochaines Sorties (Précommandes)</h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;">
            
            <?php if (empty($jeux_futurs)): ?>
                <p style="color: #b3b3b3; font-size: 18px;">Aucune sortie prévue pour le moment. Revenez plus tard !</p>
            <?php else: ?>
                <?php foreach ($jeux_futurs as $jeu): ?>
                    
                    <div style="background: #1a1c24; border: 1px solid #2a2c35; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
                        <img src="assets/img/<?php echo htmlspecialchars($jeu['image']); ?>" alt="Jeu" style="width: 100%; height: 200px; object-fit: cover;">
                        
                        <div style="padding: 20px; flex-grow: 1; display: flex; flex-direction: column;">
                            <span class="preorder-badge">PRÉCOMMANDE</span>
                            <h3 style="margin: 0 0 10px 0; font-size: 22px;"><?php echo htmlspecialchars($jeu['titre']); ?></h3>
                            <span style="color: #3498db; font-size: 14px; margin-bottom: 15px;"><?php echo htmlspecialchars($jeu['nom_cat'] ?? 'PC'); ?></span>
                            
                            <div class="timer-box" data-date="<?php echo $jeu['date_sortie']; ?>">
                                <div class="time-block"><span class="time-value jours">00</span><span class="time-label">Jours</span></div>
                                <div class="time-block"><span class="time-value heures">00</span><span class="time-label">Hrs</span></div>
                                <div class="time-block"><span class="time-value minutes">00</span><span class="time-label">Min</span></div>
                                <div class="time-block"><span class="time-value secondes">00</span><span class="time-label">Sec</span></div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; border-top: 1px solid #2a2c35; padding-top: 15px;">
                                <span style="font-size: 24px; font-weight: bold; color: #2ecc71;"><?php echo number_format($jeu['prix'], 2); ?> €</span>
                                <a href="ajouter_panier.php?id_jeu=<?php echo $jeu['id_jeu']; ?>" style="background: #f39c12; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; font-weight: bold; font-size: 14px;">PRÉCOMMANDER</a>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

    <script>
        function updateTimers() {
            const timerBoxes = document.querySelectorAll('.timer-box');
            const now = new Date().getTime();

            timerBoxes.forEach(box => {
                const releaseDate = new Date(box.getAttribute('data-date')).getTime();
                const distance = releaseDate - now;

                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    box.querySelector('.jours').innerText = days.toString().padStart(2, '0');
                    box.querySelector('.heures').innerText = hours.toString().padStart(2, '0');
                    box.querySelector('.minutes').innerText = minutes.toString().padStart(2, '0');
                    box.querySelector('.secondes').innerText = seconds.toString().padStart(2, '0');
                } else {
                    box.innerHTML = '<span style="color: #2ecc71; font-weight: bold; font-size: 16px;">DISPONIBLE MAINTENANT !</span>';
                }
            });
        }

        setInterval(updateTimers, 1000);
        updateTimers();
    </script>
</body>
</html>