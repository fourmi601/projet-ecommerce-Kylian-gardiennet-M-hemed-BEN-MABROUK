<?php
session_start();

$code    = htmlspecialchars($_GET['code']    ?? 'inconnu');
$message = htmlspecialchars($_GET['message'] ?? 'Une erreur inattendue est survenue.');
$contexte = htmlspecialchars($_GET['contexte'] ?? '');

$titres = [
    'panier_vide'     => 'Panier vide',
    'non_connecte'    => 'Non connecté',
    'banque_refus'    => 'Paiement refusé par la banque',
    'banque_timeout'  => 'Serveur bancaire injoignable',
    'token_invalide'  => 'Transaction invalide',
    'commande_echec'  => 'Erreur lors de la commande',
];

$conseils = [
    'panier_vide'     => 'Ajoutez des jeux à votre panier avant de passer commande.',
    'non_connecte'    => 'Vous devez être connecté à votre compte Digital Games pour payer.',
    'banque_refus'    => 'Votre banque a refusé la transaction. Vérifiez votre solde ou contactez Ecotech Bank.',
    'banque_timeout'  => 'Le serveur de paiement ne répond pas. Réessayez dans quelques minutes.',
    'token_invalide'  => 'Le jeton de transaction est absent ou invalide. Votre commande n\'a pas été enregistrée.',
    'commande_echec'  => 'Le paiement a bien été accepté mais une erreur technique a empêché l\'enregistrement. Contactez le support.',
];

$titre_page = $titres[$code]  ?? 'Erreur de paiement';
$conseil    = $conseils[$code] ?? 'Veuillez réessayer ou contacter le support si le problème persiste.';

$lien_retour = match($code) {
    'panier_vide', 'banque_refus', 'token_invalide' => 'panier.php',
    'non_connecte'   => 'connexion.php',
    'banque_timeout' => 'paiement.php',
    default          => 'index.php',
};

$label_retour = match($code) {
    'panier_vide'    => 'Aller au catalogue',
    'non_connecte'   => 'Se connecter',
    'banque_timeout' => 'Réessayer le paiement',
    default          => 'Retour au panier',
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur — <?php echo $titre_page; ?> · Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div style="max-width: 620px; margin: 80px auto; padding: 0 20px;">

        <!-- Icône + titre -->
        <div style="text-align: center; margin-bottom: 36px;">
            <div style="font-size: 72px; line-height: 1; margin-bottom: 16px;">❌</div>
            <h1 style="font-size: 32px; font-weight: 700; color: #ff4757; margin: 0 0 8px;">
                <?php echo $titre_page; ?>
            </h1>
            <p style="color: var(--text-grey, #9aa0b4); font-size: 16px; margin: 0;">
                Code d'erreur : <code style="background: #1a1d29; padding: 2px 8px; border-radius: 4px;"><?php echo $code; ?></code>
            </p>
        </div>

        <!-- Carte principale -->
        <div style="background: #13151e; border: 1px solid #252836; border-radius: 10px; padding: 32px; margin-bottom: 24px;">

            <!-- Message d'erreur -->
            <div style="display: flex; gap: 14px; background: rgba(255,71,87,0.08); border: 1px solid rgba(255,71,87,0.3); border-radius: 8px; padding: 18px; margin-bottom: 24px;">
                <span style="font-size: 22px; flex-shrink: 0;">⚠️</span>
                <p style="margin: 0; color: #ff7b88; font-size: 15px; line-height: 1.5;"><?php echo $message; ?></p>
            </div>

            <!-- Conseil -->
            <div style="display: flex; gap: 14px; background: rgba(52,152,219,0.08); border: 1px solid rgba(52,152,219,0.25); border-radius: 8px; padding: 18px; margin-bottom: 28px;">
                <span style="font-size: 22px; flex-shrink: 0;">💡</span>
                <p style="margin: 0; color: #a8d4f0; font-size: 15px; line-height: 1.5;"><?php echo $conseil; ?></p>
            </div>

            <?php if ($contexte): ?>
            <!-- Détail technique (caché par défaut) -->
            <details style="margin-bottom: 20px;">
                <summary style="cursor: pointer; color: #9aa0b4; font-size: 13px; user-select: none;">Détail technique</summary>
                <pre style="background: #0a0b0f; color: #2ecc71; padding: 12px; border-radius: 6px; font-size: 12px; overflow-x: auto; margin-top: 10px;"><?php echo $contexte; ?></pre>
            </details>
            <?php endif; ?>

            <!-- Boutons d'action -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="<?php echo $lien_retour; ?>"
                   style="flex: 1; min-width: 160px; background: #ff4757; color: white; text-decoration: none; padding: 13px 20px; border-radius: 6px; font-weight: 700; text-align: center; font-size: 15px; transition: background 0.2s;"
                   onmouseover="this.style.background='#ff1f38'" onmouseout="this.style.background='#ff4757'">
                    <?php echo $label_retour; ?>
                </a>
                <a href="contact.php"
                   style="flex: 1; min-width: 160px; background: transparent; color: #9aa0b4; text-decoration: none; padding: 13px 20px; border-radius: 6px; font-weight: 700; text-align: center; font-size: 15px; border: 1px solid #252836; transition: 0.2s;"
                   onmouseover="this.style.color='white';this.style.borderColor='#555'" onmouseout="this.style.color='#9aa0b4';this.style.borderColor='#252836'">
                    Contacter le support
                </a>
            </div>
        </div>

        <!-- Aide rapide -->
        <div style="background: #13151e; border: 1px solid #252836; border-radius: 10px; padding: 24px;">
            <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 14px; color: #9aa0b4;">Questions fréquentes</h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="border-bottom: 1px solid #1e2130; padding-bottom: 10px;">
                    <p style="margin: 0 0 4px; font-weight: 700; font-size: 14px;">Mon compte a-t-il été débité ?</p>
                    <p style="margin: 0; color: #9aa0b4; font-size: 13px;">
                        <?php if (in_array($code, ['token_invalide', 'banque_refus'])): ?>
                            Non. La transaction a été annulée côté bancaire, aucun débit n'a eu lieu.
                        <?php elseif ($code === 'commande_echec'): ?>
                            Peut-être. Contactez le support avec votre numéro de commande bancaire pour vérification.
                        <?php else: ?>
                            Non, aucun paiement n'a été initié.
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <p style="margin: 0 0 4px; font-weight: 700; font-size: 14px;">Mon panier est-il conservé ?</p>
                    <p style="margin: 0; color: #9aa0b4; font-size: 13px;">Oui, votre panier reste intact. Vous pouvez relancer le paiement à tout moment.</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
