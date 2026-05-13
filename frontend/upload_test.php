<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Upload Kendaraan</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

<div class="overlay"></div>

<div id="toast-container"></div>

<div class="login-container">

    <div class="login-card">

        <h1>Smart Parking</h1>

        <h3>Upload Kendaraan</h3>

        <form id="uploadForm"
              enctype="multipart/form-data">

            <input type="text"
                   name="plate_number"
                   placeholder="Masukkan Nomor Plat"
                   required>

            <input type="file"
                   name="image"
                   required>

            <button type="submit">
                Upload ke Cloud
            </button>

        </form>

        <div class="bottom-text">

            <a href="dashboard.php">
                ← Kembali ke Dashboard
            </a>

        </div>

    </div>

</div>

<script>

const form = document.getElementById('uploadForm');

form.addEventListener('submit', async function(e){

    e.preventDefault();

    const formData = new FormData(form);

    try{

        const response = await fetch(
            '/backend/api/upload_vehicle.php',
            {
                method: 'POST',
                body: formData
            }
        );

        const data = await response.json();

        if(data.status === 'success'){

            showToast(

                'Upload Berhasil',

                `Kendaraan ${formData.get('plate_number')} berhasil ditambahkan ke sistem.`,

                'success'
            );

            form.reset();

        }else{

            showToast(

                'Upload Gagal',

                data.message,

                'error'
            );
        }

    }catch(error){

        showToast(

            'Server Error',

            'Terjadi kesalahan pada sistem.',

            'error'
        );
    }
});

function showToast(title, message, type = 'success') {

    const toast = document.createElement('div');

    toast.className = `toast toast-${type}`;

    toast.innerHTML = `
    
        <div class="toast-title">
            ${title}
        </div>

        <div class="toast-message">
            ${message}
        </div>

    `;

    document
        .getElementById('toast-container')
        .appendChild(toast);

    setTimeout(() => {

        toast.remove();

    }, 5000);
}

</script>

</body>
</html>