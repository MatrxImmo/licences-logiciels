// ============================================================
// app.js — Fonctions globales pour l'interface
// ============================================================

// --- Onglets Connexion/Inscription ---
function showTab(tab) {
    // Cacher tous les formulaires
    document.querySelectorAll('.auth-form').forEach(form => {
        form.classList.remove('active-form');
    });
    // Enlever la classe active de tous les onglets
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    // Afficher le bon formulaire
    document.getElementById(tab + '-form').classList.add('active-form');
    // Activer le bon onglet
    document.querySelector(`.tab-btn[onclick="showTab('${tab}')"]`).classList.add('active');
}

// --- Recherche dans le catalogue ---
function filterCatalog() {
    const input = document.getElementById('search-bar');
    const filter = input.value.toLowerCase();
    const cards = document.querySelectorAll('.software-card');

    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const desc = card.querySelector('.card-desc').textContent.toLowerCase();
        if (title.includes(filter) || desc.includes(filter)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// --- Activation : message retour (pour activation.html) ---
document.addEventListener('DOMContentLoaded', function() {
    const activationForm = document.getElementById('activation-form');
    if (activationForm) {
        activationForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const messageDiv = document.getElementById('activation-message');

            fetch('php/activer_licence.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageDiv.className = 'activation-message success';
                    messageDiv.textContent = data.message;
                    // Optionnel : recharger la page après 2 secondes
                    setTimeout(() => {
                        window.location.href = 'mes-licences.html';
                    }, 2000);
                } else {
                    messageDiv.className = 'activation-message error';
                    messageDiv.textContent = data.message;
                }
            })
            .catch(error => {
                messageDiv.className = 'activation-message error';
                messageDiv.textContent = 'Erreur technique. Veuillez réessayer.';
            });
        });
    }
});

// --- Pré-remplir la clé dans activation.html depuis l'URL ---
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const cle = params.get('cle');
    if (cle) {
        const inputCle = document.getElementById('license-key');
        if (inputCle) {
            inputCle.value = cle;
        }
    }
});