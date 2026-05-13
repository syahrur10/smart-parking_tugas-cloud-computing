<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Aktivitas – Smart Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=Outfit:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --accent:  #3b82f6;
            --ok:      #10b981;
            --surface: rgba(255,255,255,0.04);
            --border:  rgba(255,255,255,0.08);
            --muted:   rgba(255,255,255,0.45);
            --body:    'DM Sans', sans-serif;
            --display: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            background: url('assets/images/parking-bg.jpg') center/cover no-repeat fixed;
            color: #fff;
            font-family: var(--body);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 0%, rgba(59,130,246,0.10) 0%, transparent 60%),
                linear-gradient(160deg, rgba(2,5,18,0.96) 0%, rgba(5,10,28,0.93) 100%);
            z-index: 0;
        }

        .wrap {
            position: relative;
            z-index: 1;
            width: 92%;
            max-width: 900px;
            margin: 0 auto;
            padding: 44px 0 80px;
        }

        /* ── HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .btn-back {
            padding: 10px 18px;
            border-radius: 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            color: rgba(255,255,255,0.75);
            text-decoration: none;
            font-family: var(--body);
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
            backdrop-filter: blur(14px);
            white-space: nowrap;
        }

        .btn-back:hover { background: rgba(255,255,255,0.075); color: #fff; }

        .page-title-wrap {}

        .page-eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .page-title {
            font-family: var(--display);
            font-size: clamp(28px, 4vw, 44px);
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .page-sub {
            color: var(--muted);
            font-size: 13px;
            margin-top: 8px;
        }

        /* ── STAT BAR ── */
        .stat-bar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            backdrop-filter: blur(18px);
        }

        .stat-bar-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--ok);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.2);
            animation: pulse 2.4s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 3px rgba(16,185,129,0.2); }
            50%       { box-shadow: 0 0 0 7px rgba(16,185,129,0.06); }
        }

        .stat-bar-text {
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-bar-count {
            margin-left: auto;
            font-family: var(--display);
            font-size: 18px;
            font-weight: 700;
        }

        /* ── LIST ── */
        .notif-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .notif-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 3px solid var(--ok);
            border-radius: 18px;
            padding: 20px 24px;
            backdrop-filter: blur(16px);
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 12px;
            transition: transform 0.25s, box-shadow 0.25s;
            animation: fadeIn 0.35s ease both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .notif-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.35);
        }

        .notif-plat {
            font-family: var(--display);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .notif-message {
            font-size: 12.5px;
            color: var(--muted);
            line-height: 1.5;
        }

        .notif-time {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
            text-align: right;
            white-space: nowrap;
            font-weight: 500;
        }

        /* ── STATES ── */
        .state-box {
            text-align: center;
            padding: 70px 20px;
            color: var(--muted);
        }

        .state-box .state-label {
            font-size: 14px;
            font-weight: 500;
            margin-top: 12px;
        }

        .spinner {
            width: 36px;
            height: 36px;
            border: 3px solid rgba(255,255,255,0.08);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── FOOTER ── */
        .footer {
            margin-top: 48px;
            text-align: center;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.15);
        }
    </style>
</head>
<body>

<div class="wrap">

    <div class="page-header">
        <a href="dashboard.php" class="btn-back">← Kembali ke Dashboard</a>
        <div class="page-title-wrap">
            <div class="page-eyebrow">Aktivitas Parkir</div>
            <h1 class="page-title">Riwayat Kendaraan</h1>
            <p class="page-sub">Semua aktivitas masuk kendaraan ke area parkir.</p>
        </div>
    </div>

    <div class="stat-bar">
        <div class="stat-bar-dot"></div>
        <div class="stat-bar-text">Live — update setiap 5 detik</div>
        <div class="stat-bar-count" id="totalCount">—</div>
    </div>

    <div class="notif-list" id="notifList">
        <div class="state-box">
            <div class="spinner"></div>
            <div class="state-label">Memuat data...</div>
        </div>
    </div>

    <div class="footer">Smart Parking &nbsp;•&nbsp; Realtime Activity Log</div>

</div>

<script>
    let lastCount = -1;

    async function loadNotifications() {
        try {
            const res    = await fetch('/backend/api/get_notifications.php');
            const result = await res.json();

            if (result.status !== 'success') {
                document.getElementById('notifList').innerHTML = `
                    <div class="state-box">
                        <div class="state-label">Gagal memuat data: ${result.message || 'Unknown error'}</div>
                    </div>`;
                return;
            }

            const data = result.data;

            // Update counter
            document.getElementById('totalCount').textContent =
                data.length + ' entri';

            // Hanya render ulang kalau jumlah berubah (hindari flicker)
            if (data.length === lastCount) return;
            lastCount = data.length;

            if (data.length === 0) {
                document.getElementById('notifList').innerHTML = `
                    <div class="state-box">
                        <div class="state-label">Belum ada aktivitas kendaraan tercatat.</div>
                    </div>`;
                return;
            }

            let html = '';
            data.forEach((notif, i) => {
                // Format waktu lebih rapi
                const d    = new Date(notif.time);
                const time = isNaN(d)
                    ? notif.time
                    : d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
                      + ' ' + d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });

                html += `
                    <div class="notif-card" style="animation-delay:${i * 0.04}s">
                        <div>
                            <div class="notif-plat">${notif.plate_number}</div>
                            <div class="notif-message">${notif.message}</div>
                        </div>
                        <div class="notif-time">${time}</div>
                    </div>`;
            });

            document.getElementById('notifList').innerHTML = html;

        } catch (err) {
            document.getElementById('notifList').innerHTML = `
                <div class="state-box">
                    <div class="state-label">Koneksi ke server gagal.</div>
                </div>`;
        }
    }

    loadNotifications();
    setInterval(loadNotifications, 5000);
</script>

</body>
</html>