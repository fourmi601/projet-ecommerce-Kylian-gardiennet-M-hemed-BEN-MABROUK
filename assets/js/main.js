// TOGGLE CLAIR / SOMBRE
const btnToggle = document.getElementById('theme-toggle');
const body = document.body;

// save s ou c
if (localStorage.getItem('theme') === 'light') {
    body.classList.add('light-theme');
    if (btnToggle) btnToggle.textContent = 'Mode Sombre';
}

if (btnToggle) {
    btnToggle.addEventListener('click', () => {
        body.classList.toggle('light-theme');

        if (body.classList.contains('light-theme')) {
            localStorage.setItem('theme', 'light');
            btnToggle.textContent = 'Mode Sombre';
        } else {
            localStorage.setItem('theme', 'dark');
            btnToggle.textContent = 'Mode Clair';
        }
    });
}

