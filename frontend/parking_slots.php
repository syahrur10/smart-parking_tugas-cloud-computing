<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Slot Parkir Realtime</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <style>

        .slot-wrapper{

            width:90%;
            margin:40px auto;
            position:relative;
            z-index:2;
        }

        .slot-title{

            text-align:center;
            color:white;
            font-size:56px;
            font-weight:800;
            margin-top:40px;
        }

        .slot-subtitle{

            text-align:center;
            color:#d1d5db;
            margin-top:20px;
            margin-bottom:60px;
            font-size:18px;
        }

        .slot-grid{

            display:grid;

            grid-template-columns:
            repeat(auto-fit,minmax(180px,1fr));

            gap:25px;
        }

        .slot-card{

            border-radius:24px;

            padding:40px 20px;

            color:white;

            text-align:center;

            backdrop-filter:blur(14px);

            border:1px solid rgba(255,255,255,.08);

            transition:.3s ease;

            box-shadow:
            0 10px 30px rgba(0,0,0,.35);
        }

        .slot-card:hover{

            transform:translateY(-6px);
        }

        .slot-available{

            background:
            linear-gradient(
                135deg,
                rgba(16,185,129,.9),
                rgba(5,150,105,.85)
            );
        }

        .slot-occupied{

            background:
            linear-gradient(
                135deg,
                rgba(239,68,68,.9),
                rgba(185,28,28,.85)
            );
        }

        .slot-name{

            font-size:42px;
            font-weight:800;
            margin-bottom:15px;
        }

        .slot-status{

            font-size:16px;
            opacity:.95;
        }

        .back-btn{

            display:inline-block;

            padding:14px 24px;

            border-radius:14px;

            background:
            linear-gradient(
                135deg,
                #ff9800,
                #ff6a00
            );

            color:white;

            text-decoration:none;

            font-weight:700;

            margin-bottom:40px;
        }

    </style>

</head>

<body>

<div class="overlay"></div>

<div class="slot-wrapper">

    <a href="dashboard.php"
       class="back-btn">

       ← Dashboard

    </a>

    <div class="slot-title">

        Monitoring Slot Parkir

    </div>

    <div class="slot-subtitle">

        Status slot parkir diperbarui otomatis secara realtime.

    </div>

    <div class="slot-grid"
         id="slotGrid">

    </div>

</div>

<script>

async function loadSlots(){

    try{

        const response = await fetch(
            '/backend/api/get_slots.php'
        );

        const data = await response.json();

        const grid =
        document.getElementById('slotGrid');

        grid.innerHTML = '';

        data.forEach(slot => {

            const statusClass =
            slot.status === 'available'
            ? 'slot-available'
            : 'slot-occupied';

            const statusText =
            slot.status === 'available'
            ? 'Tersedia'
            : 'Terisi';

            grid.innerHTML += `

                <div class="slot-card ${statusClass}">

                    <div class="slot-name">
                        ${slot.slot_id}
                    </div>

                    <div class="slot-status">
                        ${statusText}
                    </div>

                </div>

            `;
        });

    }catch(error){

        console.log(error);
    }
}

loadSlots();

setInterval(loadSlots, 3000);

</script>

</body>
</html>