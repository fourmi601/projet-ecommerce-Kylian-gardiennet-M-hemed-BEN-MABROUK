<?php 
session_start();
require 'db.php';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

   if ($user && $user['password'] === $password) {
        
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['pseudo'] = $user['pseudo'];
        $_SESSION['role'] = $user['role'];
        
        header('Location: index.php');
        exit();
    } else {
        $erreur = "Adresse e-mail ou mot de passe incorrect.";
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

    <nav>
        <div class="logo-container">
            <a href="index.php"><img src="assets/img/logo.jpg" alt="Logo Digital Games" class="site-logo"></a>
        </div>
        <div class="search-box">
            <input type="text" placeholder="Rechercher...">
            <button>🔍</button>
        </div>
        <div class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="#">Catalogue PC</a>
            <button id="theme-toggle" class="nav-theme-btn">Mode Clair</button>
            <a href="contact.php">Contact</a>
        </div>
       <div class="user-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" style="color: #2ecc71; font-weight: bold;">⚙️ Admin</a>
        <?php endif; ?>

        <a href="#" class="active">👤 Salut <?php echo htmlspecialchars($_SESSION['pseudo']); ?></a>
        <a href="deconnexion.php" style="color: #ff4757;">Déconnexion</a>
        
    <?php else: ?>
        <a href="connexion.php" class="active">👤 Compte</a>
    <?php endif; ?>
    
    <a href="panier.php" class="cart-btn">🛒 Panier</a>
</div>
    </nav>

    <div class="container" style="min-height: 60vh; display: flex; justify-content: center; align-items: center;">
        <div style="background: var(--bg-panel, #1a1c24); padding: 40px; border-radius: 8px; border: 1px solid #2a2c35; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
            <h1 style="color: #fff; text-align: center; margin-bottom: 30px; font-size: 32px;">Connexion</h1>
            
            <?php if (!empty($erreur)): ?>
                <div style="background: #ff475720; border: 1px solid #ff4757; color: #ff4757; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center;">
                    <?php echo $erreur; ?>
                </div>
            <?php endif; ?>

            <form action="connexion.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
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

    <footer class="site-footer">
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Digital Games. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>