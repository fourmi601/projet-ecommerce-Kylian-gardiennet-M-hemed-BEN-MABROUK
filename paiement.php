<?php
session_start();

if (empty($_SESSION['panier']) || !isset($_SESSION['total_a_payer'])) {
    header('Location: index.php');
    exit();
}

$total = $_SESSION['total_a_payer'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container" style="max-width: 600px; margin: 50px auto; background: #1a1c24; padding: 40px; border-radius: 8px; border: 1px solid #2a2c35; text-align: center;">
        <h2 style="margin-bottom: 20px; font-size: 28px;">Paiement Sécurisé 🔒</h2>
        <p style="color: #b3b3b3; margin-bottom: 30px;">Vous allez être redirigé vers l'interface sécurisée d'Ecotech Bank pour procéder au règlement de votre commande.</p>
        
       <form method="POST" action="process_paiement.php">
            <button type="submit" style="display: block; width: 100%; background: #3498db; color: white; border: none; padding: 15px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 18px; transition: 0.3s;">
                PAYER AVEC ECOTECH BANK (<?php echo number_format($total, 2); ?> €)
            </button>
        </form>
    </div>
</body>
</html>