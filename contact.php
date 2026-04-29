<?php
// Traitement du formulaire de contact côté serveur (PHP obligatoire)
session_start();

$succes  = false;
$erreurs = [];
$valeurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des champs
    $valeurs['nom']     = htmlspecialchars(trim($_POST['nom']     ?? ''));
    $valeurs['email']   = htmlspecialchars(trim($_POST['email']   ?? ''));
    $valeurs['sujet']   = htmlspecialchars(trim($_POST['sujet']   ?? ''));
    $valeurs['type']    = htmlspecialchars(trim($_POST['type']    ?? ''));
    $valeurs['urgence'] = htmlspecialchars(trim($_POST['urgence'] ?? ''));
    $valeurs['message'] = htmlspecialchars(trim($_POST['message'] ?? ''));
    $valeurs['rgpd']    = isset($_POST['rgpd']);

    // Validation
    if (empty($valeurs['nom']))    $erreurs[] = "Le nom est obligatoire.";
    if (empty($valeurs['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        $erreurs[] = "L'adresse e-mail est invalide.";
    if (empty($valeurs['sujet']))  $erreurs[] = "Le sujet est obligatoire.";
    if (empty($valeurs['type']))   $erreurs[] = "Choisissez un type de demande.";
    if (strlen($_POST['message'] ?? '') < 20)
        $erreurs[] = "Le message doit contenir au moins 20 caractères.";
    if (!$valeurs['rgpd'])         $erreurs[] = "Vous devez accepter la politique de confidentialité.";

    if (empty($erreurs)) {
        // Ici en prod on enverrait un email (mail() ou PHPMailer)
        // Pour le projet on affiche juste le succès (pas de serveur SMTP en dev)
        $succes = true;
        $valeurs = []; // reset le formulaire
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    <style>
        /* Styles spécifiques au formulaire de contact */
        .contact-wrapper { background: var(--bg-panel, #13151e); padding: 40px; border-radius: 10px; border: 1px solid var(--border-color, #252836); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 20px; }
        .form-group label { color: #9aa0b4; font-size: 13px; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 7px; font-weight: 600; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 11px 14px;
            background: #0a0b0f;
            border: 1px solid #252836;
            color: #e0e0e0;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
            transition: border-color .15s;
            outline: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { border-color: #0055cc; }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .contact-submit {
            width: 100%; padding: 14px; background: #ff4757; color: white;
            border: none; border-radius: 6px; font-size: 17px; font-weight: 700;
            font-family: inherit; cursor: pointer; transition: background .15s;
        }
        .contact-submit:hover { background: #ff1f38; }
        .form-error { background: rgba(255,71,87,.1); border: 1px solid #ff4757; color: #ff7b88; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px; }
        .form-error li { margin-left: 18px; line-height: 1.8; }
        .form-success { background: rgba(46,204,113,.1); border: 1px solid #2ecc71; color: #2ecc71; padding: 24px; border-radius: 8px; text-align: center; }
        /* Compteur de caractères */
        #msg-count { font-size: 12px; color: #9aa0b4; text-align: right; margin-top: 4px; }
        /* Responsive */
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        /* Mode clair */
        body.light-theme .contact-wrapper { background: #fff; border-color: #e1e5ec; }
        body.light-theme .form-group label { color: #6b7280; }
        body.light-theme .form-group input,
        body.light-theme .form-group select,
        body.light-theme .form-group textarea { background: #f8f9fb; border-color: #d0d5e0; color: #2c3348; }
        body.light-theme #msg-count { color: #9ca3af; }
        body.light-theme .contact-submit { color: #fff; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container" style="max-width: 760px; margin-top: 50px; margin-bottom: 60px;">
        <h1 class="section-title">Contactez-nous</h1>
        <p style="color: #9aa0b4; margin-bottom: 32px; font-size: 16px;">
            Une question, un problème de commande ou une suggestion ?<br>
            Remplissez le formulaire ci-dessous, nous répondons généralement sous 24h.
        </p>

        <?php if ($succes): ?>
            <!-- Message de confirmation -->
            <div class="form-success">
                <p style="font-size: 40px; margin: 0 0 10px;">✅</p>
                <h2 style="margin: 0 0 8px;">Message envoyé !</h2>
                <p style="margin: 0; font-size: 15px;">Merci de nous avoir contactés. Nous vous répondrons dans les plus brefs délais.</p>
            </div>

        <?php else: ?>
        <div class="contact-wrapper">

            <?php if (!empty($erreurs)): ?>
                <div class="form-error">
                    <strong>Veuillez corriger les erreurs suivantes :</strong>
                    <ul>
                        <?php foreach ($erreurs as $err): ?>
                            <li><?php echo $err; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="contact.php" method="POST" novalidate>

                <div class="form-row">
                    <!-- Nom -->
                    <div class="form-group">
                        <label for="nom">Votre nom *</label>
                        <input type="text" id="nom" name="nom"
                               placeholder="Jean Dupont"
                               value="<?php echo $valeurs['nom'] ?? ''; ?>"
                               required>
                    </div>
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Adresse e-mail *</label>
                        <input type="email" id="email" name="email"
                               placeholder="vous@exemple.fr"
                               value="<?php echo $valeurs['email'] ?? ''; ?>"
                               required>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Type de demande (liste déroulante) -->
                    <div class="form-group">
                        <label for="type">Type de demande *</label>
                        <select id="type" name="type" required>
                            <option value="">— Sélectionnez —</option>
                            <option value="commande"  <?php if(($valeurs['type'] ?? '') === 'commande')  echo 'selected'; ?>>Problème de commande</option>
                            <option value="cle"       <?php if(($valeurs['type'] ?? '') === 'cle')       echo 'selected'; ?>>Clé CD invalide</option>
                            <option value="paiement"  <?php if(($valeurs['type'] ?? '') === 'paiement')  echo 'selected'; ?>>Problème de paiement</option>
                            <option value="compte"    <?php if(($valeurs['type'] ?? '') === 'compte')    echo 'selected'; ?>>Mon compte</option>
                            <option value="vendeur"   <?php if(($valeurs['type'] ?? '') === 'vendeur')   echo 'selected'; ?>>Devenir vendeur</option>
                            <option value="autre"     <?php if(($valeurs['type'] ?? '') === 'autre')     echo 'selected'; ?>>Autre</option>
                        </select>
                    </div>
                    <!-- Niveau d'urgence (radio visuel → rendu select sur mobile) -->
                    <div class="form-group">
                        <label for="urgence">Urgence</label>
                        <select id="urgence" name="urgence">
                            <option value="normal"  <?php if(($valeurs['urgence'] ?? 'normal') === 'normal')  echo 'selected'; ?>>Normal</option>
                            <option value="urgent"  <?php if(($valeurs['urgence'] ?? '') === 'urgent')  echo 'selected'; ?>>Urgent (sous 4h)</option>
                            <option value="info"    <?php if(($valeurs['urgence'] ?? '') === 'info')    echo 'selected'; ?>>Simple information</option>
                        </select>
                    </div>
                </div>

                <!-- Sujet -->
                <div class="form-group">
                    <label for="sujet">Sujet *</label>
                    <input type="text" id="sujet" name="sujet"
                           placeholder="Résumez votre demande en quelques mots"
                           value="<?php echo $valeurs['sujet'] ?? ''; ?>"
                           required maxlength="100">
                </div>

                <!-- Numéro de commande (affiché dynamiquement si type = commande / clé) -->
                <div class="form-group" id="group-commande" style="display:none;">
                    <label for="num_commande">Numéro de commande</label>
                    <input type="text" id="num_commande" name="num_commande"
                           placeholder="Ex : 42"
                           value="<?php echo htmlspecialchars($_POST['num_commande'] ?? ''); ?>">
                </div>

                <!-- Message -->
                <div class="form-group">
                    <label for="message">Votre message * <small style="font-weight:400;">(20 caractères min.)</small></label>
                    <textarea id="message" name="message"
                              placeholder="Décrivez votre demande en détail…"
                              maxlength="2000"
                              required><?php echo $valeurs['message'] ?? ''; ?></textarea>
                    <span id="msg-count">0 / 2000 caractères</span>
                </div>

                <!-- RGPD -->
                <div class="form-group" style="flex-direction:row; align-items:flex-start; gap:10px; margin-bottom:28px;">
                    <input type="checkbox" id="rgpd" name="rgpd"
                           style="margin-top:3px; width:auto; accent-color:#0055cc;"
                           <?php if (!empty($valeurs['rgpd'])) echo 'checked'; ?> required>
                    <label for="rgpd" style="text-transform:none; letter-spacing:0; font-size:14px; color:#9aa0b4; margin-bottom:0;">
                        J'accepte que mes données soient utilisées pour traiter ma demande.
                        <a href="mentions-legales.php" style="color:#0055cc;">En savoir plus</a>.
                    </label>
                </div>

                <button type="submit" class="contact-submit">Envoyer le message →</button>
            </form>
        </div>
        <?php endif; ?>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    // Affichage conditionnel du champ "numéro de commande" selon le type choisi
    document.getElementById('type').addEventListener('change', function() {
        var show = ['commande', 'cle', 'paiement'].includes(this.value);
        document.getElementById('group-commande').style.display = show ? 'flex' : 'none';
        document.getElementById('num_commande').required = show;
    });

    // Pré-afficher si la valeur est déjà sélectionnée (après une erreur PHP)
    (function() {
        var type = document.getElementById('type').value;
        if (['commande', 'cle', 'paiement'].includes(type)) {
            document.getElementById('group-commande').style.display = 'flex';
        }
    })();

    // Compteur de caractères pour le textarea
    var textarea = document.getElementById('message');
    var counter  = document.getElementById('msg-count');
    function majCompteur() {
        var len = textarea.value.length;
        counter.textContent = len + ' / 2000 caractères';
        counter.style.color = len < 20 ? '#ff4757' : (len > 1800 ? '#f39c12' : '#9aa0b4');
    }
    textarea.addEventListener('input', majCompteur);
    majCompteur();

    // Validation visuelle en direct sur les champs requis
    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(function(el) {
        el.addEventListener('blur', function() {
            var ok = this.checkValidity();
            this.style.borderColor = ok ? '' : '#ff4757';
        });
        el.addEventListener('input', function() {
            if (this.checkValidity()) this.style.borderColor = '';
        });
    });
    </script>
</body>
</html>
