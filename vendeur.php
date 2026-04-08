<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';

// SÉCURITÉ : Uniquement pour le rôle "tiers" (ou admin s'il veut tester)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'tiers' && $_SESSION['role'] !== 'admin')) {
    header('Location: index.php');
    exit();
}

$id_vendeur = $_SESSION['user_id'];
$message = '';

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // AJOUTER / MODIFIER UN JEU
    if (isset($_POST['action']) && ($_POST['action'] === 'add_jeu' || $_POST['action'] === 'edit_jeu')) {
        $titre = $_POST['titre']; $prix = $_POST['prix']; $description = $_POST['description']; $id_cat = $_POST['id_cat'];
        $image_name = $_POST['old_image'] ?? 'default.jpg'; 
        
        if (!empty($_FILES['image']['name'])) {
            $image_name = basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], "assets/img/" . $image_name);
        }

        if ($_POST['action'] === 'add_jeu') {
            // L'astuce est ici : on force l'enregistrement de l'id_vendeur !
            $stmt = $pdo->prepare("INSERT INTO jeu (titre, description, prix, image, id_cat, id_vendeur) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $prix, $image_name, $id_cat, $id_vendeur]);
            $message = "✅ Votre jeu a été mis en vente !";
        } else {
            // Modif sécurisée : on vérifie que le jeu lui appartient bien !
            $id_jeu = $_POST['id_jeu'];
            $stmt = $pdo->prepare("UPDATE jeu SET titre=?, description=?, prix=?, image=?, id_cat=? WHERE id_jeu=? AND id_vendeur=?");
            $stmt->execute([$titre, $description, $prix, $image_name, $id_cat, $id_jeu, $id_vendeur]);
            $message = "✅ Jeu modifié !";
        }
    }
    
    // SUPPRIMER UN JEU
    if (isset($_POST['action']) && $_POST['action'] === 'delete_jeu') {
        // Sécurité : on supprime uniquement si c'est SON jeu
        $pdo->prepare("DELETE FROM jeu WHERE id_jeu = ? AND id_vendeur = ?")->execute([$_POST['id_jeu'], $id_vendeur]);
        $message = "🗑️ Jeu retiré de la vente !";
    }
}

// --- RÉCUPÉRATION DES DONNÉES ---
$categories = $pdo->query("SELECT * FROM categorie")->fetchAll();

// On ne récupère QUE les jeux de CE vendeur !
$stmtMesJeux = $pdo->prepare("SELECT j.*, c.nom_cat FROM jeu j JOIN categorie c ON j.id_cat = c.id_cat WHERE j.id_vendeur = ?");
$stmtMesJeux->execute([$id_vendeur]);
$mes_jeux = $stmtMesJeux->fetchAll();

