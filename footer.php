<?php
// footer commun — inclure juste avant </body> dans toutes les pages
?>
<footer class="site-footer">
    <div class="footer-container">

        <!-- Colonne logo + descr -->
        <div class="footer-col">
            <img src="assets/img/logo.jpg" alt="Digital Games" class="footer-logo">
            <p>Votre boutique de clés CD officielles.<br>
               Livraison instantanée, paiement sécurisé via <strong style="color:var(--text-white);">Ecotech Bank</strong>.</p>
        </div>

        <!-- Liens rapides -->
        <div class="footer-col">
            <h3>Navigation</h3>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="catalogue.php">Catalogue</a></li>
                <li><a href="prochaines_sorties.php">Précommandes</a></li>
                <li><a href="panier.php">Mon panier</a></li>
            </ul>
        </div>

        <!-- Informations légales -->
        <div class="footer-col">
            <h3>Informations</h3>
            <ul>
                <li><a href="mentions-legales.php">Mentions légales</a></li>
                <li><a href="cgv.php">Conditions Générales de Vente</a></li>
                <li><a href="contact.php">Nous contacter</a></li>
            </ul>
        </div>

        <!-- Paiement -->
        <div class="footer-col">
            <h3>Paiement sécurisé</h3>
            <p style="font-size:13px; margin-bottom:12px;">Toutes les transactions sont traitées par Ecotech Bank, notre partenaire bancaire agréé.</p>
            <div class="payment-icons">
                <span style="display:inline-flex; align-items:center; gap:6px;">🏦 Ecotech Bank</span>
                <span style="display:inline-flex; align-items:center; gap:6px;">💳 Carte bancaire</span>
            </div>
            <!-- Petit badge sécurité -->
            <div style="margin-top:14px; display:flex; align-items:center; gap:8px; color:var(--text-grey); font-size:13px;">
                <span style="font-size:18px;">🔒</span>
                <span>Connexion SSL chiffrée</span>
            </div>
        </div>

    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Digital Games &mdash; Projet BTS. Tous droits réservés.</p>
    </div>
</footer>
