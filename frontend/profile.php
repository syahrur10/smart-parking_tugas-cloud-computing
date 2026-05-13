<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

require '../backend/config/database.php';

$user = $_SESSION['user'];
$success = '';
$error   = '';

// Handle update profile
if (isset($_POST['update_profile'])) {

    $nama  = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $photo = $user['photo'] ?? null;

    // Validasi email tidak kosong
    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email tidak boleh kosong.';
    } else {

        // Proses upload foto jika ada
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {

            $allowed = ['image/jpeg','image/jpg','image/png','image/webp'];

            if (!in_array($_FILES['photo']['type'], $allowed)) {
                $error = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';

            } elseif ($_FILES['photo']['size'] > 3 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 3MB.';

            } else {
                // Upload ke S3 jika tersedia, fallback ke local
                $fileName = 'profile_' . $user['id'] . '_' . time()
                          . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);

                try {
                    require '../backend/aws/s3.php';

                    $s3->putObject([
                        'Bucket'     => 'smartparking-bucket',
                        'Key'        => 'profiles/' . $fileName,
                        'SourceFile' => $_FILES['photo']['tmp_name'],
                        'ACL'        => 'public-read'
                    ]);

                    $photo = 'profiles/' . $fileName;

                } catch (Exception $e) {
                    // Fallback: simpan lokal
                    $uploadDir = __DIR__ . '/assets/uploads/profiles/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $fileName);
                    $photo = 'assets/uploads/profiles/' . $fileName;
                }
            }
        }

        if (empty($error)) {

            // Cek email sudah dipakai user lain
            $checkEmail = $conn->prepare("
                SELECT id FROM users WHERE email = ? AND id != ?
            ");
            $checkEmail->bind_param("si", $email, $user['id']);
            $checkEmail->execute();

            if ($checkEmail->get_result()->num_rows > 0) {
                $error = 'Email sudah dipakai akun lain.';

            } else {

                // Update database
                $update = $conn->prepare("
                    UPDATE users SET nama = ?, email = ?, photo = ?
                    WHERE id = ?
                ");
                $update->bind_param("sssi", $nama, $email, $photo, $user['id']);
                $update->execute();

                // Perbarui session
                $_SESSION['user']['nama']  = $nama;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['photo'] = $photo;
                $user = $_SESSION['user'];

                $success = 'Profil berhasil diperbarui.';
            }
        }
    }
}

// Handle ganti password
if (isset($_POST['change_password'])) {

    $oldPass  = $_POST['old_password'];
    $newPass  = $_POST['new_password'];
    $confPass = $_POST['confirm_password'];

    if (empty($oldPass) || empty($newPass) || empty($confPass)) {
        $error = 'Semua kolom password wajib diisi.';

    } elseif ($newPass !== $confPass) {
        $error = 'Konfirmasi password tidak cocok.';

    } elseif (strlen($newPass) < 6) {
        $error = 'Password minimal 6 karakter.';

    } else {
        // Ambil password terkini dari DB
        $getUser = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $getUser->bind_param("i", $user['id']);
        $getUser->execute();
        $dbUser = $getUser->get_result()->fetch_assoc();

        if (!password_verify($oldPass, $dbUser['password'])) {
            $error = 'Password lama tidak tepat.';
        } else {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashed, $user['id']);
            $upd->execute();
            $success = 'Password berhasil diganti.';
        }
    }
}

// Ambil foto profile
function getPhotoUrl($photo) {
    if (!$photo) return null;

    if (str_starts_with($photo, 'profiles/')) {
        return '/backend/api/proxy_image.php?file=' . urlencode($photo);
    }

    return $photo;
}

