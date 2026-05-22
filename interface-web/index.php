<?php include 'bdd.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maison Connectée — ESP32</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
        <li><a href="index.php" class="active">Dashboard</a></li>
        <li><a href="historique.php"><i class="bi bi-clock-history"></i> Historique</a></li>
    </ul>
</nav>

<div id="overlay" onclick="toggleMenu()"></div>

<div class="grid">

    <div class="carte">
        <div class="icon"><i class="bi bi-thermometer-half"></i></div>
        <h2>Température</h2>
        <span class="valeur" id="temperature"><?= $derniere['temperature'] ?></span>
        <span class="unite">°C</span>
    </div>

    <div class="carte">
        <div class="icon"><i class="bi bi-droplet-half"></i></div>
        <h2>Humidité</h2>
        <span class="valeur" id="humidite"><?= $derniere['humidite'] ?></span>
        <span class="unite">%</span>
    </div>

    <div class="carte <?= $derniere['obstacle'] == 1 ? 'alerte-on' : 'ok' ?>" id="carte-presence">
        <div class="icon" id="presence-icon">
            <i class="bi <?= $derniere['obstacle'] == 1 ? 'bi-lightbulb-fill' : 'bi-lightbulb-off-fill' ?>"></i>
        </div>
        <h2>Lumière / Présence</h2>
        <div id="presence-badge" class="badge <?= $derniere['obstacle'] == 1 ? 'danger' : 'safe' ?>">
            <?= $derniere['obstacle'] == 1 ? 'Présence détectée' : 'Aucune présence' ?>
        </div>
    </div>

    <div class="carte <?= $derniere['flamme'] == 1 ? 'alerte-on' : 'ok' ?>" id="carte-flamme">
        <div class="icon" id="flamme-icon">
            <i class="bi <?= $derniere['flamme'] == 1 ? 'bi-fire' : 'bi-check-square-fill' ?>"></i>
        </div>
        <h2>Flamme</h2>
        <div id="flamme-badge" class="badge <?= $derniere['flamme'] == 1 ? 'danger' : 'safe' ?>">
            <?= $derniere['flamme'] == 1 ? '⚠️ Flamme détectée' : 'Aucune flamme' ?>
        </div>
    </div>

</div>

<p class="derniere-maj">Dernière mise à jour : <span id="timestamp"><?= $derniere['timestamp'] ?></span></p>

<div id="popup-flamme" style="display:none;">
    <div id="popup-content">
        <div id="popup-icon"><i class="bi bi-fire"></i></div>
        <h2>ALERTE FLAMME !</h2>
        <p>Une flamme a été détectée. Vérifiez immédiatement.</p>
        <button onclick="stopperAlarme()"><i class="bi bi-check-square-fill"></i> OK — Alarme comprise</button>
    </div>
</div>

<script>
    let alarmeActive = false;
    let alarmeInterval = null;
    let audioCtx = null;

    function flammeIgnoree() {
        return sessionStorage.getItem('flammeIgnoree') === 'true';
    }

    function mettreAJour() {
        fetch('data.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('temperature').textContent = data.temperature;
                document.getElementById('humidite').textContent = data.humidite;
                document.getElementById('timestamp').textContent = data.timestamp;

                const presenceBadge = document.getElementById('presence-badge');
                const presenceIcon = document.getElementById('presence-icon');
                const cartePresence = document.getElementById('carte-presence');
                if (data.obstacle == 1) {
                    presenceBadge.textContent = 'Présence détectée';
                    presenceBadge.className = 'badge danger';
                    presenceIcon.innerHTML = '<i class="bi bi-lightbulb-fill"></i>';
                    cartePresence.className = 'carte alerte-on';
                } else {
                    presenceBadge.textContent = 'Aucune présence';
                    presenceBadge.className = 'badge safe';
                    presenceIcon.innerHTML = '<i class="bi bi-lightbulb-off-fill"></i>';
                    cartePresence.className = 'carte ok';
                }

                const flammeBadge = document.getElementById('flamme-badge');
                const flammeIcon = document.getElementById('flamme-icon');
                const carteFlamme = document.getElementById('carte-flamme');
                if (data.flamme == 1) {
                    flammeBadge.textContent = '⚠️ Flamme détectée';
                    flammeBadge.className = 'badge danger';
                    flammeIcon.innerHTML = '<i class="bi bi-fire"></i>';
                    carteFlamme.className = 'carte alerte-on';
                    if (!alarmeActive && !flammeIgnoree()) {
                        jouerAlerteFlame();
                    }
                } else {
                    flammeBadge.textContent = 'Aucune flamme';
                    flammeBadge.className = 'badge safe';
                    flammeIcon.innerHTML = '<i class="bi bi-check-square-fill"></i>';
                    carteFlamme.className = 'carte ok';
                    sessionStorage.removeItem('flammeIgnoree');
                }
            });
    }

    mettreAJour();
    setInterval(mettreAJour, 1000);

    function toggleMenu() {
        document.getElementById('nav-links').classList.toggle('open');
        const overlay = document.getElementById('overlay');
        overlay.style.display = overlay.style.display === 'block' ? 'none' : 'block';
    }

    function jouerAlerteFlame() {
        alarmeActive = true;
        document.getElementById('popup-flamme').style.display = 'flex';

        audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        function bip() {
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.type = 'square';
            oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
            gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.4);
            oscillator.start(audioCtx.currentTime);
            oscillator.stop(audioCtx.currentTime + 0.4);
        }

        bip();
        alarmeInterval = setInterval(bip, 600);
    }

    function stopperAlarme() {
        clearInterval(alarmeInterval);
        audioCtx.close();
        alarmeActive = false;
        sessionStorage.setItem('flammeIgnoree', 'true');
        document.getElementById('popup-flamme').style.display = 'none';
    }
</script>
</body>
</html>