<?php
$page_actuelle = basename($_SERVER['PHP_SELF']);
$nb_articles = 0;
if (isset($_SESSION['panier'])) {
    $nb_articles = array_sum($_SESSION['panier']);
}
$nb_promos_wishlist = $nb_promos_wishlist ?? 0;
?>

<style>
    /* --- CSS BLINDÉ CONTRE TON STYLE.CSS --- */
    .dg-nav-container { display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; background: #1a1c24; width: 100%; box-sizing: border-box; border-bottom: 1px solid #2a2c35; }
    
    .dg-search-wrapper { position: relative; flex: 1; max-width: 450px; margin: 0 30px; }
    
    .dg-search-bar { display: flex; width: 100%; background: #2a2c35; border-radius: 6px; border: 1px solid #333; overflow: hidden; }
    
    /* On tue le contour bleu du TAB ici */
    .dg-search-bar input { flex: 1; background: transparent !important; border: none !important; color: white !important; padding: 10px 15px !important; font-size: 15px !important; outline: none !important; box-shadow: none !important; }
    .dg-search-bar input:focus { outline: none !important; box-shadow: none !important; border: none !important; }
    
    .dg-search-bar button { background: #ff4757 !important; border: none !important; color: white !important; padding: 0 15px !important; cursor: pointer !important; outline: none !important; transition: 0.2s; }
    .dg-search-bar button:hover { background: #ff1f38 !important; }
    
    /* La boîte Instant Gaming */
    .dg-search-results { display: none; position: absolute; top: calc(100% + 5px); left: 0; width: 100%; background: #1f2029; border: 1px solid #333; border-radius: 6px; z-index: 9999; box-shadow: 0 10px 30px rgba(0,0,0,0.8); overflow: hidden; }
</style>

<nav class="dg-nav-container">
    
    <div class="logo-container">
        <a href="index.php"><img src="assets/img/logo.jpg" alt="Logo" style="height: 125px;"></a>
    </div>

    <div class="dg-search-wrapper">
        <form action="catalogue.php" method="GET" style="margin: 0;">
            <div class="dg-search-bar">
                <input type="text" id="live-search" name="search" placeholder="Rechercher un jeu..." autocomplete="off" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit">🔍</button>
            </div>
        </form>
        <div id="search-results" class="dg-search-results"></div>
    </div>

    <div style="display: flex; gap: 20px;">
        <a href="index.php" style="color: <?php echo ($page_actuelle === 'index.php') ? '#ff4757' : 'white'; ?>; text-decoration: none; font-weight: bold;">Accueil</a>
        <a href="catalogue.php" style="color: <?php echo ($page_actuelle === 'catalogue.php') ? '#ff4757' : 'white'; ?>; text-decoration: none; font-weight: bold;">Catalogue</a>
        <a href="prochaines_sorties.php" style="color: #f39c12; font-weight: bold; text-decoration: none;">Précommandes</a>
    </div>

    <div style="display: flex; align-items: center; gap: 15px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="wishlist.php" style="text-decoration: none; font-size: 20px;">🔔</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" style="color: #2ecc71; text-decoration: none; font-weight: bold;">⚙️ Admin</a>
            <?php endif; ?>
            <a href="mon_compte.php" style="color: #b3b3b3; text-decoration: none;">👤 <?php echo htmlspecialchars($_SESSION['pseudo']); ?></a>
            <a href="deconnexion.php" style="color: #ff4757; text-decoration: none;">Déconnexion</a>
        <?php else: ?>
            <a href="connexion.php" style="color: white; text-decoration: none;">👤 Compte</a>
        <?php endif; ?>
        
        <a href="panier.php" style="background: #0056b3; padding: 8px 15px; border-radius: 4px; color: white; text-decoration: none; font-weight: bold;">🛒 (<?php echo $nb_articles; ?>)</a>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('live-search');
    const resultsBox = document.getElementById('search-results');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            let query = this.value.trim();
            if (query.length > 0) {
                fetch(`search_ajax.php?q=${encodeURIComponent(query)}`)
                    .then(res => res.text())
                    .then(data => {
                        resultsBox.innerHTML = data;
                        resultsBox.style.display = 'block';
                    });
            } else {
                resultsBox.style.display = 'none';
            }
        });
    }

    // Fermer si on clique ailleurs
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dg-search-wrapper') && resultsBox) {
            resultsBox.style.display = 'none';
        }
    });
});
</script>