$photoUrl = getPhotoUrl($user['photo'] ?? null);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya – Smart Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --accent: #4f8eff;
            --green: #00b874;
            --red: #ff5252;
            --glass: rgba(255,255,255,0.06);
            --border: rgba(255,255,255,0.08);
        }

        body {
            min-height: 100vh;
            background: url('assets/images/parking-bg.jpg') center/cover no-repeat fixed;
            color: white;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(4,8,20,0.93), rgba(12,18,35,0.88));
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            width: 92%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 45px 0 80px;
        }

        /* Topbar */
        .topbar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 40px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 14px;
            background: var(--glass);
            border: 1px solid var(--border);
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-btn:hover { background: rgba(255,255,255,0.1); }

        .page-title {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        /* Notifikasi */
        .notif {
            padding: 16px 20px;
            border-radius: 16px;
            margin-bottom: 28px;
            font-size: 14px;
            font-weight: 500;
        }

        .notif-success {
            background: rgba(0,190,120,0.14);
            border: 1px solid rgba(0,190,120,0.35);
            color: #8ff0c8;
        }

        .notif-error {
            background: rgba(255,82,82,0.12);
            border: 1px solid rgba(255,82,82,0.35);
            color: #ffb3b3;
        }

        /* Layout utama */
        .profile-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
        }

        /* Card glass */
        .glass-card {
            background: var(--glass);
            border: 1px solid var(--border);
            backdrop-filter: blur(18px);
            border-radius: 28px;
            padding: 32px;
        }

        /* Avatar section */
        .avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .avatar-wrap {
            position: relative;
            width: 130px;
            height: 130px;
            cursor: pointer;
        }

        .avatar-wrap img,
        .avatar-placeholder {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            border: 3px solid var(--accent);
        }

        .avatar-placeholder {
            background: linear-gradient(135deg, rgba(79,142,255,0.3), rgba(20,30,70,0.9));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 52px;
            font-weight: 800;
            color: rgba(255,255,255,0.85);
        }

        .avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        .avatar-wrap:hover .avatar-overlay { opacity: 1; }

        .user-name-big {
            font-size: 22px;
            font-weight: 700;
            text-align: center;
        }

        .user-email-big {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
            text-align: center;
        }

        .user-badge {
            background: rgba(79,142,255,0.15);
            border: 1px solid rgba(79,142,255,0.3);
            padding: 8px 18px;
            border-radius: 12px;
            font-size: 12px;
            color: #8fbfff;
            font-weight: 600;
        }

        /* Form */
        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 22px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 15px 18px;
            background: rgba(255,255,255,0.07);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: white;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: rgba(79,142,255,0.5);
            background: rgba(79,142,255,0.08);
        }

        .form-group input::placeholder { color: rgba(255,255,255,0.3); }

        .btn-primary {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #4f8eff, #2a4fcc);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 8px;
        }

        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .btn-danger {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff5252, #cc2020);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 8px;
        }

        .btn-danger:hover { transform: translateY(-2px); filter: brightness(1.1); }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 28px 0;
        }

        /* Hidden file input */
        #photoInput { display: none; }

        @media (max-width: 900px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="topbar">
        <a href="dashboard.php" class="back-btn">← Dashboard</a>
        <h1 class="page-title">Profil Saya</h1>
    </div>

    <?php if ($success): ?>
        <div class="notif notif-success">✓ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="notif notif-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="profile-layout">

        <!-- Sidebar Avatar -->
        <div class="glass-card avatar-section">

            <form method="POST" enctype="multipart/form-data" id="photoForm">
                <input type="file" name="photo" id="photoInput"
                       accept="image/jpeg,image/png,image/webp"
                       onchange="this.form.submit()">
                <input type="hidden" name="update_profile" value="1">
                <input type="hidden" name="nama" value="<?= htmlspecialchars($user['nama']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </form>

            <div class="avatar-wrap" onclick="document.getElementById('photoInput').click()">
                <?php if ($photoUrl): ?>
                    <img src="<?= htmlspecialchars($photoUrl) ?>" alt="Foto Profil" id="avatarImg">
                <?php else: ?>
                    <div class="avatar-placeholder" id="avatarImg">
                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div class="avatar-overlay">📷<br>Ganti Foto</div>
            </div>

            <div class="user-name-big"><?= htmlspecialchars($user['nama']) ?></div>
            <div class="user-email-big"><?= htmlspecialchars($user['email']) ?></div>
            <div class="user-badge">Pengguna Aktif</div>

        </div>

        <!-- Form Edit -->
        <div>

            <!-- Edit Profil -->
            <div class="glass-card" style="margin-bottom:28px">

                <div class="section-title">Edit Informasi Profil</div>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama"
                               value="<?= htmlspecialchars($user['nama']) ?>"
                               placeholder="Nama lengkap" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat Email</label>
                        <input type="email" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>"
                               placeholder="Email aktif" required>
                    </div>

                    <div class="form-group">
                        <label>Foto Profil (JPG/PNG/WEBP, maks. 3MB)</label>
                        <input type="file" name="photo"
                               accept="image/jpeg,image/png,image/webp">
                    </div>

                    <button type="submit" class="btn-primary">Simpan Perubahan</button>

                </form>

            </div>

            <!-- Ganti Password -->
            <div class="glass-card">

                <div class="section-title">Ganti Password</div>

                <form method="POST">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label>Password Saat Ini</label>
                        <input type="password" name="old_password"
                               placeholder="Password lama">
                    </div>

                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password"
                               placeholder="Minimal 6 karakter">
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password"
                               placeholder="Ketik ulang password baru">
                    </div>

                    <button type="submit" class="btn-danger">Ganti Password</button>

                </form>

            </div>

        </div>

    </div>

</div>

<script>
// Preview foto sebelum upload
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(ev) {
        const old = document.getElementById('avatarImg');
        const img = document.createElement('img');
        img.src = ev.target.result;
        img.id = 'avatarImg';
        img.style.cssText = 'width:130px;height:130px;border-radius:50%;object-fit:cover;border:3px solid #4f8eff';
        old.parentNode.replaceChild(img, old);
    };
    reader.readAsDataURL(file);
});
</script>

</body>
</html>