# Smart Parking System

Aplikasi web manajemen parkir yang dikembangkan sebagai proyek akhir mata kuliah. Sistem ini memungkinkan pengguna melihat ketersediaan slot parkir secara real-time, melakukan reservasi, mengelola data kendaraan, dan mendapatkan notifikasi — semuanya terintegrasi dengan layanan AWS menggunakan LocalStack untuk simulasi cloud di lokal.

**Nama  :** Syahrur Baihaqi  
**NIM   :** 32602400101  
**Prodi :** S1 Teknik Informatika

---

## Daftar Isi

1. [Fitur Aplikasi](#fitur-aplikasi)
2. [Arsitektur Sistem](#arsitektur-sistem)
3. [Tech Stack](#tech-stack)
4. [Struktur Folder](#struktur-folder)
5. [Cara Menjalankan](#cara-menjalankan)
6. [Konfigurasi AWS LocalStack](#konfigurasi-aws-localstack)
7. [Endpoint API](#endpoint-api)
8. [Skema Database](#skema-database)
9. [Catatan](#catatan)

---

## Fitur Aplikasi

- Registrasi dan login pengguna dengan manajemen sesi PHP
- Dashboard dengan ringkasan statistik parkir dan sapaan berdasarkan waktu
- Tampilan peta 160 slot parkir (A1 sampai D4) dengan status real-time
- Reservasi slot langsung dari halaman web
- Upload foto kendaraan beserta nomor plat, tersimpan di AWS S3
- Sistem notifikasi via AWS SQS setiap ada reservasi masuk
- Manajemen profil dengan foto yang tersimpan di cloud

---

## Arsitektur Sistem

```
Browser / Client
      |
      | HTTP
      v
Apache + PHP 8.2 (Port 8080)
      |              |
      v              v
  MySQL 8.0     LocalStack (Port 4566)
  (Port 3307)        |
                     |-- DynamoDB  (data slot parkir)
                     |-- S3        (foto kendaraan & profil)
                     |-- SQS       (antrian notifikasi)
```

Data slot parkir dikelola di DynamoDB karena lebih cepat untuk operasi baca/tulis yang sering. Data pengguna dan kendaraan disimpan di MySQL. Foto disimpan di S3, dan notifikasi dikirim lewat SQS setiap ada reservasi masuk.

---

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8.2, Apache |
| Frontend | PHP server-side rendering, HTML, CSS |
| Database relasional | MySQL 8.0 |
| Database NoSQL | AWS DynamoDB via LocalStack |
| Object storage | AWS S3 via LocalStack |
| Message queue | AWS SQS via LocalStack |
| AWS SDK | aws/aws-sdk-php via Composer |
| Container | Docker, Docker Compose |

---

## Struktur Folder

```
smart-parking/
|
|-- index.php                        # Entry point, redirect ke halaman login
|-- Dockerfile                       # Konfigurasi image PHP + Apache
|-- docker-compose.yml               # Konfigurasi semua service
|-- railway.json                     # Konfigurasi deploy ke Railway
|
|-- frontend/
|   |-- auth/
|   |   |-- login.php                # Halaman login
|   |   |-- register.php             # Halaman registrasi
|   |   `-- logout.php               # Proses logout dan hapus sesi
|   |-- assets/
|   |   |-- css/style.css            # Stylesheet global
|   |   |-- images/                  # Gambar statis
|   |   `-- uploads/profiles/        # Cache foto profil lokal
|   |-- dashboard.php                # Halaman dashboard
|   |-- parking_slots.php            # Halaman peta slot parkir
|   |-- reservation.php              # Halaman form reservasi
|   |-- vehicles.php                 # Halaman daftar kendaraan
|   |-- notifications.php            # Halaman notifikasi
|   |-- profile.php                  # Halaman profil pengguna
|   `-- process_booking.php          # Handler proses booking
|
|-- backend/
|   |-- api/
|   |   |-- get_slots.php            # GET: ambil data slot dari DynamoDB
|   |   |-- reserve_slot.php         # POST: reservasi slot parkir
|   |   |-- reset_parking.php        # POST: reset semua slot ke available
|   |   |-- upload_vehicle.php       # POST: upload foto kendaraan ke S3
|   |   |-- get_notifications.php    # GET: tarik notifikasi dari SQS
|   |   `-- proxy_image.php          # Proxy tampil gambar dari S3
|   |-- aws/
|   |   |-- dynamodb.php             # Inisialisasi DynamoDB client
|   |   |-- s3.php                   # Inisialisasi S3 client
|   |   `-- sqs.php                  # Inisialisasi SQS client
|   |-- config/
|   |   `-- database.php             # Koneksi MySQL
|   |-- controllers/
|   |   `-- BookingController.php
|   `-- database/
|       `-- parking.sql              # Skema tabel dan seed data awal
|
`-- localstack/
    `-- init/
        `-- init-aws.sh              # Script buat resource AWS saat container start
```

---

## Cara Menjalankan

Pastikan Docker dan Docker Compose sudah terinstall sebelum mulai.

**1. Clone repository**

```bash
git clone <url-repository>
cd smart-parking
```

**2. Jalankan semua service**

```bash
docker compose up -d --build
```

Perintah ini menjalankan tiga container sekaligus:
- `smart-parking-app` di port 8080, aplikasi PHP
- `smart-parking-mysql` di port 3307, database MySQL
- `smart-parking-localstack` di port 4566, emulator AWS

**3. Import skema database**

```bash
docker exec -i smart-parking-mysql mysql -uroot -proot smart_parking < backend/database/parking.sql
```

**4. Verifikasi LocalStack**

Resource AWS dibuat otomatis lewat `localstack/init/init-aws.sh` saat container pertama kali jalan. Kalau mau cek manual:

```bash
docker exec smart-parking-localstack awslocal s3 ls
docker exec smart-parking-localstack awslocal dynamodb list-tables
docker exec smart-parking-localstack awslocal sqs list-queues
```

**5. Buka di browser**

```
http://localhost:8080
```

Akan langsung diarahkan ke halaman login. Buat akun baru lewat halaman registrasi untuk mulai menggunakan aplikasi.

**Menghentikan aplikasi:**

```bash
docker compose down
```

---

## Konfigurasi AWS LocalStack

Semua layanan AWS berjalan lokal menggunakan LocalStack, jadi tidak perlu akun AWS asli. Endpoint dan kredensial dikonfigurasi di `backend/aws/*.php`.

| Service | Endpoint |
|---|---|
| DynamoDB | http://smart-parking-localstack:4566 |
| S3 | http://smart-parking-localstack:4566 |
| SQS | http://smart-parking-localstack:4566 |

Kredensial yang dipakai: key `test`, secret `test` — hanya untuk keperluan lokal.

Resource yang dibuat otomatis saat container start:

| Tipe | Nama |
|---|---|
| S3 Bucket | smartparking-bucket |
| DynamoDB Table | parking_slots |
| SQS Queue | parking-notification |

---

## Endpoint API

Semua endpoint ada di `backend/api/` dan mengembalikan response JSON.

| Method | URL | Fungsi |
|---|---|---|
| GET | /backend/api/get_slots.php | Ambil semua slot dan statusnya |
| POST | /backend/api/reserve_slot.php | Reservasi slot parkir |
| POST | /backend/api/reset_parking.php | Reset semua slot ke available |
| POST | /backend/api/upload_vehicle.php | Upload foto kendaraan ke S3 |
| GET | /backend/api/get_notifications.php | Tarik notifikasi dari SQS |
| GET | /backend/api/proxy_image.php?file= | Tampilkan gambar dari S3 |

Contoh request reservasi:

```bash
curl -X POST http://localhost:8080/backend/api/reserve_slot.php \
  -d "user_name=Baihaqi&slot_id=A1"
```

Contoh response:

```json
{
  "status": "success",
  "message": "Slot A1 berhasil dipesan atas nama Baihaqi."
}
```

---

## Skema Database

### MySQL

Tabel `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, Auto Increment | ID pengguna |
| nama | VARCHAR(100) | Nama lengkap |
| email | VARCHAR(100), Unique | Alamat email |
| password | VARCHAR(255) | Password yang sudah di-hash |
| photo | VARCHAR(255) | Path foto profil |
| created_at | TIMESTAMP | Waktu daftar |

Tabel `parking_slots`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, Auto Increment | ID record |
| slot_id | VARCHAR(10), Unique | Kode slot, A1 sampai F4 |
| status | ENUM | available atau occupied |
| booked_by | VARCHAR(100) | Nama pemesan |
| booked_at | TIMESTAMP | Waktu pemesanan |

Tabel `vehicle_uploads`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | INT, PK, Auto Increment | ID record |
| plate_number | VARCHAR(20) | Nomor plat kendaraan |
| vehicle_image | VARCHAR(500) | URL gambar di S3 |
| created_at | TIMESTAMP | Waktu upload |

### DynamoDB

Tabel `parking_slots` dipakai untuk operasi slot real-time karena lebih responsif dibanding MySQL untuk read/write yang sering dan cepat.

| Atribut | Tipe | Keterangan |
|---|---|---|
| slot_id (Partition Key) | String | Kode slot, contoh: A1, B3 |
| status | String | available atau occupied |
| booked_by | String | Nama pemesan |
| booked_at | String | Waktu pemesanan |

Total slot yang tersedia 16 slot, dari baris A sampai D dengan nomor 1 sampai 4.

---

## Catatan

Aplikasi ini menggunakan LocalStack sebagai pengganti AWS untuk pengembangan lokal. Kalau nanti deploy ke production, ganti endpoint LocalStack dengan endpoint AWS asli dan pakai IAM credentials yang valid.

File `backend/test_s3.php` tersedia untuk menguji koneksi ke S3 secara terpisah tanpa harus masuk ke aplikasi.

Folder `localstack/cache/` berisi data persistensi LocalStack antar restart container, jangan dihapus supaya tidak perlu inisialisasi ulang setiap kali container dijalankan.
