<?php
// Nom de la page actuelle pour colorier le lien actif dans la nav
$page_actuelle = basename($_SERVER['PHP_SELF']);
?>

<!-- Applique le thème avant que le contenu s'affiche, sinon on voit un flash blanc/noir -->
<script>
(function(){
    var saved = localStorage.getItem('theme');
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    var useLight = saved ? saved === 'light' : !prefersDark;
    if (useLight) {
        document.documentElement.classList.add('light-theme');
        document.body && document.body.classList.add('light-theme');
    }
})();
</script>

<?php
$nb_articles = isset($_SESSION['panier']) ? array_sum($_SESSION['panier']) : 0;
$nb_promos_wishlist = $nb_promos_wishlist ?? 0;
?>

<style>
    /* === BARRE DE NAV === */
    .dg-nav-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
        height: 110px;
        background: #1a1c24;
        width: 100%;
        box-sizing: border-box;
        border-bottom: 1px solid #2a2c35;
        position: sticky;
        top: 0;
        z-index: 500;
    }

    /* Logo */
    .dg-logo img { height: 95px; display: block; }

    /* Barre de recherche */
    .dg-search-wrapper {
        position: relative;
        flex: 1;
        max-width: 420px;
        margin: 0 20px;
    }
    .dg-search-bar {
        display: flex;
        background: #2a2c35;
        border-radius: 6px;
        border: 1px solid #333;
        overflow: hidden;
    }
    .dg-search-bar input {
        flex: 1;
        background: transparent !important;
        border: none !important;
        color: white !important;
        padding: 9px 14px !important;
        font-size: 14px !important;
        outline: none !important;
    }
    .dg-search-bar button {
        background: #ff4757 !important;
        border: none !important;
        color: white !important;
        padding: 0 14px !important;
        cursor: pointer !important;
        transition: background .15s;
    }
    .dg-search-bar button:hover { background: #ff1f38 !important; }
    .dg-search-results {
        display: none;
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        width: 100%;
        background: #1f2029;
        border: 1px solid #333;
        border-radius: 6px;
        z-index: 9999;
        box-shadow: 0 10px 30px rgba(0,0,0,.8);
        overflow: hidden;
    }

    /* Liens nav bureau */
    .dg-nav-links {
        display: flex;
        gap: 18px;
        align-items: center;
    }
    .dg-nav-links a {
        color: #ccc;
        text-decoration: none;
        font-weight: 700;
        font-size: 15px;
        transition: color .15s;
        white-space: nowrap;
    }
    .dg-nav-links a:hover,
    .dg-nav-links a.active { color: #ff4757; }
    .dg-nav-links a.preorder { color: #f39c12 !important; }

    /* Actions droite (compte, panier, thème) */
    .dg-nav-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .dg-nav-actions a {
        color: #b3b3b3;
        text-decoration: none;
        font-size: 14px;
        white-space: nowrap;
        transition: color .15s;
    }
    .dg-nav-actions a:hover { color: white; }

    /* Bouton panier */
    .dg-cart-btn {
        background: #0055cc;
        padding: 7px 14px;
        border-radius: 5px;
        color: white !important;
        font-weight: 700;
        font-size: 14px;
        transition: background .15s !important;
    }
    .dg-cart-btn:hover { background: #1a6fff !important; color: white !important; }

    /* Bouton thème */
    .dg-theme-btn {
        background: transparent;
        border: 1px solid #3a3b40;
        color: #9aa0b4;
        padding: 6px 10px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: border-color .15s, color .15s;
        line-height: 1;
    }
    .dg-theme-btn:hover { border-color: #9aa0b4; color: white; }

    /* === HAMBURGER (visible seulement sur mobile) === */
    .dg-hamburger {
        display: none;
        flex-direction: column;
        justify-content: center;
        gap: 5px;
        cursor: pointer;
        padding: 6px;
        border: none;
        background: transparent;
        flex-shrink: 0;
    }
    .dg-hamburger span {
        display: block;
        width: 24px;
        height: 2px;
        background: #ccc;
        border-radius: 2px;
        transition: transform .25s, opacity .25s;
    }
    .dg-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .dg-hamburger.open span:nth-child(2) { opacity: 0; }
    .dg-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

    /* === MENU MOBILE (drawer) === */
    .dg-mobile-menu {
        display: none; /* caché par défaut sur bureau */
        position: fixed;
        top: 110px;
        left: 0;
        right: 0;
        bottom: 0;
        background: #13151e;
        z-index: 490;
        overflow-y: auto;
        flex-direction: column;
        padding: 20px;
        gap: 0;
        transform: translateX(-100%);
        transition: transform .25s ease;
    }
    .dg-mobile-menu.open { transform: translateX(0); }

    .dg-mobile-menu a {
        color: #ccc;
        text-decoration: none;
        font-size: 18px;
        font-weight: 700;
        padding: 16px 0;
        border-bottom: 1px solid #1e2130;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: color .15s;
    }
    .dg-mobile-menu a:hover,
    .dg-mobile-menu a.active { color: #ff4757; }
    .dg-mobile-menu a.preorder { color: #f39c12 !important; }
    .dg-mobile-menu a.cart    { color: white !important; background: #0055cc; border-radius: 6px; padding: 14px 16px; border-bottom: none; margin-top: 10px; justify-content: center; }

    /* Recherche mobile (dans le menu) */
    .dg-mobile-search {
        display: flex;
        background: #2a2c35;
        border-radius: 6px;
        border: 1px solid #333;
        overflow: hidden;
        margin-bottom: 10px;
    }
    .dg-mobile-search input {
        flex: 1;
        background: transparent;
        border: none;
        color: white;
        padding: 12px 14px;
        font-size: 15px;
        outline: none;
    }
    .dg-mobile-search button {
        background: #ff4757;
        border: none;
        color: white;
        padding: 0 16px;
        cursor: pointer;
    }

    /* === RESPONSIVE === */
    @media (max-width: 768px) {
        .dg-nav-links   { display: none; }
        .dg-nav-actions { display: none; }
        .dg-search-wrapper { display: none; }
        .dg-hamburger   { display: flex; }
        .dg-mobile-menu { display: flex; }
        .dg-logo img      { height: 58px; }
        .dg-nav-container { height: 76px; }
        .dg-mobile-menu   { top: 76px; }
    }

    /* Mode clair */
    body.light-theme .dg-nav-container { background: #ffffff; border-bottom-color: #e1e5ec; }
    body.light-theme .dg-search-bar    { background: #f0f2f5; border-color: #d0d5e0; }
    body.light-theme .dg-search-bar input { color: #2c3348 !important; }
    body.light-theme .dg-search-results  { background: #ffffff; border-color: #e1e5ec; }
    body.light-theme .dg-search-results a { color: #2c3348 !important; background: #fafafa !important; }
    body.light-theme .dg-nav-links a  { color: #4b5563; }
    body.light-theme .dg-nav-links a:hover,
    body.light-theme .dg-nav-links a.active { color: #ff4757; }
    body.light-theme .dg-nav-actions a { color: #4b5563; }
    body.light-theme .dg-nav-actions a:hover { color: #2c3348; }
    body.light-theme .dg-theme-btn { border-color: #d0d5e0; color: #4b5563; }
    body.light-theme .dg-theme-btn:hover { color: #2c3348; }
    body.light-theme .dg-hamburger span { background: #4b5563; }
    body.light-theme .dg-mobile-menu { background: #f8f9fb; }
    body.light-theme .dg-mobile-menu a { color: #2c3348; border-bottom-color: #e1e5ec; }
    body.light-theme .dg-mobile-search { background: #f0f2f5; border-color: #d0d5e0; }
    body.light-theme .dg-mobile-search input { color: #2c3348; }
</style>

<nav class="dg-nav-container">

    <!-- Logo -->
    <div class="dg-logo">
        <a href="index.php"><img src="assets/img/logo.jpg" alt="Digital Games"></a>
    </div>

    <!-- Recherche (bureau) -->
    <div class="dg-search-wrapper">
        <form action="catalogue.php" method="GET" style="margin:0;">
            <div class="dg-search-bar">
                <input type="text" id="live-search" name="search"
                       placeholder="Rechercher un jeu..."
                       autocomplete="off"
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit">🔍</button>
            </div>
        </form>
        <div id="search-results" class="dg-search-results"></div>
    </div>

    <!-- Liens (bureau) -->
    <div class="dg-nav-links">
        <a href="index.php" <?php echo $page_actuelle === 'index.php' ? 'class="active"' : ''; ?>>Accueil</a>
        <a href="catalogue.php" <?php echo $page_actuelle === 'catalogue.php' ? 'class="active"' : ''; ?>>Catalogue</a>
        <a href="prochaines_sorties.php" class="preorder">Précommandes</a>
    </div>

    <!-- Actions droite (bureau) -->
    <div class="dg-nav-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="wishlist.php" title="Wishlist">🔔</a>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" style="color:#2ecc71; font-weight:bold;">⚙ Admin</a>
            <?php endif; ?>
            <a href="mon_compte.php">👤 <?php echo htmlspecialchars($_SESSION['pseudo']); ?></a>
            <a href="deconnexion.php" style="color:#ff4757;">Déco</a>
        <?php else: ?>
            <a href="connexion.php">👤 Connexion</a>
        <?php endif; ?>

        <a href="panier.php" class="dg-cart-btn">🛒 <?php echo $nb_articles; ?></a>

        <!-- Toggle thème -->
        <button class="dg-theme-btn" id="theme-toggle" title="Changer le thème">🌙</button>
    </div>

    <!-- Hamburger (mobile) -->
    <button class="dg-hamburger" id="dg-hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>

</nav>

<!-- Menu mobile (drawer) -->
<div class="dg-mobile-menu" id="dg-mobile-menu">

    <!-- Recherche mobile -->
    <form action="catalogue.php" method="GET" style="margin-bottom:10px;">
        <div class="dg-mobile-search">
            <input type="text" name="search" placeholder="Rechercher un jeu..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit">🔍</button>
        </div>
    </form>

    <a href="index.php" <?php echo $page_actuelle === 'index.php' ? 'class="active"' : ''; ?>>🏠 Accueil</a>
    <a href="catalogue.php" <?php echo $page_actuelle === 'catalogue.php' ? 'class="active"' : ''; ?>>🎮 Catalogue</a>
    <a href="prochaines_sorties.php" class="preorder">🕐 Précommandes</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="wishlist.php">🔔 Ma Wishlist</a>
        <a href="bibliotheque.php">📚 Ma Bibliothèque</a>
        <a href="mes_commandes.php">📦 Mes commandes</a>
        <a href="mon_compte.php">👤 <?php echo htmlspecialchars($_SESSION['pseudo']); ?></a>
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" style="color:#2ecc71;">⚙ Administration</a>
        <?php endif; ?>
        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'tiers'): ?>
            <a href="vendeur.php" style="color:#f39c12;">🏪 Espace vendeur</a>
        <?php endif; ?>
        <a href="deconnexion.php" style="color:#ff4757;">🚪 Déconnexion</a>
    <?php else: ?>
        <a href="connexion.php">👤 Se connecter</a>
        <a href="inscription.php">✍ Créer un compte</a>
    <?php endif; ?>

    <!-- Thème dans le menu mobile aussi -->
    <div style="padding:16px 0; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #1e2130;">
        <span style="font-size:15px; color:#9aa0b4;">Thème</span>
        <button class="dg-theme-btn" id="theme-toggle-mobile">🌙</button>
    </div>

    <a href="panier.php" class="cart">🛒 Panier (<?php echo $nb_articles; ?>)</a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // === Recherche live ===
    const searchInput = document.getElementById('live-search');
    const resultsBox  = document.getElementById('search-results');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim();
            if (q.length < 1) { resultsBox.style.display = 'none'; return; }
            fetch('search_ajax.php?q=' + encodeURIComponent(q))
                .then(r => r.text())
                .then(html => {
                    resultsBox.innerHTML = html;
                    resultsBox.style.display = 'block';
                });
        });
    }
    document.addEventListener('click', function(e) {
        if (resultsBox && !e.target.closest('.dg-search-wrapper')) {
            resultsBox.style.display = 'none';
        }
    });

    // === Menu hamburger ===
    const burger     = document.getElementById('dg-hamburger');
    const mobileMenu = document.getElementById('dg-mobile-menu');

    if (burger && mobileMenu) {
        burger.addEventListener('click', function() {
            burger.classList.toggle('open');
            mobileMenu.classList.toggle('open');
            // bloque le scroll quand le menu est ouvert
            document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
        });
        // fermer si on clique sur un lien dans le menu
        mobileMenu.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                burger.classList.remove('open');
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
    }

    // === Toggle thème (bureau + mobile) ===
    function appliquerTheme(light) {
        document.body.classList.toggle('light-theme', light);
        document.documentElement.classList.toggle('light-theme', light);
        localStorage.setItem('theme', light ? 'light' : 'dark');
        const ico = light ? '☀️' : '🌙';
        const btns = document.querySelectorAll('#theme-toggle, #theme-toggle-mobile');
        btns.forEach(function(b) { b.textContent = ico; });
    }

    // Met à jour l'icône au chargement
    const isLight = document.body.classList.contains('light-theme');
    document.querySelectorAll('#theme-toggle, #theme-toggle-mobile').forEach(function(b) {
        b.textContent = isLight ? '☀️' : '🌙';
    });

    document.querySelectorAll('#theme-toggle, #theme-toggle-mobile').forEach(function(btn) {
        btn.addEventListener('click', function() {
            appliquerTheme(!document.body.classList.contains('light-theme'));
        });
    });

    // Écoute les changements OS si l'utilisateur n'a pas forcé de préférence
    var mq = window.matchMedia('(prefers-color-scheme: dark)');
    mq.addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            appliquerTheme(!e.matches);
        }
    });

});
</script>
<script src="assets/js/main.js"></script>
