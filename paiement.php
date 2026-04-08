<?php
session_start();

// Si le client essaie de venir ici alors que son panier est vide, on le renvoie à l'accueil !
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
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <nav style="padding: 20px; background: #1a1c24; display: flex; justify-content: space-between;">
        <a href="panier.php" style="color: #ff4757; text-decoration: none; font-weight: bold;">← RETOUR AU PANIER</a>
        <span style="font-size: 20px;">Montant à régler : <strong style="color: #2ecc71;"><?php echo number_format($total, 2); ?> €</strong></span>
    </nav>

    <div class="container" style="max-width: 600px; margin: 50px auto; background: #1a1c24; padding: 40px; border-radius: 8px; border: 1px solid #2a2c35;">
        <h2 style="text-align: center; margin-bottom: 30px; font-size: 28px;">Paiement Sécurisé 🔒</h2>
        
        <form action="confirmation.php" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">Nom sur la carte</label>
                <input type="text" placeholder="Ex: Jean Dupont" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">Numéro de carte</label>
                <input type="text" placeholder="**** **** **** ****" maxlength="19" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <div style="flex: 1;">
                    <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">Expiration</label>
                    <input type="text" placeholder="MM/AA" maxlength="5" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
                </div>
                <div style="flex: 1;">
                    <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">CVC</label>
                    <input type="text" placeholder="123" maxlength="3" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
                </div>
            </div>

            <button type="submit" class="btn-hero" style="width: 100%; padding: 15px; border:none; cursor:pointer; font-size: 18px;">
                PAYER <?php echo number_format($total, 2); ?> €
            </button>
        </form>
    </div>
</body>
</html>