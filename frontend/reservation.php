<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reservasi Parkir</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:url('assets/images/parking-bg.jpg') center/cover no-repeat fixed;
            position:relative;
            overflow:hidden;
            padding:30px;
        }

        body::before{
            content:'';
            position:absolute;
            inset:0;
            background:
            linear-gradient(
                135deg,
                rgba(4,8,20,0.93),
                rgba(12,18,35,0.88)
            );
        }

        .reservation-box{
            position:relative;
            z-index:2;
            width:100%;
            max-width:560px;
            padding:42px;
            border-radius:34px;
            background:rgba(255,255,255,0.06);
            backdrop-filter:blur(18px);
            border:1px solid rgba(255,255,255,0.08);
            box-shadow:0 20px 60px rgba(0,0,0,0.35);
            color:white;
        }

        .back-btn{
            display:inline-block;
            margin-bottom:25px;
            padding:12px 18px;
            border-radius:14px;
            text-decoration:none;
            color:white;
            background:rgba(255,255,255,0.08);
            border:1px solid rgba(255,255,255,0.08);
            transition:0.3s;
            font-size:14px;
            font-weight:600;
        }

        .back-btn:hover{
            background:rgba(255,255,255,0.14);
        }

        h1{
            font-size:52px;
            margin-bottom:15px;
            font-weight:800;
            line-height:1.1;
        }

        .desc{
            color:rgba(255,255,255,0.72);
            line-height:1.9;
            margin-bottom:35px;
            font-size:15px;
        }

        .input-group{
            margin-bottom:24px;
        }

        .input-group label{
            display:block;
            margin-bottom:12px;
            font-size:14px;
            color:rgba(255,255,255,0.78);
        }

        .input-group input{
            width:100%;
            padding:18px;
            border:none;
            outline:none;
            border-radius:18px;
            background:rgba(255,255,255,0.08);
            color:white;
            font-size:15px;
        }

        .input-group input::placeholder{
            color:rgba(255,255,255,0.4);
        }

        .submit-btn{
            width:100%;
            padding:18px;
            border:none;
            border-radius:20px;
            background:
            linear-gradient(
                135deg,
                #00b874,
                #0d5f46
            );
            color:white;
            font-size:16px;
            font-weight:700;
            cursor:pointer;
            transition:0.3s;
        }

        .submit-btn:hover{
            transform:translateY(-3px);
        }

        .alert{
            margin-top:25px;
            padding:18px;
            border-radius:18px;
            display:none;
            line-height:1.8;
            font-size:14px;
        }

        .success{
            background:rgba(0,190,120,0.14);
            border:1px solid rgba(0,190,120,0.35);
            color:#8ff0c8;
        }

        .error{
            background:rgba(255,80,80,0.12);
            border:1px solid rgba(255,80,80,0.35);
            color:#ffb3b3;
        }

    </style>

</head>

<body>

<div class="reservation-box">

    <a href="dashboard.php"
       class="back-btn">

        ← Kembali ke Dashboard

    </a>

    <h1>
        Reservasi Parkir
    </h1>

    <p class="desc">
        Lakukan reservasi slot parkir secara realtime
        melalui sistem Smart Parking untuk memastikan
        ketersediaan area parkir kendaraan Anda.
    </p>

    <form id="reserveForm">

        <div class="input-group">

            <label>
                Nama Pengguna
            </label>

            <input type="text"
                   id="user_name"
                   placeholder="Masukkan nama pengguna"
                   required>

        </div>

        <div class="input-group">

            <label>
                Slot Parkir
            </label>

            <input type="text"
                   id="slot_id"
                   placeholder="Contoh: A1"
                   required>

        </div>

        <button type="button"
                onclick="reserveSlot()"
                class="submit-btn">

            Reservasi Sekarang

        </button>

    </form>

    <div id="alertBox"
         class="alert">
    </div>

</div>

<script>

function reserveSlot(){

    const userName =
        document.getElementById('user_name').value;

    const slotId =
        document.getElementById('slot_id').value;

    const alertBox =
        document.getElementById('alertBox');

    if(userName === '' || slotId === ''){

        alertBox.style.display = 'block';

        alertBox.className =
            'alert error';

        alertBox.innerHTML =
            'Semua data wajib diisi.';

        return;
    }

    fetch('/backend/api/reserve_slot.php', {

        method:'POST',

        headers:{
            'Content-Type':
            'application/x-www-form-urlencoded'
        },

        body:
            'user_name=' + encodeURIComponent(userName)
            +
            '&slot_id=' + encodeURIComponent(slotId)

    })

    .then(response => response.json())

    .then(result => {

        alertBox.style.display = 'block';

        if(result.status === 'success'){

            alertBox.className =
                'alert success';

            alertBox.innerHTML =
                `
                Reservasi berhasil.<br><br>

                Slot parkir
                <b>${slotId}</b>
                berhasil dipesan atas nama
                <b>${userName}</b>.
                `;

            setTimeout(() => {

                window.location.href =
                    'parking_slots.php';

            }, 2200);

        }else{

            alertBox.className =
                'alert error';

            alertBox.innerHTML =
                result.message;
        }

    })

    .catch(error => {

        alertBox.style.display = 'block';

        alertBox.className =
            'alert error';

        alertBox.innerHTML =
            'Sistem gagal terhubung ke server.';
    });

}

</script>

</body>
</html>