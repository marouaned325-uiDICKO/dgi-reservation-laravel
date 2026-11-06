@if (\App\Support\Notif::estBandeau(session('success')))
    <div class="bandeau-succes" id="bandeauSucces" role="status" aria-live="polite">
        <span id="bandeauTexte">{{ session('success') }}</span>
        <button type="button" aria-label="Fermer" onclick="fermerBandeau()">&times;</button>
    </div>

    <script>
        function fermerBandeau() {
            const b = document.getElementById('bandeauSucces');
            if (!b) return;
            b.classList.add('masque');
            setTimeout(function () { b.remove(); }, 400);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const bandeau = document.getElementById('bandeauSucces');
            const message = document.getElementById('bandeauTexte').textContent.trim();

            // Masque les anciens bandeaux de succès affichés dans les pages (évite le doublon)
            document.querySelectorAll('body *').forEach(function (el) {
                if (bandeau.contains(el) || el.children.length > 0) return;
                if (el.textContent.trim() === message) {
                    const conteneur = el.closest('.alerte, .alerte-succes, .message, .alert, .alert-success, .success');
                    (conteneur || el).style.display = 'none';
                }
            });

            setTimeout(fermerBandeau, 6000);
        });
    </script>
@endif