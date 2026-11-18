{{-- Listes déroulantes avec recherche : Direction et Service --}}
<style>
    .form-row > .form-group { min-width: 0; }
    @media (min-width: 701px) { .form-row.trois { grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr) minmax(0, 1fr); } }
    #ligneDirectionService { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    @media (max-width: 600px) { #ligneDirectionService { grid-template-columns: minmax(0, 1fr); } }
    #ligneDirectionService .form-group { min-width: 0; }
    .ss { position: relative; min-width: 0; max-width: 100%; }
    .ss > select { position: absolute; left: 0; bottom: 0; width: 100%; height: 1px; opacity: 0; pointer-events: none; }
    .ss-btn { width: 100%; min-width: 0; max-width: 100%; padding: 11px 13px; border: 1.3px solid #ddd; border-radius: 9px; font-size: 14px; background: #fafafa; font-family: inherit; text-align: left; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 8px; color: #222; }
    .ss-btn span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ss-btn::after { content: ''; flex: none; border: solid #777; border-width: 0 2px 2px 0; padding: 3px; transform: rotate(45deg); margin-top: -3px; }
    .ss-btn:focus, .ss.ouvert .ss-btn { outline: none; border-color: #1A8969; background: white; box-shadow: 0 0 0 3px rgba(26,137,105,.12); }
    .ss.desactive .ss-btn { background: #f0f2f1; color: #888; cursor: not-allowed; }
    .ss-vide span { color: #777; }
    .ss-panel { display: none; position: absolute; z-index: 50; left: 0; right: 0; top: calc(100% + 4px); background: white; border: 1.3px solid #cfd8d3; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.12); padding: 8px; }
    .ss.ouvert .ss-panel { display: block; }
    .ss-recherche { width: 100%; padding: 9px 11px; border: 1.3px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; margin-bottom: 6px; }
    .ss-liste { list-style: none; margin: 0; padding: 0; max-height: 240px; overflow-y: auto; }
    .ss-liste li { overflow-wrap: anywhere; padding: 8px 10px; font-size: 13px; border-radius: 6px; cursor: pointer; line-height: 1.35; }
    .ss-liste li:hover, .ss-liste li.actif { background: #e8f5ef; }
    .ss-liste li.choisi { font-weight: 700; color: #146e54; }
    .ss-aucun { padding: 10px; font-size: 13px; color: #999; text-align: center; }
</style>

<script>
(function () {
    function sansAccent(t) {
        return (t || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }

    function rendreRecherchable(select) {
        if (!select || select.dataset.recherche) return;
        select.dataset.recherche = '1';

        const boite = document.createElement('div');
        boite.className = 'ss';
        select.parentNode.insertBefore(boite, select);
        boite.appendChild(select);
        select.tabIndex = -1;

        const bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.className = 'ss-btn';
        const libelle = document.createElement('span');
        bouton.appendChild(libelle);

        const panneau = document.createElement('div');
        panneau.className = 'ss-panel';
        const champ = document.createElement('input');
        champ.type = 'text';
        champ.className = 'ss-recherche';
        champ.placeholder = 'Rechercher...';
        champ.autocomplete = 'off';
        const liste = document.createElement('ul');
        liste.className = 'ss-liste';
        panneau.append(champ, liste);
        boite.append(bouton, panneau);

        let actif = -1;

        function rafraichirLibelle() {
            const opt = select.options[select.selectedIndex];
            const vide = !opt || opt.value === '';
            libelle.textContent = opt ? opt.textContent.trim() : '-- Choisir --';
            bouton.classList.toggle('ss-vide', vide);
            boite.classList.toggle('desactive', select.disabled);
        }

        function remplirListe() {
            const terme = sansAccent(champ.value.trim());
            liste.innerHTML = '';
            actif = -1;
            let n = 0;
            Array.from(select.options).forEach(function (opt) {
                if (opt.value === '') return;
                if (terme && sansAccent(opt.textContent).indexOf(terme) === -1) return;
                const li = document.createElement('li');
                li.textContent = opt.textContent.trim();
                li.dataset.valeur = opt.value;
                if (opt.selected) li.classList.add('choisi');
                li.addEventListener('mousedown', function (e) { e.preventDefault(); choisir(opt.value); });
                liste.appendChild(li);
                n++;
            });
            if (n === 0) {
                const aucun = document.createElement('div');
                aucun.className = 'ss-aucun';
                aucun.textContent = 'Aucun résultat';
                liste.appendChild(aucun);
            }
        }

        function surligner(i) {
            const items = liste.querySelectorAll('li');
            if (!items.length) return;
            actif = (i + items.length) % items.length;
            items.forEach(function (li, k) { li.classList.toggle('actif', k === actif); });
            items[actif].scrollIntoView({ block: 'nearest' });
        }

        function ouvrir() {
            if (select.disabled) return;
            champ.value = '';
            remplirListe();
            boite.classList.add('ouvert');
            champ.focus();
            const choisi = liste.querySelector('li.choisi');
            if (choisi) choisi.scrollIntoView({ block: 'nearest' });
        }

        function fermer() { boite.classList.remove('ouvert'); }

        function choisir(valeur) {
            select.value = valeur;
            rafraichirLibelle();
            fermer();
            select.dispatchEvent(new Event('change', { bubbles: true }));
            bouton.focus();
        }

        bouton.addEventListener('click', function () {
            boite.classList.contains('ouvert') ? fermer() : ouvrir();
        });
        champ.addEventListener('input', remplirListe);
        champ.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); surligner(actif + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); surligner(actif - 1); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                const items = liste.querySelectorAll('li');
                const cible = items[actif >= 0 ? actif : 0];
                if (cible) choisir(cible.dataset.valeur);
            }
            else if (e.key === 'Escape') { fermer(); bouton.focus(); }
        });
        document.addEventListener('mousedown', function (e) { if (!boite.contains(e.target)) fermer(); });

        // Le script de la page reconstruit la liste des services : on suit ces changements
        new MutationObserver(rafraichirLibelle).observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });
        select.addEventListener('change', rafraichirLibelle);

        rafraichirLibelle();
    }

    document.addEventListener('DOMContentLoaded', function () {
        rendreRecherchable(document.getElementById('direction_id'));
        rendreRecherchable(document.getElementById('service_id'));
        rendreRecherchable(document.getElementById('localite'));
    });
})();
</script>