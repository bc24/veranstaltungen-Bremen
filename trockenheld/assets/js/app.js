(function () {
    'use strict';

    // --- Bestätigung vor "Ich habe getrunken" -----------------------------
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // --- Streak-Zahl von 0 hochzählen lassen ------------------------------
    document.querySelectorAll('[data-count-up]').forEach(function (el) {
        var ziel = parseInt(el.textContent, 10);
        if (isNaN(ziel) || ziel <= 0) {
            return;
        }
        var start = 0;
        var dauer = Math.min(1200, 200 + ziel * 25);
        var beginn = null;

        function schritt(zeitpunkt) {
            if (!beginn) beginn = zeitpunkt;
            var fortschritt = Math.min(1, (zeitpunkt - beginn) / dauer);
            var wert = Math.round(start + (ziel - start) * fortschritt);
            el.textContent = wert;
            if (fortschritt < 1) {
                window.requestAnimationFrame(schritt);
            }
        }
        el.textContent = '0';
        window.requestAnimationFrame(schritt);
    });

    // --- Konfetti bei Erfolgs-Meldung (Meilenstein / Level-Up) ------------
    var feiernAusloeser = document.querySelectorAll('.flash-erfolg, .flash-info');
    var sollFeiern = false;
    feiernAusloeser.forEach(function (el) {
        if (/Meilenstein|Level-Up/.test(el.textContent)) {
            sollFeiern = true;
        }
    });
    if (sollFeiern) {
        konfetti();
    }

    function konfetti() {
        var canvas = document.createElement('canvas');
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none';
        canvas.style.zIndex = '9999';
        document.body.appendChild(canvas);
        var ctx = canvas.getContext('2d');
        var breite = canvas.width = window.innerWidth;
        var hoehe = canvas.height = window.innerHeight;

        var farben = ['#0f9d78', '#f2a541', '#2f8fd6', '#d9534f', '#a24fd6'];
        var teilchen = [];
        for (var i = 0; i < 120; i++) {
            teilchen.push({
                x: Math.random() * breite,
                y: -20 - Math.random() * breite * 0.3,
                groesse: 4 + Math.random() * 6,
                farbe: farben[Math.floor(Math.random() * farben.length)],
                vy: 2 + Math.random() * 3,
                vx: -2 + Math.random() * 4,
                rotation: Math.random() * 360,
                vr: -6 + Math.random() * 12,
            });
        }

        var start = null;
        function frame(zeitpunkt) {
            if (!start) start = zeitpunkt;
            var vergangen = zeitpunkt - start;
            ctx.clearRect(0, 0, breite, hoehe);
            teilchen.forEach(function (t) {
                t.x += t.vx;
                t.y += t.vy;
                t.rotation += t.vr;
                ctx.save();
                ctx.translate(t.x, t.y);
                ctx.rotate(t.rotation * Math.PI / 180);
                ctx.fillStyle = t.farbe;
                ctx.fillRect(-t.groesse / 2, -t.groesse / 2, t.groesse, t.groesse * 0.6);
                ctx.restore();
            });
            if (vergangen < 2600) {
                window.requestAnimationFrame(frame);
            } else {
                document.body.removeChild(canvas);
            }
        }
        window.requestAnimationFrame(frame);
    }
})();
