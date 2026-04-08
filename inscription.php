<<?php 
session_start();
require 'db.php'; // On branche le câble à la base de données

$erreur = '';

// Si le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = $_POST['pseudo'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. On vérifie si l'email n'est pas déjà pris
    $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->fetch()) {
        $erreur = "Cet email est déjà utilisé !";
    } else {
        // 2. L'email est libre, on insère le nouveau membre !
        $stmt = $pdo->prepare("INSERT INTO utilisateur (pseudo, email, password, role) VALUES (?, ?, ?, 'client')");
        
        if ($stmt->execute([$pseudo, $email, $password])) {
            // Succès ! On redirige vers la page de connexion
            header('Location: connexion.php');
            exit();
        } else {
            $erreur = "Une erreur est survenue lors de l'inscription.";
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

    <div class="container" style="min-height: 60vh; display: flex; justify-content: center; align-items: center; margin-top: 40px; margin-bottom: 40px;">
        
        <div style="background: var(--bg-panel, #1a1c24); padding: 40px; border-radius: 8px; border: 1px solid #2a2c35; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
            <h1 style="color: #fff; text-align: center; margin-bottom: 30px; font-size: 32px;">Créer un compte</h1>
            
            <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                
                <div>
                    <label style="color: #b3b3b3; font-size: 14px; margin-bottom: 5px; display: block;">Pseudo</label>
                    <input type="text" name="pseudo" placeholder="Kouakou" required style="width: 100%; padding: 12px; border-radius: 4px; border: 1px solid #333; background: #2a2c35; color: white; font-family: 'Rajdhani', sans-serif; font-size: 16px; box-sizing: border-box;">
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

            <p style="text-align: center; margin-top: 25px; color: #b3b3b3;">
                Déjà un compte ? <a href="connexion.php" style="color: #ff4757; text-decoration: none; font-weight: bold;">Se connecter</a>
            </p>
        </div>

    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-col">
                <img src="assets/img/logo.jpg" alt="Logo Digital Games" class="footer-logo">
                <p>Votre boutique N°1 de clés CD officielles.</p>
            </div>
            <div class="footer-col">
                <h3>Liens Rapides</h3>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="#">Catalogue PC</a></li>
                    <li><a href="panier.php">Mon Panier</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Informations</h3>
                <ul>
                    <li><a href="mentions-legales.php">Mentions Légales</a></li>
                    <li><a href="cgv.php">Conditions Générales de Vente</a></li>
                    <li><a href="contact.php">Contactez-nous</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Digital Games. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>