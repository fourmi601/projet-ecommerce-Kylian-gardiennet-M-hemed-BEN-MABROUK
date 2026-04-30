<?php
// auth : admin → BDD locale / client → BDD Ecotech
require_once 'security.php';
session_start();
require 'db.php';
require_once 'marchands-config.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!csrf_verify()) {
        $erreur = "Requête invalide. Veuillez réessayer.";
    }
    // Protection brute force : 5 tentatives max en 15 min
    elseif (brute_force_check()) {
        $erreur = "Trop de tentatives. Réessayez dans 15 minutes.";
    }
    else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt_local = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt_local->execute([$email]);
        $user_local = $stmt_local->fetch();

        if ($user_local && $user_local['role'] === 'admin') {
            if (password_verify($password, $user_local['password'])) {
                // Regénère l'ID de session après connexion (anti session fixation)
                session_regenerate_id(true);
                brute_force_reset();
                $_SESSION['user_id']       = $user_local['id_user'];
                $_SESSION['pseudo']        = $user_local['pseudo'];
                $_SESSION['role']          = $user_local['role'];
                $_SESSION['email']         = $user_local['email'];
                $_SESSION['ecotech_token'] = 'ADMIN_LOCAL';
                header('Location: admin.php');
                exit();
            } else {
                brute_force_increment();
                $erreur = "Mot de passe incorrect.";
            }
        } else {
            try {
                $pdo_bank = new PDO("mysql:host=100.65.154.19;dbname=ecotech_db;charset=utf8mb4", "dev_remote", "ezechiel", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                $stmt_bank = $pdo_bank->prepare("SELECT * FROM users WHERE email = ?");
                $stmt_bank->execute([$email]);
                $user_bank = $stmt_bank->fetch();

                if ($user_bank && password_verify($password, $user_bank['password'])) {
                    if ($user_local) {
                        // Regénère l'ID de session (anti session fixation)
                        session_regenerate_id(true);
                        brute_force_reset();
                        $_SESSION['user_id']       = $user_local['id_user'];
                        $_SESSION['pseudo']        = $user_local['pseudo'];
                        $_SESSION['role']          = $user_local['role'];
                        $_SESSION['email']         = $user_local['email'];
                        $_SESSION['ecotech_token'] = $user_bank['token'] ?? '';
                        header('Location: index.php');
                        exit();
                    } else {
                        $erreur = "Compte bancaire valide, mais vous n'êtes pas inscrit sur Digital Games.";
                    }
                } else {
                    brute_force_increment();
                    $erreur = "Adresse e-mail ou mot de passe incorrect.";
                }
            } catch (PDOException $e) {
                $erreur = "Le serveur bancaire est temporairement indisponible. Réessayez plus tard.";
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
    <title>Connexion - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container" style="min-height: 60vh; display: flex; justify-content: center; align-items: center;">
        <div style="background: var(--bg-panel, #1a1c24); padding: 40px; border-radius: 8px; border: 1px solid #2a2c35; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
            <h1 style="color: #fff; text-align: center; margin-bottom: 30px; font-size: 32px;">Connexion</h1>
            
            <?php if (!empty($erreur)): ?>
                <div style="background: #ff475720; border: 1px solid #ff4757; color: #ff4757; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endif; ?>

            <form action="connexion.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                <?php echo csrf_field(); ?>
                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Adresse E-mail</label>
                    <input type="email" name="email" placeholder="pseudo@digitalgames.fr" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; box-sizing: border-box;">
                </div>
                <button type="submit" class="btn-hero" style="width: 100%; padding: 15px; border: none; cursor: pointer; font-size: 18px; margin-top: 10px;">SE CONNECTER</button>
            </form>

            <p style="text-align: center; margin-top: 25px; color: #b3b3b3;">
                Pas encore de compte ? <a href="inscription.php" style="color: #ff4757; text-decoration: none; font-weight: bold;">S'inscrire ici</a>
            </p>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>