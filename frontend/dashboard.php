<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

require '../backend/config/database.php';

$user = $_SESSION['user'];

// Total kendaraan tetap dari MySQL
$totalVehicles = mysqli_num_rows(
    mysqli_query($conn, "SELECT id FROM vehicle_uploads")
);

// Foto profil
$photoUrl = null;
if (!empty($user['photo'])) {
    if (str_starts_with($user['photo'], 'profiles/')) {
        $photoUrl = '/backend/api/proxy_image.php?file=' . urlencode($user['photo']);
    } else {
        $photoUrl = $user['photo'];
    }
}

$firstName = explode(' ', $user['nama'])[0];
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Selamat pagi' : ($hour < 17 ? 'Selamat siang' : 'Selamat malam');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Smart Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0; padding: 0; box-sizing: border-box;
        }

        :root {
            --accent:    #3b82f6;
            --accent2:   #06b6d4;
            --ok:        #10b981;
            --warn:      #f59e0b;
            --danger:    #ef4444;
            --surface:   rgba(255,255,255,0.04);
            --surface-h: rgba(255,255,255,0.075);
            --border:    rgba(255,255,255,0.08);
            --muted:     rgba(255,255,255,0.45);
            --body:      'DM Sans', sans-serif;
            --display:   'Outfit', sans-serif;
        }

        html { scroll-behavior: smooth; }

        body {
            min-height: 100vh;
            background: url('assets/images/parking-bg.jpg') center/cover no-repeat fixed;
            color: #fff;
            font-family: var(--body);
            overflow-x: hidden;
        }

        /* overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 0%, rgba(59,130,246,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 100%, rgba(6,182,212,0.08) 0%, transparent 55%),
                linear-gradient(160deg, rgba(2,5,18,0.96) 0%, rgba(5,10,28,0.93) 100%);
            z-index: 0;
        }

        /* ─── LAYOUT ─── */
        .wrap {
            position: relative;
            z-index: 1;
            width: 92%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 44px 0 80px;
        }

        /* ─── HEADER ─── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 46px;
            flex-wrap: wrap;
        }

        .brand-eyebrow {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .brand-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.18);
            animation: pulse 2.4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 4px rgba(59,130,246,0.18); }
            50%       { box-shadow: 0 0 0 8px rgba(59,130,246,0.06); }
        }

        .brand-eyebrow span {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3.5px;
            text-transform: uppercase;
            color: var(--accent);
        }

        .brand-title {
            font-family: var(--display);
            font-size: clamp(36px, 4.5vw, 58px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.5px;
        }

        .brand-title em {
            font-style: normal;
            background: linear-gradient(90deg, var(--accent), var(--accent2));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-sub {
            margin-top: 14px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
            max-width: 480px;
        }

        /* user panel */
        .header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 12px;
            padding-top: 4px;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 12px 18px;
            backdrop-filter: blur(18px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            object-fit: cover;
            border: 1.5px solid rgba(59,130,246,0.5);
        }

        .user-avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(59,130,246,0.5), rgba(6,182,212,0.3));
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--display);
            font-weight: 700;
            font-size: 16px;
            border: 1.5px solid rgba(59,130,246,0.4);
        }

        .user-greeting { font-size: 11px; color: var(--muted); line-height: 1.2; }
        .user-name     { font-size: 14px; font-weight: 700; line-height: 1.3; }

        .btn-row { display: flex; gap: 8px; }

        .btn {
            padding: 9px 16px;
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
        }

        .btn:hover { background: var(--surface-h); color: #fff; }

        .btn-danger {
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.2);
            color: #fca5a5;
        }

        .btn-danger:hover {
            background: rgba(239,68,68,0.2);
            color: #fecaca;
        }

        /* ─── OCCUPANCY BAR ─── */
        .occ-strip {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px 26px;
            margin-bottom: 26px;
            display: flex;
            align-items: center;
            gap: 20px;
            backdrop-filter: blur(18px);
        }

        .occ-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }

        .occ-track {
            flex: 1;
            height: 6px;
            background: rgba(255,255,255,0.07);
            border-radius: 999px;
            overflow: hidden;
        }

        .occ-fill {
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--ok), var(--accent));
            width: 0%;
            transition: width 1.2s cubic-bezier(.23,1,.32,1);
        }

        .occ-pct {
            font-family: var(--display);
            font-size: 20px;
            font-weight: 700;
            min-width: 60px;
            text-align: right;
            white-space: nowrap;
        }

        /* ─── STATS ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 26px;
        }

        .stat {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            padding: 28px 30px;
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            transition: transform 0.3s;
        }

        .stat:hover { transform: translateY(-4px); }

        .stat-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .stat-val {
            font-family: var(--display);
            font-size: clamp(48px, 4.5vw, 64px);
            font-weight: 800;
            line-height: 1;
            letter-spacing: -1px;
            margin-bottom: 10px;
        }

        .stat-desc {
            color: var(--muted);
            font-size: 12.5px;
            line-height: 1.7;
        }

        .stat-blue   { background: linear-gradient(145deg, rgba(59,130,246,0.35), rgba(10,20,60,0.92)); }
        .stat-green  { background: linear-gradient(145deg, rgba(16,185,129,0.35), rgba(5,35,25,0.92)); }
        .stat-amber  { background: linear-gradient(145deg, rgba(245,158,11,0.35), rgba(50,32,5,0.92)); }

        /* shimmer for loading */
        .stat-val.loading {
            background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.04) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 6px;
            color: transparent;
            user-select: none;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ─── MENU ─── */
        .menu-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 36px;
            backdrop-filter: blur(18px);
        }

        .menu-head {
            margin-bottom: 28px;
        }

        .menu-head-eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .menu-head h2 {
            font-family: var(--display);
            font-size: clamp(26px, 2.8vw, 36px);
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }

        .menu-head p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 8px;
        }

        /* divider */
        .menu-divider {
            height: 1px;
            background: var(--border);
            margin-bottom: 28px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        .menu-card {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            padding: 26px 24px 22px;
            text-decoration: none;
            color: #fff;
            border: 1px solid var(--border);
            transition: 0.3s cubic-bezier(.23,1,.32,1);
            display: flex;
            flex-direction: column;
            min-height: 195px;
        }

        .menu-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }

        /* subtle corner highlight */
        .menu-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 15%, rgba(255,255,255,0.07), transparent 55%);
            pointer-events: none;
        }

        .card-tag {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 6px;
            padding: 3px 8px;
            margin-bottom: 16px;
            width: fit-content;
        }

        .menu-card h3 {
            font-family: var(--display);
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            line-height: 1.3;
            position: relative;
            z-index: 1;
        }

        .menu-card p {
            color: rgba(255,255,255,0.65);
            font-size: 12.5px;
            line-height: 1.75;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .card-cta {
            margin-top: 18px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: 0.25s;
            position: relative;
            z-index: 1;
        }

        .card-cta span { transition: transform 0.25s; }
        .menu-card:hover .card-cta { color: rgba(255,255,255,0.85); }
        .menu-card:hover .card-cta span { transform: translateX(4px); }

        /* card gradients – no icons */
        .c-blue   { background: linear-gradient(145deg, rgba(59,130,246,0.42), rgba(10,20,65,0.94)); }
        .c-violet { background: linear-gradient(145deg, rgba(139,92,246,0.42), rgba(35,15,70,0.94)); }
        .c-cyan   { background: linear-gradient(145deg, rgba(6,182,212,0.38), rgba(5,40,60,0.94)); }
        .c-rose   { background: linear-gradient(145deg, rgba(244,63,94,0.40), rgba(60,10,20,0.94)); }
        .c-green  { background: linear-gradient(145deg, rgba(16,185,129,0.40), rgba(5,35,25,0.94)); }
        .c-amber  { background: linear-gradient(145deg, rgba(245,158,11,0.38), rgba(55,30,5,0.94)); }

        /* ─── RESET BUTTON ─── */
        .reset-wrap {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }

        .btn-reset {
            padding: 10px 20px;
            border-radius: 12px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.25);
            color: #fca5a5;
            font-family: var(--body);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .btn-reset:hover {
            background: rgba(239,68,68,0.2);
            color: #fecaca;
            transform: translateY(-1px);
        }

        .btn-reset:active { transform: translateY(0); }

        /* ─── TOAST ─── */
        .toast {
            position: fixed;
            bottom: 32px;
            right: 32px;
            padding: 14px 22px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            backdrop-filter: blur(18px);
            border: 1px solid transparent;
            z-index: 9999;
            opacity: 0;
            transform: translateY(16px);
            transition: opacity 0.3s, transform 0.3s;
            pointer-events: none;
            max-width: 320px;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            background: rgba(16,185,129,0.18);
            border-color: rgba(16,185,129,0.35);
            color: #6ee7b7;
        }

        .toast.error {
            background: rgba(239,68,68,0.18);
            border-color: rgba(239,68,68,0.35);
            color: #fca5a5;
        }

        /* ─── FOOTER ─── */
        .footer {
            margin-top: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: rgba(255,255,255,0.2);
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .footer-dot { color: var(--accent); opacity: 0.5; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1100px) {
            .stats-row, .menu-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 680px) {
            .stats-row, .menu-grid { grid-template-columns: 1fr; }
            .header { flex-direction: column; }
            .header-right { align-items: flex-start; }
            .menu-box { padding: 20px; }
        }
    </style>
</head>
<body>

<div class="wrap">

    <!-- ── HEADER ── -->
    <div class="header">

        <div class="brand">
            <div class="brand-eyebrow">
                <div class="brand-dot"></div>
                <span>Smart Parking System</span>
            </div>
            <h1 class="brand-title">Selamat datang,<br><em><?= htmlspecialchars($firstName) ?></em></h1>
            <p class="brand-sub">
                Pantau semua aktivitas parkir, kelola kendaraan, dan reservasi slot
                dari satu tempat secara langsung dan real-time.
            </p>
        </div>

        <div class="header-right">
            <div class="user-card">
                <?php if ($photoUrl): ?>
                    <img src="<?= htmlspecialchars($photoUrl) ?>" class="user-avatar" alt="Foto">
                <?php else: ?>
                    <div class="user-avatar-placeholder">
                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="user-greeting"><?= $greeting ?></div>
                    <div class="user-name"><?= htmlspecialchars($user['nama']) ?></div>
                </div>
            </div>

            <div class="btn-row">
                <a href="profile.php" class="btn">Profil Saya</a>
                <a href="auth/logout.php" class="btn btn-danger">Keluar</a>
            </div>
        </div>

    </div>

    <!-- ── OCCUPANCY STRIP ── -->
    <div class="occ-strip">
        <div class="occ-label">Kepadatan Parkir</div>
        <div class="occ-track">
            <div class="occ-fill" id="parkingBar"></div>
        </div>
        <div class="occ-pct" id="parkingPercent">—</div>
    </div>

    <!-- ── STATS ── -->
    <div class="stats-row">

        <div class="stat stat-blue">
            <div class="stat-label">Total Kendaraan</div>
            <div class="stat-val"><?= $totalVehicles ?></div>
            <div class="stat-desc">Kendaraan tercatat di sistem cloud storage.</div>
        </div>

        <div class="stat stat-green">
            <div class="stat-label">Slot Terpakai</div>
            <div class="stat-val loading" id="occupiedCount">0</div>
            <div class="stat-desc">Slot yang sedang digunakan saat ini.</div>
        </div>

        <div class="stat stat-amber">
            <div class="stat-label">Slot Kosong</div>
            <div class="stat-val loading" id="availableCount">0</div>
            <div class="stat-desc">Slot yang masih tersedia untuk dipesan.</div>
        </div>

    </div>

    <!-- ── MENU ── -->
    <div class="menu-box">

        <div class="menu-head">
            <div class="menu-head-eyebrow">Navigasi Utama</div>
            <h2>Menu Fitur</h2>
            <p>Pilih modul yang ingin kamu gunakan.</p>
        </div>

        <div class="menu-divider"></div>

        <div class="reset-wrap">
            <button class="btn-reset" onclick="resetParking()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.5"/></svg>
                Reset Semua Data Parkir
            </button>
        </div>

        <div class="menu-grid">

            <a href="upload_test.php" class="menu-card c-blue">
                <div class="card-tag">Upload</div>
                <h3>Upload Kendaraan</h3>
                <p>Daftarkan kendaraan baru dengan foto dan nomor plat ke sistem cloud.</p>
                <div class="card-cta">Buka <span>→</span></div>
            </a>

            <a href="vehicles.php" class="menu-card c-violet">
                <div class="card-tag">Data</div>
                <h3>Data Kendaraan</h3>
                <p>Lihat semua kendaraan yang sudah terdaftar di dalam sistem.</p>
                <div class="card-cta">Buka <span>→</span></div>
            </a>

            <a href="parking_slots.php" class="menu-card c-cyan">
                <div class="card-tag">Real-time</div>
                <h3>Slot Parkir</h3>
                <p>Pantau kondisi setiap slot parkir secara langsung tanpa perlu refresh.</p>
                <div class="card-cta">Buka <span>→</span></div>
            </a>

            <a href="notifications.php" class="menu-card c-rose">
                <div class="card-tag">Notifikasi</div>
                <h3>Riwayat Aktivitas</h3>
                <p>Pantau semua aktivitas masuk dan keluar kendaraan dari area parkir.</p>
                <div class="card-cta">Buka <span>→</span></div>
            </a>

            <a href="reservation.php" class="menu-card c-green">
                <div class="card-tag">Reservasi</div>
                <h3>Pesan Slot Parkir</h3>
                <p>Pesan slot parkir lebih awal agar tidak kehabisan saat tiba di lokasi.</p>
                <div class="card-cta">Buka <span>→</span></div>
            </a>

            <a href="profile.php" class="menu-card c-amber">
                <div class="card-tag">Akun</div>
                <h3>Profil Saya</h3>
                <p>Ubah nama, email, foto profil, dan ganti password akun kamu.</p>
                <div class="card-cta">Buka <span>→</span></div>
            </a>

        </div>

    </div>

    <div class="footer">
        Smart Parking
        <span class="footer-dot">•</span>
        Cloud-Based Parking System
        <span class="footer-dot">•</span>
        Real-time DynamoDB
    </div>

</div>

<div class="toast" id="toast"></div>

<script>
    // ── Realtime stats ──────────────────────────────────────────
    async function loadDashboardStats() {
        try {
            const response = await fetch('/backend/api/get_slots.php');
            const slots = await response.json();

            let occupied = 0;
            let available = 0;

            slots.forEach(slot => {
                if (slot.status === 'occupied') {
                    occupied++;
                } else {
                    available++;
                }
            });

            const occupiedEl   = document.getElementById('occupiedCount');
            const availableEl  = document.getElementById('availableCount');
            const percentEl    = document.getElementById('parkingPercent');
            const barEl        = document.getElementById('parkingBar');

            occupiedEl.classList.remove('loading');
            availableEl.classList.remove('loading');

            occupiedEl.textContent  = occupied;
            availableEl.textContent = available;

            const total   = occupied + available;
            const percent = total > 0 ? Math.round((occupied / total) * 100) : 0;

            percentEl.textContent  = percent + '%';
            barEl.style.width      = percent + '%';

        } catch (err) {
            console.error('Gagal memuat data slot:', err);
        }
    }

    loadDashboardStats();
    setInterval(loadDashboardStats, 3000);

    // ── Toast ────────────────────────────────────────────────────
    function showToast(msg, type = 'success') {
        const el = document.getElementById('toast');
        el.textContent = msg;
        el.className   = 'toast ' + type + ' show';
        setTimeout(() => { el.className = 'toast ' + type; }, 3500);
    }

    // ── Reset ────────────────────────────────────────────────────
    async function resetParking() {
        const ok = confirm(
            'Yakin ingin menghapus SEMUA data kendaraan dan mereset slot parkir?\n\nAksi ini tidak bisa dibatalkan.'
        );
        if (!ok) return;

        try {
            const res  = await fetch('/backend/api/reset_parking.php', { method: 'POST' });
            const data = await res.json();

            if (data.status === 'success') {
                showToast('Data berhasil direset!', 'success');

                // Update total kendaraan ke 0 langsung
                const totalEl = document.querySelector('.stat-blue .stat-val');
                if (totalEl) totalEl.textContent = '0';

                // Refresh slot stats
                loadDashboardStats();
            } else {
                showToast('Gagal: ' + (data.message || 'Unknown error'), 'error');
            }
        } catch (err) {
            showToast('Koneksi gagal saat reset.', 'error');
            console.error(err);
        }
    }
</script>

</body>
</html>