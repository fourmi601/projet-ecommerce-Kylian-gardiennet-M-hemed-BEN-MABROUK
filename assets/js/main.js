// Applique le thème avant que la page s'affiche (anti-flash).
// Le toggle lui-même est géré dans navbar.php pour éviter les doublons.
(function () {
    var saved       = localStorage.getItem('theme');
    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    var useLight    = saved ? saved === 'light' : !prefersDark;

    if (useLight) {
        document.documentElement.classList.add('light-theme');
        // body pas encore dispo ici, on le fait dès qu'il existe
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.add('light-theme');
        });
    }
})();
