<?php include 'bdd.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique — Maison Connectée</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
</head>
<body>

<nav>
    <div class="nav-logo">
        <i class="bi bi-house"></i>
        <div>
            <h1>Maison Connectée</h1>
            <p>ESP32 — Surveillance en temps réel</p>
        </div>
    </div>

    <button class="burger" onclick="toggleMenu()">☰</button>

    <ul class="nav-links" id="nav-links">
        <li><a href="index.php">Dashboard</a></li>
        <li><a href="historique.php" class="active"><i class="bi bi-clock-history"></i> Historique</a></li>
    </ul>
</nav>

<div id="overlay" onclick="toggleMenu()"></div>

<button class="filtre-btn btn-export" onclick="telechargerExcel()">
    <i class="bi bi-download"></i> Télécharger Excel
</button>

<div class="historique-container">
    <h2><i class="bi bi-clock-history"></i> Historique des données</h2>

    <div class="graphes-grid">
        <div class="carte-historique">
            <h3><i class="bi bi-thermometer-half"></i> Température</h3>
            <canvas id="graphTemp"></canvas>
        </div>
        <div class="carte-historique">
            <h3><i class="bi bi-droplet-half"></i> Humidité</h3>
            <canvas id="graphHum"></canvas>
        </div>
    </div>

    <div class="carte-historique section-historique">
        <h3><i class="bi bi-lightbulb-fill"></i> Historique présence</h3>
        <div class="filtres">
            <button class="filtre-btn active" onclick="filtrerPresence('tout', this)">20 dernières</button>
            <button class="filtre-btn" onclick="filtrerPresence('presence', this)">Présence</button>
            <button class="filtre-btn" onclick="filtrerPresence('suspect', this)">Présence suspecte</button>
        </div>
        <table>
            <thead>
            <tr>
                <th>Date / Heure</th>
                <th>Statut</th>
            </tr>
            </thead>
            <tbody id="tbody-presence"></tbody>
        </table>
    </div>

    <div class="carte-historique section-historique">
        <h3><i class="bi bi-fire"></i> Alertes flamme</h3>
        <div id="liste-flammes"><p class="aucune-alerte">Aucune alerte détectée ✅</p></div>
    </div>
</div>

