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

// panier a jour
function getCart() {
    return JSON.parse(localStorage.getItem('cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function updateCartDisplay() {
    const cart = getCart();
    const total = cart.reduce((sum, item) => sum + item.qty, 0);
    const cartBtns = document.querySelectorAll('.cart-btn');
    cartBtns.forEach(btn => {
        btn.textContent = `🛒 Panier (${total})`;
    });
}

document.querySelectorAll('.btn-add').forEach(btn => {
    btn.addEventListener('click', () => {
        const card = btn.closest('.game-card');
        const titre = card.querySelector('h3')?.textContent || 'Jeu';
        const prix = card.querySelector('.price')?.textContent || '0 €';

        let cart = getCart();
        const existant = cart.find(item => item.titre === titre);

        if (existant) {
            existant.qty++;
        } else {
            cart.push({ titre, prix, qty: 1 });
        }

        saveCart(cart);
        updateCartDisplay();

        btn.textContent = '✓ Ajouté';
        btn.style.background = 'linear-gradient(135deg, #00a651, #00cc66)';
        setTimeout(() => {
            btn.textContent = 'Ajouter';
            btn.style.background = '';
        }, 1200);
    });
});

updateCartDisplay();