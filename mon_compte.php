<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$id_user = $_SESSION['user_id'];
$message = '';
$type_message = '';

$stmt = $pdo->prepare("SELECT email, password, role FROM utilisateur WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_email'])) {
        $nouvel_email = $_POST['email'];
        $check = $pdo->prepare("SELECT id_user FROM utilisateur WHERE email = ? AND id_user != ?");
        $check->execute([$nouvel_email, $id_user]);
        
        if ($check->fetch()) {
            $message = "❌ Cet e-mail est déjà utilisé par un autre compte.";
            $type_message = "#ff4757";
        } else {
            $pdo->prepare("UPDATE utilisateur SET email = ? WHERE id_user = ?")->execute([$nouvel_email, $id_user]);
            $user['email'] = $nouvel_email;
            $message = "✅ Adresse e-mail mise à jour avec succès !";
            $type_message = "#2ecc71";
        }
    }

    if (isset($_POST['update_password'])) {
        $ancien_mdp = $_POST['ancien_mdp'];
        $nouveau_mdp = $_POST['nouveau_mdp'];

        if (!password_verify($ancien_mdp, $user['password'])) {
            $message = "❌ L'ancien mot de passe est incorrect.";
            $type_message = "#ff4757";
        } elseif (strlen($nouveau_mdp) < 6) {
            $message = "❌ Le nouveau mot de passe doit contenir au moins 6 caractères.";
            $type_message = "#ff4757";
        } else {
            $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE utilisateur SET password = ? WHERE id_user = ?")->execute([$hash, $id_user]);
            $user['password'] = $hash;
            $message = "✅ Mot de passe modifié avec succès !";
            $type_message = "#2ecc71";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Compte - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container" style="padding: 40px; max-width: 1000px; margin: auto;">
        <h1 style="border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 30px;">⚙️ Paramètres du compte</h1>

        <?php if($message): ?>
            <div style="background: <?php echo $type_message; ?>20; border: 1px solid <?php echo $type_message; ?>; color: <?php echo $type_message; ?>; padding: 15px; border-radius: 4px; margin-bottom: 30px; font-weight: bold; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            
            <div style="display: flex; flex-direction: column; gap: 30px;">
                
                <section style="background: #1a1c24; border: 1px solid #2a2c35; padding: 25px; border-radius: 8px;">
                    <h2 style="margin-top: 0; margin-bottom: 20px;">✉️ Modifier mon E-mail</h2>
                    <form action="mon_compte.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="color: #b3b3b3; font-size: 14px;">Adresse E-mail actuelle</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width: 100%; padding: 10px; background: #0f1014; border: 1px solid #333; color: white; margin-top: 5px;">
                        </div>
                        <button type="submit" name="update_email" class="btn-hero" style="border: none; padding: 10px; cursor: pointer; border-radius: 4px;">METTRE À JOUR L'E-MAIL</button>
                    </form>
                </section>

                <section style="background: #1a1c24; border: 1px solid #2a2c35; padding: 25px; border-radius: 8px;">
                    <h2 style="margin-top: 0; margin-bottom: 20px;">🔒 Modifier mon Mot de passe</h2>
                    <form action="mon_compte.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="color: #b3b3b3; font-size: 14px;">Ancien mot de passe</label>
                            <input type="password" name="ancien_mdp" required style="width: 100%; padding: 10px; background: #0f1014; border: 1px solid #333; color: white; margin-top: 5px;">
                        </div>
                        <div>
                            <label style="color: #b3b3b3; font-size: 14px;">Nouveau mot de passe</label>
                            <input type="password" name="nouveau_mdp" required style="width: 100%; padding: 10px; background: #0f1014; border: 1px solid #333; color: white; margin-top: 5px;">
                        </div>
                        <button type="submit" name="update_password" class="btn-hero" style="border: none; padding: 10px; cursor: pointer; background: #ff4757; border-radius: 4px;">CHANGER LE MOT DE PASSE</button>
                    </form>
                </section>

            </div>

            <div style="display: flex; flex-direction: column; gap: 20px;">
                
                <a href="bibliotheque.php" style="background: linear-gradient(135deg, #1a1c24, #2ecc7130); border: 1px solid #2ecc71; padding: 30px; border-radius: 8px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <span style="font-size: 40px; margin-bottom: 10px;">📚</span>
                    <h2 style="margin: 0;">Ma Bibliothèque</h2>
                    <p style="color: #b3b3b3; margin-top: 5px;">Accédez à vos clés CD et activez vos jeux.</p>
                </a>

                <a href="mes_commandes.php" style="background: linear-gradient(135deg, #1a1c24, #3498db30); border: 1px solid #3498db; padding: 30px; border-radius: 8px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <span style="font-size: 40px; margin-bottom: 10px;">📦</span>
                    <h2 style="margin: 0;">Historique des Commandes</h2>
                    <p style="color: #b3b3b3; margin-top: 5px;">Consultez vos factures et anciens achats.</p>
                </a>
<?php if ($_SESSION['role'] === 'tiers' || $_SESSION['role'] === 'admin'): ?>
                <a href="admin_stats.php" style="background: linear-gradient(135deg, #1a1c24, #9b59b630); border: 1px solid #9b59b6; padding: 30px; border-radius: 8px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <span style="font-size: 40px; margin-bottom: 10px;">📊</span>
                    <h2 style="margin: 0;">Monitoring & Stats</h2>
                    <p style="color: #b3b3b3; margin-top: 5px;">Voir les ventes réelles et le CA.</p>
                </a>
                <?php endif; ?>
<?php if ($_SESSION['role'] === 'tiers'): ?>
                <a href="vendeur.php" style="background: linear-gradient(135deg, #1a1c24, #f39c1230); border: 1px solid #f39c12; padding: 30px; border-radius: 8px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <span style="font-size: 40px; margin-bottom: 10px;">🏪</span>
                    <h2 style="margin: 0;">Espace Vendeur</h2>
                    <p style="color: #b3b3b3; margin-top: 5px;">Gérer mes annonces et mes jeux.</p>
                </a>
                
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" style="background: linear-gradient(135deg, #1a1c24, #f39c1230); border: 1px solid #f39c12; padding: 30px; border-radius: 8px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: 0.3s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                    <span style="font-size: 40px; margin-bottom: 10px;">⚙️</span>
                    <h2 style="margin: 0;">Panel Administrateur</h2>
                    <p style="color: #b3b3b3; margin-top: 5px;">Gérer le catalogue et les membres.</p>
                </a>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>