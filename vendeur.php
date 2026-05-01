<?php
// espace tiers //
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'tiers' && $_SESSION['role'] !== 'admin')) {
    header('Location: index.php');
    exit();
}

$id_vendeur = $_SESSION['user_id'];
$message = '';

//  TRAITEMENT FORMULAIRE //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Import automatique depuis l'API Steam
    if (isset($_POST['action']) && $_POST['action'] === 'import_steam') {
        $id_steam_import = (int)$_POST['import_id_steam'];
        $url      = "https://store.steampowered.com/api/appdetails?appids={$id_steam_import}&l=french";
        $reponse  = @file_get_contents($url);

        if ($reponse) {
            $data = json_decode($reponse, true);
            if (isset($data[$id_steam_import]['success']) && $data[$id_steam_import]['success']) {
                $jeu_steam   = $data[$id_steam_import]['data'];
                $titre       = $jeu_steam['name'];
                $description = strip_tags($jeu_steam['short_description']);

                // Catégorie : on cherche si elle existe déjà, sinon on la crée //
                $id_cat_import = 1;
                if (!empty($jeu_steam['genres'])) {
                    $nom_genre = $jeu_steam['genres'][0]['description'];
                    $verifCat  = $pdo->prepare("SELECT id_cat FROM categorie WHERE nom_cat = ?");
                    $verifCat->execute([$nom_genre]);
                    $catExistante = $verifCat->fetch();
                    if ($catExistante) {
                        $id_cat_import = $catExistante['id_cat'];
                    } else {
                        $pdo->prepare("INSERT INTO categorie (nom_cat) VALUES (?)")->execute([$nom_genre]);
                        $id_cat_import = $pdo->lastInsertId();
                    }
                }

                // Prix  //
                $prix = isset($jeu_steam['price_overview'])
                    ? ($jeu_steam['price_overview']['initial'] / 100) : 0;

                // Date de sortie
                $date_sortie_import = null;
                if (!empty($jeu_steam['release_date']['date'])) {
                    $date_anglaise = str_replace(
                        ['janv.', 'févr.', 'avr.', 'juil.', 'sept.', 'oct.', 'nov.', 'déc.'],
                        ['jan',   'feb',   'apr',  'jul',   'sep',   'oct',  'nov',  'dec'],
                        $jeu_steam['release_date']['date']
                    );
                    $ts = strtotime($date_anglaise);
                    if ($ts) $date_sortie_import = date('Y-m-d H:i:s', $ts);
                }

                // Image de couverture //
                $image_name = 'steam_' . $id_steam_import . '.jpg';
                $image_data = @file_get_contents($jeu_steam['header_image']);
                if ($image_data) {
                    file_put_contents('assets/img/' . $image_name, $image_data);
                } else {
                    $image_name = 'default.jpg';
                }

                $stmt = $pdo->prepare("INSERT INTO jeu (titre, description, prix, image, id_cat, id_steam, date_sortie, id_vendeur) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titre, $description, $prix, $image_name, $id_cat_import, $id_steam_import, $date_sortie_import, $id_vendeur]);

                $msg_date = $date_sortie_import ? date('d/m/Y', strtotime($date_sortie_import)) : 'date inconnue';
                $message  = ['type' => 'ok', 'text' => "✅ <strong>{$titre}</strong> importé depuis Steam ! (date : {$msg_date})"];
            } else {
                $message = ['type' => 'err', 'text' => "❌ Jeu introuvable sur Steam. Vérifiez l'ID."];
            }
        } else {
            $message = ['type' => 'err', 'text' => "❌ Impossible de contacter Steam. Vérifiez votre connexion."];
        }
    }

    if (isset($_POST['action']) && in_array($_POST['action'], ['add_jeu', 'edit_jeu'])) {
        $titre       = trim($_POST['titre']);
        $prix        = (float)$_POST['prix'];
        $prix_solde  = isset($_POST['prix_solde']) && $_POST['prix_solde'] !== '' ? (float)$_POST['prix_solde'] : 0;
        $description = trim($_POST['description']);
        $id_cat      = (int)$_POST['id_cat'];
        $date_sortie = !empty($_POST['date_sortie']) ? $_POST['date_sortie'] : null;
        $image_name  = $_POST['old_image'] ?? 'default.jpg';

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext_autorisees = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                               'png' => 'image/png', 'webp' => 'image/webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $mime_reel = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['image']['tmp_name']);
            if (isset($ext_autorisees[$ext])
                && $ext_autorisees[$ext] === $mime_reel
                && $_FILES['image']['size'] <= 5 * 1024 * 1024) {
                $image_name = uniqid('jeu_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], 'assets/img/' . $image_name);
            }
        }

        if ($_POST['action'] === 'add_jeu') {
            $stmt = $pdo->prepare("INSERT INTO jeu (titre, description, prix, prix_solde, image, id_cat, id_vendeur, date_sortie) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $prix, $prix_solde, $image_name, $id_cat, $id_vendeur, $date_sortie]);
            $message = ['type' => 'ok', 'text' => '✅ Votre jeu a été mis en vente !'];
        } else {
            $id_jeu = (int)$_POST['id_jeu'];
            $stmt = $pdo->prepare("UPDATE jeu SET titre=?, description=?, prix=?, prix_solde=?, image=?, id_cat=?, date_sortie=? WHERE id_jeu=? AND id_vendeur=?");
            $stmt->execute([$titre, $description, $prix, $prix_solde, $image_name, $id_cat, $date_sortie, $id_jeu, $id_vendeur]);
            $message = ['type' => 'ok', 'text' => '✅ Jeu modifié avec succès !'];
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete_jeu') {
        $pdo->prepare("DELETE FROM jeu WHERE id_jeu = ? AND id_vendeur = ?")->execute([$_POST['id_jeu'], $id_vendeur]);
        $message = ['type' => 'warn', 'text' => '🗑️ Jeu retiré de la vente.'];
    }
}

// Data //
$categories = $pdo->query("SELECT * FROM categorie ORDER BY nom_cat")->fetchAll();

$stmtMesJeux = $pdo->prepare("SELECT j.*, c.nom_cat FROM jeu j JOIN categorie c ON j.id_cat = c.id_cat WHERE j.id_vendeur = ? ORDER BY j.titre");
$stmtMesJeux->execute([$id_vendeur]);
$mes_jeux = $stmtMesJeux->fetchAll();

// Stats rapides vendeur
$stats = $pdo->query("
    SELECT COUNT(v.id_vente) AS total_ventes, COALESCE(SUM(v.prix_paye),0) AS ca_total
    FROM historique_ventes v JOIN jeu j ON v.id_jeu = j.id_jeu
    WHERE j.id_vendeur = $id_vendeur
")->fetch();

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
    <style>
        .v-card { background:#13151e; border:1px solid #252836; border-radius:10px; padding:24px; }
        .v-label { display:block; color:#9aa0b4; font-size:13px; margin-bottom:5px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
        .v-input { width:100%; padding:9px 12px; background:#0a0b0f; border:1px solid #252836; color:white; border-radius:6px; font-family:inherit; font-size:14px; box-sizing:border-box; }
        .v-input:focus { outline:none; border-color:#0055cc; }
        .v-btn  { padding:11px 20px; border:none; border-radius:6px; font-weight:700; font-size:14px; cursor:pointer; transition:.2s; }
        .v-btn-primary   { background:#f39c12; color:#fff; }
        .v-btn-primary:hover { background:#e67e22; }
        .v-btn-secondary { background:transparent; color:#9aa0b4; border:1px solid #252836; }
        .v-btn-secondary:hover { color:white; border-color:#555; }
        .kpi { background:#13151e; border:1px solid #252836; border-radius:8px; padding:16px 20px; text-align:center; }
        .kpi-val  { font-size:26px; font-weight:700; }
        .kpi-lbl  { font-size:12px; color:#9aa0b4; text-transform:uppercase; letter-spacing:.07em; margin-top:4px; }
        .jeu-row  { display:grid; grid-template-columns:46px 1fr auto auto; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid #1e2130; }
        .jeu-row:last-child { border-bottom:none; }
        .tag-preorder { background:#f39c12; color:#1a1c24; font-size:10px; font-weight:700; padding:2px 6px; border-radius:3px; }
        body.light-theme .v-card { background:#fff; border-color:#e1e5ec; }
        body.light-theme .v-input { background:#f0f2f5; border-color:#d0d5e0; color:#2c3348; }
        body.light-theme .kpi { background:#fff; border-color:#e1e5ec; }
        body.light-theme .jeu-row { border-color:#f0f2f5; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div style="max-width:1280px; margin:36px auto; padding:0 24px;">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
            <div>
                <h1 style="margin:0; font-size:26px;">🏪 Tableau de bord vendeur</h1>
                <span style="color:#9aa0b4; font-size:14px;">Bonjour, <strong style="color:white;"><?php echo htmlspecialchars($_SESSION['pseudo']); ?></strong></span>
            </div>
            <a href="admin_stats.php" class="v-btn v-btn-primary">📊 Voir mes statistiques</a>
        </div>

        <?php if ($message): ?>
        <div style="background:<?php echo $message['type']==='ok' ? 'rgba(46,204,113,.1)' : 'rgba(243,156,18,.1)'; ?>;
                    border:1px solid <?php echo $message['type']==='ok' ? '#2ecc71' : '#f39c12'; ?>;
                    color:<?php echo $message['type']==='ok' ? '#2ecc71' : '#f39c12'; ?>;
                    padding:14px 18px; border-radius:8px; margin-bottom:20px; font-weight:600;">
            <?php echo $message['text']; ?>
        </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:14px; margin-bottom:28px;">
            <div class="kpi">
                <div class="kpi-val" style="color:#2ecc71;"><?php echo number_format($stats['ca_total'],2); ?> €</div>
                <div class="kpi-lbl">Chiffre d'affaires</div>
            </div>
            <div class="kpi">
                <div class="kpi-val" style="color:#3498db;"><?php echo $stats['total_ventes']; ?></div>
                <div class="kpi-lbl">Clés vendues</div>
            </div>
            <div class="kpi">
                <div class="kpi-val" style="color:#f39c12;"><?php echo count($mes_jeux); ?></div>
                <div class="kpi-lbl">Jeux en vente</div>
            </div>
        </div>

        <!-- steam import -->
        <?php if (!$jeu_a_modifier): ?>
        <div class="v-card" style="margin-bottom:24px; border-color:#0055cc;">
            <h2 style="margin:0 0 6px; font-size:18px; color:#3498db;">🎮 Importer un jeu depuis Steam</h2>
            <p style="color:#9aa0b4; font-size:13px; margin:0 0 16px;">
                Entrez l'ID Steam du jeu (ex : <code style="background:#0a0b0f; padding:2px 6px; border-radius:3px;">271590</code> pour GTA V).
                Les informations sont récupérées automatiquement : titre, description, image, catégorie et date de sortie.
            </p>
            <form action="vendeur.php" method="POST" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                <input type="hidden" name="action" value="import_steam">
                <div style="flex:1; min-width:180px;">
                    <label class="v-label">ID Steam du jeu</label>
                    <input class="v-input" type="number" name="import_id_steam"
                           placeholder="Ex : 271590" required min="1">
                </div>
                <button type="submit" class="v-btn v-btn-primary" style="background:#0055cc; white-space:nowrap;">
                    🔍 Importer depuis Steam
                </button>
            </form>
            <p style="margin:12px 0 0; font-size:12px; color:#6b7280;">
                💡 Pour trouver l'ID Steam : allez sur la page du jeu sur
                <a href="https://store.steampowered.com" target="_blank" style="color:#0055cc;">store.steampowered.com</a>
                et regardez l'URL — ex : <em>store.steampowered.com/app/<strong>271590</strong>/</em>
            </p>
        </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:360px 1fr; gap:24px; align-items:start;">

            <div class="v-card">
                <h2 style="margin:0 0 20px; font-size:18px; border-bottom:1px solid #252836; padding-bottom:12px;">
                    <?php echo $jeu_a_modifier ? '✏️ Modifier l\'annonce' : '➕ Mettre un jeu en vente'; ?>
                </h2>
                <form action="vendeur.php" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:14px;">
                    <input type="hidden" name="action" value="<?php echo $jeu_a_modifier ? 'edit_jeu' : 'add_jeu'; ?>">
                    <?php if ($jeu_a_modifier): ?>
                        <input type="hidden" name="id_jeu"    value="<?php echo $jeu_a_modifier['id_jeu']; ?>">
                        <input type="hidden" name="old_image" value="<?php echo $jeu_a_modifier['image']; ?>">
                    <?php endif; ?>

                    <div>
                        <label class="v-label">Titre du jeu *</label>
                        <input class="v-input" type="text" name="titre" required
                               value="<?php echo htmlspecialchars($jeu_a_modifier['titre'] ?? ''); ?>">
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label class="v-label">Prix normal (€) *</label>
                            <input class="v-input" type="number" step="0.01" min="0" name="prix" required
                                   value="<?php echo $jeu_a_modifier['prix'] ?? ''; ?>">
                        </div>
                        <div>
                            <label class="v-label">Prix soldé (€)</label>
                            <input class="v-input" type="number" step="0.01" min="0" name="prix_solde"
                                   placeholder="0 = pas de solde"
                                   value="<?php echo ($jeu_a_modifier['prix_solde'] ?? 0) > 0 ? $jeu_a_modifier['prix_solde'] : ''; ?>">
                        </div>
                    </div>

                    <div>
                        <label class="v-label">Catégorie *</label>
                        <select class="v-input" name="id_cat">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id_cat']; ?>"
                                    <?php if ($jeu_a_modifier && $jeu_a_modifier['id_cat'] == $cat['id_cat']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cat['nom_cat']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="v-label">Date de sortie (laisser vide si déjà sorti)</label>
                        <input class="v-input" type="datetime-local" name="date_sortie"
                               value="<?php echo !empty($jeu_a_modifier['date_sortie']) ? date('Y-m-d\TH:i', strtotime($jeu_a_modifier['date_sortie'])) : ''; ?>">
                    </div>

                    <div>
                        <label class="v-label">Image de couverture</label>
                        <input class="v-input" type="file" name="image" accept="image/*" style="padding:6px;">
                        <?php if (!empty($jeu_a_modifier['image'])): ?>
                            <img src="assets/img/<?php echo htmlspecialchars($jeu_a_modifier['image']); ?>"
                                 style="margin-top:8px; height:60px; border-radius:4px; object-fit:cover;">
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="v-label">Description *</label>
                        <textarea class="v-input" name="description" required rows="4"><?php echo htmlspecialchars($jeu_a_modifier['description'] ?? ''); ?></textarea>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="v-btn v-btn-primary" style="flex:1;">
                            <?php echo $jeu_a_modifier ? 'ENREGISTRER' : 'METTRE EN VENTE'; ?>
                        </button>
                        <?php if ($jeu_a_modifier): ?>
                            <a href="vendeur.php" class="v-btn v-btn-secondary" style="text-decoration:none; display:flex; align-items:center;">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Liste des jeux -->
            <div class="v-card">
                <h2 style="margin:0 0 20px; font-size:18px; border-bottom:1px solid #252836; padding-bottom:12px;">
                    📦 Mes jeux (<?php echo count($mes_jeux); ?>)
                </h2>

                <?php if (empty($mes_jeux)): ?>
                    <div style="text-align:center; padding:50px 20px; color:#9aa0b4;">
                        <p style="font-size:36px; margin:0 0 12px;">🎮</p>
                        <p>Vous n'avez encore mis aucun jeu en vente.</p>
                    </div>
                <?php else: ?>
                    <?php $now = date('Y-m-d H:i:s'); ?>
                    <?php foreach ($mes_jeux as $j): ?>
                        <?php $est_pc = !empty($j['date_sortie']) && $j['date_sortie'] > $now; ?>
                        <div class="jeu-row">
                            <img src="assets/img/<?php echo htmlspecialchars($j['image']); ?>"
                                 style="width:46px; height:60px; object-fit:cover; border-radius:5px;">
                            <div>
                                <div style="font-weight:700; font-size:15px;"><?php echo htmlspecialchars($j['titre']); ?></div>
                                <div style="font-size:12px; color:#9aa0b4; margin-top:2px;">
                                    <?php echo htmlspecialchars($j['nom_cat']); ?>
                                    <?php if ($est_pc): ?>
                                        · <span class="tag-preorder">PRÉCOMMANDE</span>
                                    <?php endif; ?>
                                    <?php if ($j['prix_solde'] > 0): ?>
                                        · <span style="color:#f39c12;">Soldé : <?php echo number_format($j['prix_solde'],2); ?>€</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div style="font-weight:700; color:#2ecc71; font-size:16px; white-space:nowrap;">
                                <?php echo number_format($j['prix'],2); ?> €
                            </div>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <a href="jeu.php?id=<?php echo $j['id_jeu']; ?>" style="color:#9aa0b4; text-decoration:none; font-size:18px;" title="Voir">👁</a>
                                <a href="vendeur.php?edit=<?php echo $j['id_jeu']; ?>" style="color:#3498db; text-decoration:none; font-size:18px;" title="Modifier">✏️</a>
                                <form action="vendeur.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_jeu">
                                    <input type="hidden" name="id_jeu" value="<?php echo $j['id_jeu']; ?>">
                                    <button type="submit" onclick="return confirm('Retirer ce jeu de la vente ?')"
                                            style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:18px;" title="Retirer">🗑</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</body>
</html>
