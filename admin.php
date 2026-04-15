<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db.php';

// Sécurité
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$message = '';

// --- TRAITEMENT DES FORMULAIRES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // ========================================================
    // 🪄 IMPORTATION MAGIQUE DEPUIS STEAM (CATÉGORIE + DATE)
    // ========================================================
    if (isset($_POST['action']) && $_POST['action'] === 'import_steam') {
        $id_steam_import = (int)$_POST['import_id_steam'];
        
        $url = "https://store.steampowered.com/api/appdetails?appids={$id_steam_import}&l=french";
        $reponse = @file_get_contents($url);
        
        if ($reponse) {
            $data = json_decode($reponse, true);
            if (isset($data[$id_steam_import]['success']) && $data[$id_steam_import]['success']) {
                $jeu_steam = $data[$id_steam_import]['data'];
                
                $titre = $jeu_steam['name'];
                $description = strip_tags($jeu_steam['short_description']); 
                
                // GESTION AUTOMATIQUE DE LA CATÉGORIE
                $id_cat_import = 1;
                if (isset($jeu_steam['genres']) && count($jeu_steam['genres']) > 0) {
                    $nom_genre_steam = $jeu_steam['genres'][0]['description']; 
                    $verifCat = $pdo->prepare("SELECT id_cat FROM categorie WHERE nom_cat = ?");
                    $verifCat->execute([$nom_genre_steam]);
                    $catExistante = $verifCat->fetch();
                    
                    if ($catExistante) {
                        $id_cat_import = $catExistante['id_cat']; 
                    } else {
                        $insertCat = $pdo->prepare("INSERT INTO categorie (nom_cat) VALUES (?)");
                        $insertCat->execute([$nom_genre_steam]);
                        $id_cat_import = $pdo->lastInsertId();
                    }
                }

                // GESTION DU PRIX (0 si pas encore annoncé)
                $prix = isset($jeu_steam['price_overview']) ? ($jeu_steam['price_overview']['initial'] / 100) : 0;
                
                // --- NOUVEAU : GESTION AUTOMATIQUE DE LA DATE DE SORTIE ---
                $date_sortie_import = null;
                if (isset($jeu_steam['release_date']) && !empty($jeu_steam['release_date']['date'])) {
                    $date_brute = $jeu_steam['release_date']['date'];
                    // On traduit les mois français pour que PHP comprenne la date
                    $date_anglaise = str_replace(
                        ['janv.', 'févr.', 'avr.', 'juil.', 'sept.', 'oct.', 'nov.', 'déc.'], 
                        ['jan', 'feb', 'apr', 'jul', 'sep', 'oct', 'nov', 'dec'], 
                        $date_brute
                    );
                    $timestamp = strtotime($date_anglaise);
                    
                    // Si Steam donne une vraie date (ex: "15 nov. 2025"), on l'enregistre
                    if ($timestamp) {
                        $date_sortie_import = date('Y-m-d H:i:s', $timestamp);
                    }
                }
                // -----------------------------------------------------------
                
                // TÉLÉCHARGEMENT DE L'IMAGE
                $image_url = $jeu_steam['header_image'];
                $image_name = 'steam_' . $id_steam_import . '.jpg';
                $image_data = @file_get_contents($image_url);
                if ($image_data) {
                    file_put_contents('assets/img/' . $image_name, $image_data);
                } else {
                    $image_name = 'default.jpg';
                }
                
                // 1. On insère le jeu AVEC LA DATE DE SORTIE
                $stmt = $pdo->prepare("INSERT INTO jeu (titre, description, prix, image, id_cat, id_steam, date_sortie) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titre, $description, $prix, $image_name, $id_cat_import, $id_steam_import, $date_sortie_import]);
                
                $nouveau_jeu_id = $pdo->lastInsertId();

                // 2. On insère les plateformes cochées
                if (!empty($_POST['import_plateformes'])) {
                    $stmtPlat = $pdo->prepare("INSERT INTO jeu_plateforme (id_jeu, id_plateforme) VALUES (?, ?)");
                    foreach ($_POST['import_plateformes'] as $id_plat) {
                        $stmtPlat->execute([$nouveau_jeu_id, $id_plat]);
                    }
                }
                
                $msg_date = $date_sortie_import ? "Date détectée : " . date('d/m/Y', strtotime($date_sortie_import)) : "Aucune date précise.";
                $message = "🪄 MAGIE ! <strong>{$titre}</strong> importé ! ($msg_date)";
            } else {
                $message = "❌ Erreur : Ce jeu n'existe pas ou l'ID est invalide.";
            }
        } else {
            $message = "❌ Erreur de connexion à Steam.";
        }
    }
    // ========================================================

    if (isset($_POST['action']) && $_POST['action'] === 'remove_game_promo') {
        $id_j = $_POST['id_jeu'];
        $pdo->prepare("UPDATE jeu SET prix_solde = 0 WHERE id_jeu = ?")->execute([$id_j]);
        $message = "❌ Promotion retirée du jeu avec succès !";
    }

    if (isset($_POST['action']) && ($_POST['action'] === 'add_jeu' || $_POST['action'] === 'edit_jeu')) {
        $titre = $_POST['titre']; 
        $prix = $_POST['prix']; 
        $prix_solde = !empty($_POST['prix_solde']) ? $_POST['prix_solde'] : 0;
        $description = $_POST['description']; 
        $id_cat = $_POST['id_cat'];
        $image_name = $_POST['old_image'] ?? 'default.jpg'; 
        
        $id_steam = !empty($_POST['id_steam']) ? $_POST['id_steam'] : null;
        $date_sortie = !empty($_POST['date_sortie']) ? $_POST['date_sortie'] : null;

        if (!empty($_FILES['image']['name'])) {
            $image_name = basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], "assets/img/" . $image_name);
        }

        if ($_POST['action'] === 'add_jeu') {
            $stmt = $pdo->prepare("INSERT INTO jeu (titre, description, prix, prix_solde, image, id_cat, id_steam, date_sortie) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $prix, $prix_solde, $image_name, $id_cat, $id_steam, $date_sortie]);
            $id_jeu = $pdo->lastInsertId();
            $message = "✅ Jeu ajouté manuellement !";
        } else {
            $id_jeu = $_POST['id_jeu'];
            $stmt = $pdo->prepare("UPDATE jeu SET titre=?, description=?, prix=?, prix_solde=?, image=?, id_cat=?, id_steam=?, date_sortie=? WHERE id_jeu=?");
            $stmt->execute([$titre, $description, $prix, $prix_solde, $image_name, $id_cat, $id_steam, $date_sortie, $id_jeu]);
            $pdo->prepare("DELETE FROM jeu_plateforme WHERE id_jeu = ?")->execute([$id_jeu]);
            $message = "✅ Jeu modifié !";
        }

        if (!empty($_POST['plateformes'])) {
            $stmtPlat = $pdo->prepare("INSERT INTO jeu_plateforme (id_jeu, id_plateforme) VALUES (?, ?)");
            foreach ($_POST['plateformes'] as $id_plat) {
                $stmtPlat->execute([$id_jeu, $id_plat]);
            }
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'delete_jeu') {
        $pdo->prepare("DELETE FROM jeu WHERE id_jeu = ?")->execute([$_POST['id_jeu']]);
        $message = "🗑️ Jeu supprimé !";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_cat') {
        $pdo->prepare("INSERT INTO categorie (nom_cat) VALUES (?)")->execute([$_POST['nom_cat']]);
        $message = "✅ Catégorie créée avec succès !";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_cat') {
        $id_c = $_POST['id_cat'];
        $check = $pdo->prepare("SELECT COUNT(*) FROM jeu WHERE id_cat = ?");
        $check->execute([$id_c]);
        
        if ($check->fetchColumn() > 0) {
            $message = "❌ Impossible : Des jeux sont actuellement liés à cette catégorie !";
        } else {
            $pdo->prepare("DELETE FROM categorie WHERE id_cat = ?")->execute([$id_c]);
            $message = "🗑️ Catégorie supprimée !";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'cat_promo') {
        $id_c = $_POST['id_cat'];
        $pourcentage = $_POST['pourcentage'] / 100;
        $pdo->prepare("UPDATE jeu SET prix_solde = prix * (1 - ?) WHERE id_cat = ?")->execute([$pourcentage, $id_c]);
        $message = "🏷️ Promotion appliquée à toute la catégorie !";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'game_promo') {
        $id_j = $_POST['id_jeu'];
        $pourcentage = $_POST['pourcentage'] / 100;
        $pdo->prepare("UPDATE jeu SET prix_solde = prix * (1 - ?) WHERE id_jeu = ?")->execute([$pourcentage, $id_j]);
        $message = "🎯 Promotion appliquée sur le jeu !";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_promo') {
        $code = strtoupper($_POST['code']);
        $reduction = $_POST['reduction'];
        $date_exp = !empty($_POST['date_expiration']) ? $_POST['date_expiration'] : null;
        $max_util = !empty($_POST['max_utilisations']) ? $_POST['max_utilisations'] : 100;

        $stmt = $pdo->prepare("INSERT INTO code_promo (code, reduction_pourcentage, date_expiration, max_utilisations) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $reduction, $date_exp, $max_util]);
        $message = "🎟️ Code promo activé !";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_promo') {
        $pdo->prepare("DELETE FROM code_promo WHERE id_promo = ?")->execute([$_POST['id_promo']]);
        $message = "🗑️ Code promo supprimé !";
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        if ($_POST['id_user'] != $_SESSION['user_id']) {
            $pdo->prepare("DELETE FROM utilisateur WHERE id_user = ?")->execute([$_POST['id_user']]);
            $message = "👤 Utilisateur banni/supprimé !";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'change_role') {
        $id_u = $_POST['id_user'];
        $nouveau_role = $_POST['role'];
        if ($id_u != $_SESSION['user_id']) { 
            $pdo->prepare("UPDATE utilisateur SET role = ? WHERE id_user = ?")->execute([$nouveau_role, $id_u]);
            $message = "👤 Rôle mis à jour pour l'utilisateur !";
        }
    }
}

// --- RÉCUPÉRATION DES DONNÉES ---
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom_cat ASC")->fetchAll();
$jeux = $pdo->query("SELECT j.*, c.nom_cat FROM jeu j JOIN categorie c ON j.id_cat = c.id_cat ORDER BY j.id_jeu DESC")->fetchAll();
$utilisateurs = $pdo->query("SELECT * FROM utilisateur")->fetchAll();
$promos = $pdo->query("SELECT * FROM code_promo ORDER BY id_promo DESC")->fetchAll();
$plateformes = $pdo->query("SELECT * FROM plateforme")->fetchAll();

$jeu_a_modifier = null;
$jeu_plateformes = [];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM jeu WHERE id_jeu = ?");
    $stmt->execute([$_GET['edit']]);
    $jeu_a_modifier = $stmt->fetch();

    $stmtPlat = $pdo->prepare("SELECT id_plateforme FROM jeu_plateforme WHERE id_jeu = ?");
    $stmtPlat->execute([$_GET['edit']]);
    $jeu_plateformes = $stmtPlat->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body style="background: #0b0c10; color: white; font-family: 'Rajdhani', sans-serif;">

    <?php include 'navbar.php'; ?>

    <div class="container" style="padding: 40px; max-width: 1400px; margin: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 30px;">
            <h1 style="margin: 0;">⚙️ Administration Générale</h1>
            <a href="sync_steam.php" style="background: #3498db; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: bold; transition: 0.2s;">
                🔄 Synchroniser les avis Steam
            </a>
        </div>
        
        <?php if($message): ?>
            <div style="background: #2ecc7120; border: 1px solid #2ecc71; color: #2ecc71; padding: 15px; border-radius: 4px; margin-bottom: 30px; font-weight: bold; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
            
            <div style="display: flex; flex-direction: column; gap: 30px;">
                
                <?php if(!$jeu_a_modifier): ?>
                <section style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #3498db; box-shadow: 0 0 15px rgba(52, 152, 219, 0.2);">
                    <h2 style="margin-top: 0; color: #3498db;">🪄 Importation Magique via Steam</h2>
                    <p style="color: #b3b3b3; font-size: 14px; margin-bottom: 15px;">Entrez l'ID Steam d'un jeu, choisissez sa catégorie et ses plateformes. Le site s'occupe du reste !</p>
                    
                    <form action="admin.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                        <input type="hidden" name="action" value="import_steam">
                        
                        <div style="display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#b3b3b3; font-size: 14px;">ID Steam du jeu (ex: 271590)</label>
                                <input type="number" name="import_id_steam" required style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px; margin-top: 5px;">
                            </div>
                            
                            
                        </div>

                        <div>
                            <label style="color:#b3b3b3; font-size: 14px;">Plateformes disponibles :</label>
                            <div style="background: #0f1014; border: 1px solid #333; padding: 15px; border-radius: 4px; display: flex; flex-wrap: wrap; gap: 20px; margin-top: 5px;">
                                <?php foreach($plateformes as $p): ?>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 5px; color: white;">
                                        <input type="checkbox" name="import_plateformes[]" value="<?php echo $p['id_plateforme']; ?>"> 
                                        <?php echo $p['nom_plateforme']; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <button type="submit" style="background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; align-self: flex-start;">
                            🚀 IMPORTER LE JEU
                        </button>
                    </form>
                </section>
                <?php endif; ?>

                <section style="background: #1a1c24; padding: 30px; border-radius: 8px; border: 1px solid #2a2c35; height: fit-content;">
                    <h2 style="margin-top: 0; color: #fff;"><?php echo $jeu_a_modifier ? "Modifier l'annonce" : "Ajouter un Jeu Manuellement"; ?></h2>
                    <form action="admin.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
                        <input type="hidden" name="action" value="<?php echo $jeu_a_modifier ? 'edit_jeu' : 'add_jeu'; ?>">
                        <?php if($jeu_a_modifier): ?><input type="hidden" name="id_jeu" value="<?php echo $jeu_a_modifier['id_jeu']; ?>"><input type="hidden" name="old_image" value="<?php echo $jeu_a_modifier['image']; ?>"><?php endif; ?>

                        <div style="display: flex; gap: 15px;">
                            <div style="flex: 2;"><label style="color:#b3b3b3;">Titre</label><input type="text" name="titre" value="<?php echo $jeu_a_modifier['titre'] ?? ''; ?>" required style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;"></div>
                            <div style="flex: 1;"><label style="color:#b3b3b3;">Prix (€)</label><input type="number" step="0.01" name="prix" value="<?php echo $jeu_a_modifier['prix'] ?? ''; ?>" required style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;"></div>
                            <div style="flex: 1;"><label style="color:#ff4757;">Prix Soldé (€)</label><input type="number" step="0.01" name="prix_solde" value="<?php echo ($jeu_a_modifier && $jeu_a_modifier['prix_solde'] > 0) ? $jeu_a_modifier['prix_solde'] : ''; ?>" placeholder="Vide si pas de solde" style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;"></div>
                        </div>
                        
                        <div style="display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#b3b3b3;">Catégorie</label>
                                <select name="id_cat" style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;">
                                    <?php foreach($categories as $cat): ?><option value="<?php echo $cat['id_cat']; ?>" <?php if($jeu_a_modifier && $jeu_a_modifier['id_cat'] == $cat['id_cat']) echo 'selected'; ?>><?php echo $cat['nom_cat']; ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex: 2;"><label style="color:#b3b3b3;">Image de couverture</label><input type="file" name="image" style="width:100%; padding:8px; background:#0f1014; border:1px solid #333; border-radius:4px;"></div>
                        </div>

                        <div>
                            <label style="color:#b3b3b3;">Plateformes disponibles :</label>
                            <div style="background: #0f1014; border: 1px solid #333; padding: 15px; border-radius: 4px; display: flex; flex-wrap: wrap; gap: 20px; margin-top: 5px;">
                                <?php foreach($plateformes as $p): ?>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                        <input type="checkbox" name="plateformes[]" value="<?php echo $p['id_plateforme']; ?>" <?php if(in_array($p['id_plateforme'], $jeu_plateformes)) echo 'checked'; ?>> 
                                        <?php echo $p['nom_plateforme']; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <label style="color:#b3b3b3;">ID Steam</label>
                                <input type="number" name="id_steam" placeholder="Ex: 271590" value="<?php echo $jeu_a_modifier['id_steam'] ?? ''; ?>" style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;">
                            </div>
                            <div style="flex: 1;">
                                <label style="color:#b3b3b3;">Date de Sortie (Optionnel)</label>
                                <?php 
                                    $date_val = '';
                                    if(!empty($jeu_a_modifier['date_sortie'])) {
                                        $date_val = date('Y-m-d\TH:i', strtotime($jeu_a_modifier['date_sortie']));
                                    }
                                ?>
                                <input type="datetime-local" name="date_sortie" value="<?php echo $date_val; ?>" style="width:100%; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;">
                            </div>
                        </div>

                        <div><label style="color:#b3b3b3;">Description</label><textarea name="description" required style="width:100%; height:80px; padding:10px; background:#0f1014; border:1px solid #333; color:white; border-radius:4px;"><?php echo $jeu_a_modifier['description'] ?? ''; ?></textarea></div>
                        
                        <button type="submit" class="btn-hero" style="width:100%; padding:15px; font-size:18px; border:none; border-radius:4px; cursor:pointer; background: #2ecc71; color: white;">VALIDER L'ANNONCE</button>
                        <?php if($jeu_a_modifier) echo "<a href='admin.php' style='text-align:center; color:#ccc; text-decoration:none;'>Annuler la modification</a>"; ?>
                    </form>
                </section>
            </div>

            <div style="display: flex; flex-direction: column; gap: 30px;">
                
                <section style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
                    <h2 style="margin-top: 0; font-size: 18px;">📁 Gestion des Catégories</h2>
                    <form action="admin.php" method="POST" style="margin-bottom: 15px; display: flex; gap: 10px;">
                        <input type="hidden" name="action" value="add_cat">
                        <input type="text" name="nom_cat" placeholder="Nouvelle (ex: RPG)" required style="flex: 1; padding:8px; background:#0f1014; border:1px solid #333; color:white;">
                        <button type="submit" style="background:#2ecc71; color:white; border:none; padding:8px 15px; cursor:pointer; font-weight:bold;">Créer</button>
                    </form>
                    <form action="admin.php" method="POST" style="display: flex; gap: 10px;">
                        <input type="hidden" name="action" value="delete_cat">
                        <select name="id_cat" required style="flex: 1; padding:8px; background:#0f1014; border:1px solid #333; color:white;">
                            <option value="">-- Catégorie à supprimer --</option>
                            <?php foreach($categories as $cat): ?><option value="<?php echo $cat['id_cat']; ?>"><?php echo htmlspecialchars($cat['nom_cat']); ?></option><?php endforeach; ?>
                        </select>
                        <button type="submit" onclick="return confirm('Sûr ?')" style="background:#ff4757; color:white; border:none; padding:8px 15px; cursor:pointer; font-weight:bold;">Effacer</button>
                    </form>
                </section>

                <section style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
                    <h2 style="margin-top: 0; font-size: 18px; color: #3498db;">🏷️ Gestion des Soldes</h2>
                    
                    <form action="admin.php" method="POST" style="margin-bottom: 15px; display: flex; flex-direction: column; gap: 8px;">
                        <input type="hidden" name="action" value="game_promo">
                        <select name="id_jeu" required style="padding:8px; background:#0f1014; border:1px solid #333; color:white;">
                            <option value="">-- Promo sur UN jeu --</option>
                            <?php foreach($jeux as $j): ?><option value="<?php echo $j['id_jeu']; ?>"><?php echo htmlspecialchars($j['titre']); ?></option><?php endforeach; ?>
                        </select>
                        <div style="display: flex; gap: 10px;">
                            <input type="number" name="pourcentage" placeholder="% Réduction" required style="flex: 1; padding:8px; background:#0f1014; border:1px solid #333; color:white;">
                            <button type="submit" style="background:#3498db; color:white; border:none; padding:8px 15px; cursor:pointer; font-weight:bold;">Appliquer</button>
                        </div>
                    </form>

                    <hr style="border-color: #333; margin: 15px 0;">

                    <form action="admin.php" method="POST" style="margin-bottom: 15px; display: flex; flex-direction: column; gap: 8px;">
                        <input type="hidden" name="action" value="cat_promo">
                        <select name="id_cat" required style="padding:8px; background:#0f1014; border:1px solid #333; color:white;">
                            <option value="">-- Promo sur UNE catégorie --</option>
                            <?php foreach($categories as $cat): ?><option value="<?php echo $cat['id_cat']; ?>"><?php echo htmlspecialchars($cat['nom_cat']); ?></option><?php endforeach; ?>
                        </select>
                        <div style="display: flex; gap: 10px;">
                            <input type="number" name="pourcentage" placeholder="% Réduction" required style="flex: 1; padding:8px; background:#0f1014; border:1px solid #333; color:white;">
                            <button type="submit" style="background:#f39c12; color:white; border:none; padding:8px 15px; cursor:pointer; font-weight:bold;">Appliquer</button>
                        </div>
                    </form>

                    <hr style="border-color: #333; margin: 15px 0;">

                    <form action="admin.php" method="POST" style="display: flex; gap: 10px;">
                        <input type="hidden" name="action" value="remove_game_promo">
                        <select name="id_jeu" required style="flex: 1; padding:8px; background:#0f1014; border:1px solid #ff4757; color:white;">
                            <option value="">-- Retirer une promo --</option>
                            <?php foreach($jeux as $j): if($j['prix_solde']>0): ?>
                                <option value="<?php echo $j['id_jeu']; ?>"><?php echo htmlspecialchars($j['titre']); ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                        <button type="submit" style="background:#ff4757; color:white; border:none; padding:8px 15px; cursor:pointer; font-weight:bold;">Retirer</button>
                    </form>
                </section>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
            
            <section style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
                <h2 style="margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px;">👥 Membres Inscrits</h2>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <tr style="border-bottom: 2px solid #333; color:#b3b3b3; text-align:left; font-size:14px;">
                        <th style="padding-bottom:10px;">Pseudo</th><th>Rôle</th><th style="text-align:right;">Actions</th>
                    </tr>
                    <?php foreach($utilisateurs as $u): ?>
                    <tr style="border-bottom: 1px solid #2a2c35;">
                        <td style="padding:15px 0;"><?php echo htmlspecialchars($u['pseudo']); ?></td>
                        <td style="color: <?php echo $u['role'] === 'admin' ? '#2ecc71' : ($u['role'] === 'tiers' ? '#f39c12' : '#ccc'); ?>; font-weight:bold;"><?php echo strtoupper($u['role']); ?></td>
                        <td style="text-align:right;">
                            <?php if($u['id_user'] != $_SESSION['user_id']): ?>
                                <form action="admin.php" method="POST" style="display:inline-flex; gap:5px;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                                    <select name="role" style="padding:4px; font-size:12px; background:#0f1014; color:white; border:1px solid #333;">
                                        <option value="client" <?php if($u['role']=='client') echo 'selected'; ?>>Client</option>
                                        <option value="tiers" <?php if($u['role']=='tiers') echo 'selected'; ?>>Vendeur</option>
                                        <option value="admin" <?php if($u['role']=='admin') echo 'selected'; ?>>Admin</option>
                                    </select>
                                    <button type="submit" style="background:#3498db; color:white; border:none; padding:4px 8px; cursor:pointer; border-radius:2px;">✔</button>
                                </form>
                                <form action="admin.php" method="POST" style="display:inline; margin-left:10px;">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id_user" value="<?php echo $u['id_user']; ?>">
                                    <button type="submit" onclick="return confirm('Bannir ?')" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:16px;">🗑️</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </section>

            <section style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
                <h2 style="margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px;">🎟️ Codes Promos</h2>
                <form action="admin.php" method="POST" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; margin-bottom: 25px;">
                    <input type="hidden" name="action" value="add_promo">
                    <input type="text" name="code" placeholder="CODE (ex: NOEL)" required style="flex:1; padding:10px; background:#0f1014; border:1px solid #333; color:white;">
                    <input type="number" name="reduction" placeholder="% Réd" required style="width:80px; padding:10px; background:#0f1014; border:1px solid #333; color:white;">
                    <input type="date" name="date_expiration" title="Date fin (optionnel)" style="flex:1; padding:10px; background:#0f1014; border:1px solid #333; color:white;">
                    <button type="submit" style="background:#e74c3c; color:white; border:none; padding:10px 15px; font-weight:bold; cursor:pointer; border-radius:4px;">GÉNÉRER</button>
                </form>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 2px solid #333; color:#b3b3b3; text-align:left; font-size:14px;">
                        <th style="padding-bottom:10px;">Code</th><th>Réduction</th><th>Fin</th><th style="text-align:right;">Action</th>
                    </tr>
                    <?php foreach($promos as $pr): ?>
                    <tr style="border-bottom: 1px solid #2a2c35;">
                        <td style="padding:15px 0; font-weight:bold; color:#f39c12; letter-spacing:1px;"><?php echo htmlspecialchars($pr['code']); ?></td>
                        <td>-<?php echo $pr['reduction_pourcentage']; ?>%</td>
                        <td style="color: <?php echo ($pr['date_expiration'] && strtotime($pr['date_expiration']) < time()) ? '#ff4757' : '#b3b3b3'; ?>">
                            <?php echo $pr['date_expiration'] ? date('d/m/Y', strtotime($pr['date_expiration'])) : '∞'; ?>
                        </td>
                        <td style="text-align: right;">
                            <form action="admin.php" method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete_promo">
                                <input type="hidden" name="id_promo" value="<?php echo $pr['id_promo']; ?>">
                                <button type="submit" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:16px;" onclick="return confirm('Supprimer ce code promo ?')">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </section>
            
        </div>

        <section style="background: #1a1c24; padding: 25px; border-radius: 8px; border: 1px solid #2a2c35;">
            <h2 style="margin-top: 0; border-bottom: 1px solid #333; padding-bottom: 10px;">🎮 Inventaire Complet</h2>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <tr style="border-bottom: 2px solid #333; color:#b3b3b3; text-align:left; font-size:14px;">
                    <th style="padding-bottom:10px;">Jeu</th><th>Catégorie</th><th style="text-align:center;">Prix Actuel</th><th style="text-align:right;">Actions</th>
                </tr>
                <?php foreach($jeux as $j): ?>
                <tr style="border-bottom: 1px solid #2a2c35;">
                    <td style="padding:15px 0; font-weight:bold; display: flex; align-items: center; gap: 15px;">
                        <img src="assets/img/<?php echo $j['image']; ?>" style="width: 40px; height: 50px; object-fit: cover; border-radius: 4px;">
                        <?php echo htmlspecialchars($j['titre']); ?>
                    </td>
                    <td style="color:#3498db;"><?php echo htmlspecialchars($j['nom_cat']); ?></td>
                    <td style="text-align:center;">
                        <?php if($j['prix_solde'] > 0): ?>
                            <span style="text-decoration:line-through; color:#ff4757; font-size:12px; margin-right:5px;"><?php echo $j['prix']; ?>€</span>
                            <span style="color:#2ecc71; font-weight:bold; font-size:18px;"><?php echo $j['prix_solde']; ?>€</span>
                        <?php else: ?>
                            <span style="font-weight:bold; font-size:16px;"><?php echo $j['prix']; ?>€</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <a href="admin.php?edit=<?php echo $j['id_jeu']; ?>" style="color: #3498db; text-decoration: none; border:1px solid #3498db; padding:5px 10px; border-radius:4px; margin-right:5px;">Modifier</a>
                        <form action="admin.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_jeu">
                            <input type="hidden" name="id_jeu" value="<?php echo $j['id_jeu']; ?>">
                            <button type="submit" onclick="return confirm('Supprimer définitivement ce jeu ?')" style="background:#ff4757; border:none; color:white; padding:6px 10px; border-radius:4px; cursor:pointer;">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

    </div>
</body>
</html> 