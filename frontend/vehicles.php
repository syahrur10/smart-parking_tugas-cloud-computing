<?php

include '../backend/config/database.php';

$query = mysqli_query($conn, "
    SELECT *
    FROM vehicle_uploads
    ORDER BY created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Daftar Kendaraan</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body{
            min-height: 100vh;
            overflow-x: hidden;
        }

        .vehicle-container{
            width: 92%;
            margin: 40px auto;
            position: relative;
            z-index: 2;
        }

        .vehicle-title{
            text-align: center;
            color: white;
            margin-bottom: 40px;
            font-size: 42px;
            font-weight: bold;
        }

        .vehicle-grid{
            display: grid;
            grid-template-columns: repeat(auto-fit,minmax(320px,1fr));
            gap: 30px;
        }

        .vehicle-card{
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(14px);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            transition: 0.3s ease;
        }

        .vehicle-card:hover{
            transform: translateY(-6px);
            box-shadow: 0 14px 35px rgba(0,0,0,0.45);
        }

        .vehicle-card img{
            width: 100%;
            height: 260px;
            object-fit: cover;
            display: block;
            background: #111;
        }

        .vehicle-info{
            padding: 24px;
            color: white;
        }

        .vehicle-info h3{
            font-size: 26px;
            margin-bottom: 12px;
        }

        .vehicle-info p{
            color: #ddd;
            font-size: 14px;
            line-height: 1.7;
        }

        .back-btn{
            display: inline-block;
            margin-bottom: 35px;
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg,#ff9800,#ff6a00);
            padding: 13px 24px;
            border-radius: 14px;
            font-weight: bold;
            transition: 0.3s;
        }

        .back-btn:hover{
            transform: scale(1.05);
        }

        .empty-state{
            text-align: center;
            color: white;
            margin-top: 100px;
            font-size: 24px;
            opacity: 0.85;
        }

    </style>

</head>

<body>

<div class="overlay"></div>

<div class="vehicle-container">

    <a href="dashboard.php" class="back-btn">
        ← Dashboard
    </a>

    <h1 class="vehicle-title">
        Data Kendaraan Cloud Storage
    </h1>

    <?php if(mysqli_num_rows($query) > 0) : ?>

        <div class="vehicle-grid">

            <?php while($row = mysqli_fetch_assoc($query)) : ?>

                <div class="vehicle-card">

                    <img src="../backend/api/proxy_image.php?file=<?= urlencode($row['vehicle_image']); ?>"
                         alt="Vehicle Image">

                    <div class="vehicle-info">

                        <h3>
                            <?= htmlspecialchars($row['plate_number']); ?>
                        </h3>

                        <p>
                            Upload:
                            <?= $row['created_at']; ?>
                        </p>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else : ?>

        <div class="empty-state">
            Belum ada kendaraan yang diupload.
        </div>

    <?php endif; ?>

</div>

</body>
</html>