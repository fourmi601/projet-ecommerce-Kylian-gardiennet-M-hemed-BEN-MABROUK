<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 50px auto; background: #1a1c24; padding: 40px; border-radius: 8px; border: 1px solid #00439C;">
        <h2 style="text-align: center; margin-bottom: 30px;">Paiement Sécurisé</h2>
        
        <form action="confirmation.php" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">Nom sur la carte</label>
                <input type="text" placeholder="Kylian Mbappé" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">Numéro de carte</label>
                <input type="text" placeholder="**** **** **** ****" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 30px;">
                <div style="flex: 1;">
                    <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">Expiration</label>
                    <input type="text" placeholder="MM/AA" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
                </div>
                <div style="flex: 1;">
                    <label style="color: #b3b3b3; display: block; margin-bottom: 8px;">CVC</label>
                    <input type="text" placeholder="123" style="width: 100%; padding: 12px; border-radius: 5px; background: #0f1014; border: 1px solid #333; color: white; box-sizing: border-box;" required>
                </div>
            </div>

            <button type="submit" class="btn-hero" style="width: 100%; text-align: center; border:none; cursor:pointer;">PAYER 76.98 €</button>
        </form>
    </div>
</body>
</html>