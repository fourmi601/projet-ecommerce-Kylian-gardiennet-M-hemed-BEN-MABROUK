<?php
// inscription : 1) API Ecotech → 2) insert BDD locale
session_start();
require 'db.php';
require_once 'marchands-config.php';

$erreur = '';
$succes = '';
$redirect_connexion = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo   = trim($_POST['pseudo'] ?? '');
    $email    = trim($_POST['email']  ?? '');
    $password = $_POST['password']    ?? '';

    if (empty($pseudo) || empty($email) || empty($password)) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Adresse e-mail invalide.";
    } elseif (strlen($password) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $erreur = "Cette adresse e-mail est déjà utilisée.";
        } else {
            $data = json_encode(['username' => $pseudo, 'email' => $email, 'password' => $password]);
            $ch   = curl_init(ECOTECH_API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
                'X-API-KEY: ' . ECOTECH_API_KEY
            ]);
            $response  = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $erreur = "Le service d'inscription est temporairement indisponible.";
            } else {
                $resultat = json_decode($response, true);
                if (isset($resultat['status']) && $resultat['status'] === 'success') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO utilisateur (pseudo, email, password, role) VALUES (?, ?, ?, 'client')");
                    if ($stmt->execute([$pseudo, $email, $hash])) {
                        $succes = "Compte créé avec succès ! Redirection en cours…";
                        $redirect_connexion = true;
                    } else {
                        $erreur = "Erreur lors de l'enregistrement du compte.";
                    }
                } else {
                    $erreur = "L'inscription a été refusée. Vérifiez vos informations.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container" style="min-height: 60vh; display: flex; justify-content: center; align-items: center; margin-top: 40px; margin-bottom: 40px;">
        
        <div style="background: var(--bg-panel, #1a1c24); padding: 40px; border-radius: 8px; border: 1px solid #2a2c35; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
            <h1 style="color: #fff; text-align: center; margin-bottom: 30px; font-size: 32px;">Créer un compte</h1>
            
            <?php if ($erreur): ?>
                <div style="background: rgba(255, 71, 87, 0.1); border: 1px solid #ff4757; color: #ff4757; padding: 12px; border-radius: 4px; text-align: center; margin-bottom: 20px; font-size: 14px;">
                 <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <?php if ($succes): ?>
                <div style="background: rgba(46, 204, 113, 0.1); border: 1px solid #2ecc71; color: #2ecc71; padding: 12px; border-radius: 4px; text-align: center; margin-bottom: 20px; font-size: 14px;">
                    <?php echo htmlspecialchars($succes); ?>
                </div>
            <?php endif; ?>

            <form action="inscription.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                
                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Pseudo</label>
                    <input type="text" name="pseudo" placeholder="Ex: Kouakou" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Adresse E-mail</label>
                    <input type="email" name="email" placeholder="contact@email.com" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; box-sizing: border-box;">
                </div>

                <button type="submit" class="btn-hero" style="width: 100%; padding: 15px; border: none; cursor: pointer; font-size: 18px; margin-top: 10px;">S'INSCRIRE</button>
            </form>
            <?php if ($redirect_connexion): ?>
            <script>setTimeout(function(){ window.location.href = 'connexion.php'; }, 2500);</script>
            <?php endif; ?>

            <p style="text-align: center; margin-top: 25px; color: #b3b3b3;">
                Déjà un compte ? <a href="connexion.php" style="color: #ff4757; text-decoration: none; font-weight: bold;">Se connecter</a>
            </p>
        </div>

    </div>

    <?php include 'footer.php'; ?>
</body>
</html>