<script>
    let filtrePresence = 'tout';
    let toutesLignesTout = [];
    let toutesLignesPresence = [];
    let chartTemp = null;
    let chartHum = null;

    function filtrerPresence(filtre, btn) {
        filtrePresence = filtre;
        document.querySelectorAll('.filtres')[0].querySelectorAll('.filtre-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        afficherPresence();
    }

    function afficherPresence() {
        const tbody = document.getElementById('tbody-presence');
        tbody.innerHTML = '';

        const lignes = filtrePresence === 'tout' ? toutesLignesTout : toutesLignesPresence;

        lignes.forEach(row => {
            const heure = parseInt(row.timestamp.split(' ')[1].split(':')[0]);
            const estSuspect = row.obstacle == 1 && (heure >= 0 && heure < 6);
            const estPresence = row.obstacle == 1 && !estSuspect;

            if (filtrePresence === 'presence' && !estPresence) return;
            if (filtrePresence === 'suspect' && !estSuspect) return;

            let statut = '';
            if (estSuspect) {
                statut = '<span class="badge danger">⚠️ Présence suspecte</span>';
            } else if (estPresence) {
                statut = '<span class="badge warning">Présence</span>';
            } else {
                statut = '<span class="badge safe">Rien à signaler</span>';
            }

            tbody.innerHTML += `
                <tr>
                    <td>${row.timestamp}</td>
                    <td>${statut}</td>
                </tr>`;
        });
    }

    function telechargerExcel() {
        fetch('export_data.php')
            .then(response => response.json())
            .then(data => {
                const wb = XLSX.utils.book_new();

                const parJour = {};
                data.forEach(row => {
                    const jour = row.timestamp.split(' ')[0];
                    if (!parJour[jour]) parJour[jour] = [];
                    parJour[jour].push(row);
                });

                Object.keys(parJour).sort().forEach(jour => {
                    const rows = parJour[jour];
                    const ws = {};

                    // === COLONNE A-C : Température & Humidité ===
                    ws['A1'] = { v: 'TEMPÉRATURE & HUMIDITÉ', t: 's' };
                    ws['A2'] = { v: 'Heure', t: 's' };
                    ws['B2'] = { v: 'Température (°C)', t: 's' };
                    ws['C2'] = { v: 'Humidité (%)', t: 's' };
                    rows.forEach((r, i) => {
                        ws[`A${i+3}`] = { v: r.timestamp.split(' ')[1], t: 's' };
                        ws[`B${i+3}`] = { v: parseFloat(r.temperature), t: 'n' };
                        ws[`C${i+3}`] = { v: parseFloat(r.humidite), t: 'n' };
                    });

                    // === COLONNE E-F : Présence ===
                    ws['E1'] = { v: 'PRÉSENCE', t: 's' };
                    ws['E2'] = { v: 'Heure', t: 's' };
                    ws['F2'] = { v: 'Statut', t: 's' };
                    const presRows = rows.filter(r => r.obstacle == 1);
                    if (presRows.length === 0) {
                        ws['E3'] = { v: 'Rien à signaler', t: 's' };
                    } else {
                        presRows.forEach((r, i) => {
                            const heure = parseInt(r.timestamp.split(' ')[1].split(':')[0]);
                            ws[`E${i+3}`] = { v: r.timestamp.split(' ')[1], t: 's' };
                            ws[`F${i+3}`] = { v: heure >= 0 && heure < 6 ? '⚠️ Présence suspecte' : 'Présence détectée', t: 's' };
                        });
                    }

                    // === COLONNE H-I : Flammes ===
                    ws['H1'] = { v: 'ALERTES FLAMME', t: 's' };
                    ws['H2'] = { v: 'Heure', t: 's' };
                    ws['I2'] = { v: 'Statut', t: 's' };
                    const flamRows = rows.filter(r => r.flamme == 1);
                    if (flamRows.length === 0) {
                        ws['H3'] = { v: 'Aucune alerte ce jour', t: 's' };
                    } else {
                        flamRows.forEach((r, i) => {
                            ws[`H${i+3}`] = { v: r.timestamp.split(' ')[1], t: 's' };
                            ws[`I${i+3}`] = { v: '🔥 Flamme détectée', t: 's' };
                        });
                    }

                    // Définir la plage de la feuille
                    const maxRows = Math.max(rows.length, presRows.length, flamRows.length) + 3;
                    ws['!ref'] = `A1:I${maxRows}`;

                    XLSX.utils.book_append_sheet(wb, ws, jour);
                });

                XLSX.writeFile(wb, 'maison_connectee_' + new Date().toISOString().split('T')[0] + '.xlsx');
            });
    }

    function mettreAJourHistorique() {
        fetch('data.php')
            .then(response => response.json())
            .then(data => {
                const rows = data.historique;
                toutesLignesTout = rows;
                toutesLignesPresence = data.presence;

                const labels = rows.map(r => r.timestamp.split(' ')[1]).reverse();
                const temps = rows.map(r => r.temperature).reverse();
                const hums = rows.map(r => r.humidite).reverse();

                if (chartTemp) chartTemp.destroy();
                chartTemp = new Chart(document.getElementById('graphTemp'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Température °C',
                            data: temps,
                            borderColor: '#f85149',
                            backgroundColor: 'rgba(248,81,73,0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2
                        }]
                    },
                    options: {
                        plugins: { legend: { labels: { color: 'white' } } },
                        scales: {
                            x: { ticks: { color: 'rgba(255,255,255,0.4)', maxTicksLimit: 8 }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            y: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                        }
                    }
                });

                if (chartHum) chartHum.destroy();
                chartHum = new Chart(document.getElementById('graphHum'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Humidité %',
                            data: hums,
                            borderColor: '#00d4ff',
                            backgroundColor: 'rgba(0,212,255,0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2
                        }]
                    },
                    options: {
                        plugins: { legend: { labels: { color: 'white' } } },
                        scales: {
                            x: { ticks: { color: 'rgba(255,255,255,0.4)', maxTicksLimit: 8 }, grid: { color: 'rgba(255,255,255,0.05)' } },
                            y: { ticks: { color: 'rgba(255,255,255,0.4)' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                        }
                    }
                });

                afficherPresence();

                const listeFlammes = document.getElementById('liste-flammes');
                const flammes = data.flammes;
                if (flammes.length === 0) {
                    listeFlammes.innerHTML = '<p class="aucune-alerte">Aucune alerte détectée ✅</p>';
                } else {
                    listeFlammes.innerHTML = flammes.map(r => `
                        <div class="flamme-item">
                            <i class="bi bi-fire"></i> ${r.timestamp}
                        </div>`).join('');
                }
            });
    }

    function toggleMenu() {
        document.getElementById('nav-links').classList.toggle('open');
        const overlay = document.getElementById('overlay');
        overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
    }

    mettreAJourHistorique();
    setInterval(mettreAJourHistorique, 3000);
</script>

</body>
</html>