$jeu_a_modifier = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM jeu WHERE id_jeu = ? AND id_vendeur = ?");
    $stmt->execute([$_GET['edit'], $id_vendeur]);
    $jeu_a_modifier = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Vendeur - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <nav style="padding: 20px; background: #1a1c24; display: flex; justify-content: space-between;">
        <a href="index.php" style="color: #3498db; text-decoration: none; font-weight: bold;">← RETOUR AU SITE</a>
        <span>Espace Vendeur de : <strong><?php echo htmlspecialchars($_SESSION['pseudo']); ?></strong></span>
    </nav>

    <div class="container" style="padding: 40px; max-width: 1200px; margin: auto;">
        <h1 style="border-bottom: 2px solid #f39c12; padding-bottom: 10px; margin-bottom: 30px;">🏪 Mon Tableau de Bord Vendeur</h1>
        
        <?php if($message) echo "<p style='color: #2ecc71; font-size:18px; font-weight: bold;'>$message</p>"; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
            
            <section style="background: #1a1c24; padding: 20px; border-radius: 8px; border: 1px solid #2a2c35; height: fit-content;">
                <h2 style="margin-top: 0;"><?php echo $jeu_a_modifier ? "Modifier l'annonce" : "Mettre un jeu en vente"; ?></h2>
                <form action="vendeur.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                    <input type="hidden" name="action" value="<?php echo $jeu_a_modifier ? 'edit_jeu' : 'add_jeu'; ?>">
                    <?php if($jeu_a_modifier): ?>
                        <input type="hidden" name="id_jeu" value="<?php echo $jeu_a_modifier['id_jeu']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo $jeu_a_modifier['image']; ?>">
                    <?php endif; ?>

                    <div><label style="color: #b3b3b3;">Titre du jeu</label><input type="text" name="titre" value="<?php echo $jeu_a_modifier['titre'] ?? ''; ?>" required style="width:100%; padding:8px; background: #0f1014; border: 1px solid #333; color: white;"></div>
                    <div><label style="color: #b3b3b3;">Prix de vente (€)</label><input type="number" step="0.01" name="prix" value="<?php echo $jeu_a_modifier['prix'] ?? ''; ?>" required style="width:100%; padding:8px; background: #0f1014; border: 1px solid #333; color: white;"></div>
                    <div>
                        <label style="color: #b3b3b3;">Catégorie</label>
                        <select name="id_cat" style="width:100%; padding:8px; background: #0f1014; border: 1px solid #333; color: white;">
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id_cat']; ?>" <?php if($jeu_a_modifier && $jeu_a_modifier['id_cat'] == $cat['id_cat']) echo 'selected'; ?>><?php echo $cat['nom_cat']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div><label style="color: #b3b3b3;">Image de couverture</label><input type="file" name="image" style="width:100%;"></div>
                    <div><label style="color: #b3b3b3;">Description</label><textarea name="description" required style="width:100%; height:80px; padding:8px; background: #0f1014; border: 1px solid #333; color: white;"><?php echo $jeu_a_modifier['description'] ?? ''; ?></textarea></div>
                    
                    <button type="submit" style="background: #f39c12; color: white; border: none; padding: 12px; font-weight: bold; cursor: pointer; border-radius: 4px;">VALIDER L'ANNONCE</button>
                    <?php if($jeu_a_modifier) echo "<a href='vendeur.php' style='text-align:center; color:#ccc; text-decoration: none;'>Annuler la modification</a>"; ?>
                </form>
            </section>

            <section style="background: #1a1c24; padding: 20px; border-radius: 8px; border: 1px solid #2a2c35;">
                <h2 style="margin-top: 0;">📦 Mes jeux actuellement en vente</h2>
                
                <?php if(empty($mes_jeux)): ?>
                    <p style="color: #b3b3b3; text-align: center; margin-top: 50px;">Vous n'avez encore mis aucun jeu en vente.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid #333;"><th style="text-align:left; padding-bottom: 10px;">Jeu</th><th>Prix</th><th>Actions</th></tr>
                        <?php foreach($mes_jeux as $j): ?>
                        <tr style="border-bottom: 1px solid #2a2c35;">
                            <td style="padding:10px 0; display: flex; align-items: center; gap: 15px;">
                                <img src="assets/img/<?php echo $j['image']; ?>" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php echo htmlspecialchars($j['titre']); ?>
                            </td>
                            <td style="text-align:center; color: #2ecc71; font-weight: bold;"><?php echo $j['prix']; ?>€</td>
                            <td style="text-align:right;">
                                <a href="vendeur.php?edit=<?php echo $j['id_jeu']; ?>" style="color: #3498db; text-decoration: none;">Modifier</a> | 
                                <form action="vendeur.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_jeu">
                                    <input type="hidden" name="id_jeu" value="<?php echo $j['id_jeu']; ?>">
                                    <button type="submit" onclick="return confirm('Retirer ce jeu de la vente ?')" style="background:none; border:none; color:#ff4757; cursor:pointer;">Retirer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>