<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions Générales de Vente - Digital Games</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container" style="max-width: 900px; margin-top: 40px; margin-bottom: 60px;">
        <h2 class="section-title">Conditions Générales de Vente</h2>

        <div style="color: #b3b3b3; line-height: 1.8;">
            
            <h3 style="color: var(--text-white); margin-top: 30px;">1. Objet</h3>
            <p>
                Les présentes conditions générales de vente régissent les relations commerciales entre Digital Games et ses clients 
                pour la vente de clés CD officielles de jeux vidéo. L'accès et l'utilisation du site impliquent l'acceptation 
                pleine et entière de ces conditions.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">2. Produits et prix</h3>
            <p>
                <strong>Disponibilité :</strong> Les produits sont proposés dans la limite des stocks disponibles. 
                Digital Games se réserve le droit de modifier les prix sans préavis.<br>
                <strong>Prix :</strong> Les prix affichés sont en euros TTC. Les frais de port, le cas échéant, seront indiqués 
                avant la validation de la commande.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">3. Commande</h3>
            <p>
                <strong>Processus :</strong> Pour commander, le client doit remplir son panier et procéder au paiement sécurisé.<br>
                <strong>Confirmation :</strong> Une confirmation de commande sera envoyée à l'adresse email fournie lors de l'achat.<br>
                <strong>Livraison :</strong> Les clés CD sont livrées instantanément par email après validation du paiement.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">4. Paiement</h3>
            <p>
                <strong>Modes de paiement :</strong> Digital Games accepte les paiements par carte bancaire et PayPal.<br>
                <strong>Sécurité :</strong> Les paiements sont traités par Stripe, un prestataire de paiement sécurisé et certifié PCI DSS.<br>
                <strong>Validation :</strong> Le paiement doit être autorisé par votre banque pour que la commande soit validée.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">5. Clés de produit</h3>
            <p>
                <strong>Génération :</strong> Les clés de produit sont générées automatiquement après validation du paiement.<br>
                <strong>Activation :</strong> Les clés sont activables immédiatement sur les plateformes respectives (Steam, Epic Games, etc.).<br>
                <strong>Vérification :</strong> Toutes nos clés proviennent de fournisseurs officiels et sont 100% légales et authentiques.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">6. Droit de rétractation</h3>
            <p>
                Conformément à la loi, le droit de rétractation est EXCU pour les jeux vidéo et les clés de produit, 
                car ceux-ci constituent des contenus numériques dont la fourniture commence immédiatement après achat. 
                Aucun remboursement ne sera possible une fois la clé générée et transmise.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">7. Responsabilité</h3>
            <p>
                <strong>Garantie limitée :</strong> Digital Games garantit que les clés vendues sont authentiques et proviennent de sources légales.<br>
                <strong>En cas de problème :</strong> Si une clé ne fonctionne pas, veuillez contacter immédiatement notre équipe support à contact@digitalgames.fr 
                avec la preuve d'achat. Nous fournirons une clé de remplacement ou un remboursement au cas par cas.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">8. Données personnelles</h3>
            <p>
                Les données collectées sont utilisées pour traiter votre commande, vous envoyer des confirmations 
                et vous informer des nouvelles promotions. Vous pouvez vous désabonner à tout moment. 
                Consulter notre <a href="mentions-legales.php" style="color: var(--primary-blue); text-decoration: none;">politique de confidentialité</a> 
                pour plus d'informations.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">9. Litiges et réclamations</h3>
            <p>
                En cas de litige, le client accepte de nous contacter d'abord pour trouver une solution amiable. 
                Les litiges non résolus seront soumis à la juridiction des tribunaux français.
            </p>

            <h3 style="color: var(--text-white); margin-top: 30px;">10. Modification des conditions</h3>
            <p>
                Digital Games se réserve le droit de modifier ces conditions générales à tout moment. 
                Les modifications entreront en vigueur dès leur publication sur le site.
            </p>

            <p style="margin-top: 40px; text-align: center; color: var(--text-grey);">
                Dernière mise à jour : <?php echo date('d/m/Y'); ?>
            </p>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
