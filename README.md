# Mealify – Web Sistem Manajemen Nutrisi & Perencanaan Makan

Dokumentasi resmi untuk struktur project, alur kerja, instalasi, dan hubungan antar komponen dalam aplikasi **Mealify**.

---

# 📑 Daftar Isi

1. [Tentang Aplikasi](#-tentang-aplikasi)
2. [Fitur Utama](#-fitur-utama)
3. [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
4. [Instalasi & Konfigurasi](#-instalasi--konfigurasi)
5. [Struktur Direktori](#-struktur-direktori)
6. [Skema Database](#-skema-database)
7. [Penjelasan Komponen](#-penjelasan-komponen)
8. [Alur Kerja](#-alur-kerja)

---

# 💡 Tentang Aplikasi

**Mealify** adalah aplikasi berbasis web yang dirancang untuk membantu pengguna (khususnya mahasiswa) dalam mengelola gaya hidup sehat. Aplikasi ini memungkinkan pengguna untuk merencanakan menu makan, menganalisis nutrisi bahan makanan, dan memantau konsistensi melalui sistem streak.

---

# 🚀 Fitur Utama

*   **Autentikasi Ganda**: Login konvensional dengan verifikasi **OTP Email** dan integrasi **Google OAuth**.
*   **Pencarian Resep**: Integrasi dengan **Edamam Recipe Search API** untuk menemukan beberapa resep makanan.
*   **Meal Planner**: Jadwalkan makan pagi, siang, dan malam Anda secara terorganisir.
*   **Nutrition Analyzer**: Analisis kandungan nutrisi (kalori, protein, lemak, karbo) secara instan dari teks daftar bahan makanan (Edamam Nutrition API).
*   **Streak System**: Motivasi harian melalui pelacakan konsistensi makan sehat.
*   **Admin Analytics**: Dashboard khusus admin untuk memantau aktivitas user dan statistik nutrisi pengguna secara keseluruhan.
*   **Notifikasi Pengingat**: Pengiriman email otomatis untuk mengingatkan jadwal makan atau notifikasi dari admin.

---

# 🛠 Teknologi yang Digunakan

*   **Bahasa Pemrograman**: PHP 8.1+ (Native OOP)
*   **Database**: MySQL 8.0
*   **Frontend**: HTML5, CSS3 Modern (Custom Design), JavaScript (Vanilla), Chart.js, Feather Icons.
*   **Dependencies (Composer)**:
    *   `phpmailer/phpmailer`: Sistem pengiriman email (Gmail SMTP).
    *   `vlucas/phpdotenv`: Manajemen variabel lingkungan (.env).
    *   `google/apiclient`: Integrasi login Google.

---

# ⚙ Instalasi & Konfigurasi

Ikuti langkah berikut untuk menjalankan aplikasi di lokal (XAMPP/Laragon):

### 1. Clone Repository

```bash
git clone [URL_REPOSI_ANDA]
cd mealify
```

### 2. Install Dependencies

Pastikan **Composer** sudah terinstall, lalu jalankan:

```bash
composer install
```

### 3. Konfigurasi Database

1.  Buat database baru di MySQL, misalnya `mealify`.
2.  Import file database (jika ada) yaitu `SQL/mealify_full_schema.sql` atau pastikan tabel dibuat sesuai [Skema Database](#-skema-database) di bawah.

### 4. Konfigurasi Environment

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Lalu edit file `.env` sesuaikan dengan konfigurasi lokal Anda:

```env
DB_HOST=localhost
DB_NAME=mealify
DB_USER=root
DB_PASS=

# Konfigurasi API Edamam (Untuk fitur search & nutrition)
EDAMAM_RECIPE_APP_ID=your_app_id
EDAMAM_RECIPE_APP_KEY=your_app_key
EDAMAM_NUTRITION_APP_ID=your_app_id
EDAMAM_NUTRITION_APP_KEY=your_app_key

# Konfigurasi SMTP (Untuk notifikasi email)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=email@anda.com
MAIL_PASSWORD=password_aplikasi
```

### 5. Jalankan Aplikasi

Buka browser dan akses:

```
http://localhost/mealify/public/index.php
```

---

# 📁 Struktur Direktori

```
mealify/
├── app/
│   ├── api/          # Endpoint internal untuk pencarian & statistik
│   ├── classes/      # Logic Class (API Client, dll)
│   ├── config/       # Koneksi Database
│   ├── controllers/  # Logic aplikasi (Auth, MealPlan, Nutrition)
│   ├── helpers/      # Fungsi pembantu (Session, Email, Env)
│   ├── models/       # Interaksi Database (User, OTP, dll)
│   └── scripts/      # Script Cron Job (Reminder & Notifikasi)
├── public/
│   ├── admin/        # Panel Dashboard Administrator
│   ├── mahasiswa/    # Fitur utama untuk user (Dashboard, Profile)
│   ├── assets/       # CSS, JS, Images
│   └── index.php     # Landing Page Utama
├── SQL/              # File export database (.sql)
└── vendor/           # Library pihak ketiga
```

---

# 🗄 Skema Database

Berikut adalah tabel utama yang digunakan dalam **Mealify**:

### 1. `users`

Menyimpan data pengguna dan target kesehatan mereka.

*   `id` (INT UNSIGNED, PK, AI)
*   `name` (VARCHAR(100))
*   `email` (VARCHAR(150), Unique)
*   `password` (VARCHAR(255))
*   `google_id` (VARCHAR(255))
*   `google_avatar_url` (VARCHAR(255))
*   `is_verified` (TINYINT(1))
*   `role` (ENUM: 'admin', 'mahasiswa')
*   `is_active` (TINYINT(1))
*   `weight_kg` (INT)
*   `goal_diet` (TINYINT(1))
*   `goal_bulking` (TINYINT(1))
*   `avatar` (VARCHAR(255))
*   `streak_freeze` (INT)
*   `last_freeze_refill` (DATE)
*   `last_streak_check` (DATE)

### 2. `meal_plans`

Data jadwal rencana makan harian pengguna.

*   `id` (INT UNSIGNED, PK, AI)
*   `user_id` (INT UNSIGNED, FK -> users.id)
*   `food_name` (VARCHAR(255))
*   `meal_type` (VARCHAR(50))
*   `meal_time` (DATETIME)
*   `notes` (TEXT)
*   `is_notified` (TINYINT(1))
*   `created_at` (TIMESTAMP)

### 3. `meal_logs`

Catatan riwayat makan user per hari.

*   `id` (INT UNSIGNED, PK, AI)
*   `user_id` (INT UNSIGNED, FK -> users.id)
*   `meal_type` (ENUM: 'breakfast', 'lunch', 'dinner')
*   `meal_name` (VARCHAR(255))
*   `log_date` (DATE)
*   `logged_at` (DATETIME)
*   `calories`, `protein`, `carbs`, `fat` (DOUBLE)

### 4. `favorites`

Daftar resep makanan yang disimpan/disukai oleh pengguna.

*   `id` (INT UNSIGNED, PK, AI)
*   `user_id` (INT UNSIGNED, FK -> users.id)
*   `recipe_uri` (VARCHAR(255))
*   `label` (VARCHAR(255))
*   `image` (TEXT)
*   `source` (VARCHAR(255))
*   `url` (TEXT)
*   `calories` (INT)
*   `created_at` (TIMESTAMP)

### 6. `reset_password`

Token keamanan untuk proses pemulihan kata sandi.

*   `id` (INT UNSIGNED, PK, AI)
*   `user_id` (INT UNSIGNED, FK -> users.id)
*   `email` (VARCHAR(150))
*   `token` (VARCHAR(255))
*   `expires_at` (DATETIME)
*   `is_used` (TINYINT(1))
*   `created_at` (DATETIME)

### 7. `admin_notifications`

Template dan jadwal notifikasi dari administrator.

*   `id` (INT UNSIGNED, PK, AI)
*   `name` (VARCHAR(150))
*   `title` (VARCHAR(191))
*   `body` (TEXT)
*   `type` (ENUM: 'reminder_mealplanner')
*   `send_time` (TIME)
*   `active` (TINYINT(1))
*   `created_at` (DATETIME)

---

# 🔍 Penjelasan Komponen

### 1. Komunikasi Database (Models & Config)
Bertanggung jawab atas koneksi dan manipulasi data langsung di database.
*   **app/config/Database.php**: Menginisialisasi koneksi MySQL menggunakan MySQLi.
*   **app/models/User.php**: Mengelola data user, registrasi secara native, login, dan profil.
*   **app/models/OTP.php**: Menangani pembuatan dan validasi kode OTP untuk keamanan.

### 2. Logika Fitur (Controllers & Classes)
Menangani alur bisnis, integrasi API sistem, dan pemrosesan data.
*   **app/controllers/**: Berisi logika utama fitur seperti `AuthController.php` (autentikasi), `MealPlannerController.php` (rencana makan), dan `NutritionController.php` (analisis nutrisi).
*   **app/api/**: Endpoint API internal yang melayani request AJAX dari frontend (misal: `search.php`, `favorite.php`).
*   **app/classes/ApiClientEdamam.php**: Menangani komunikasi HTTP ke API eksternal Edamam untuk data resep dan nutrisi.
*   **app/helpers/Mailer.php**: Layanan pengiriman email untuk OTP dan notifikasi.

### 3. Bagian Interface (Public / Frontend)
Bagian yang berinteraksi langsung dengan pengguna di browser.
*   **public/mahasiswa/**: Halaman antarmuka untuk user (Dashboard, Detail Resep, Planner, Analisis Nutrisi).
*   **public/admin/**: Antarmuka dashboard administrator untuk manajemen user dan statistik analytics.
*   **public/assets/**: Asset pendukung seperti CSS (styling modern), JavaScript (interaktivitas frontend), dan desain visual (Feather Icons).

---

# 🔗 Alur Kerja

**Alur Pencarian Makanan (Search Food)**
```
User (Browser)
   │
   ▼
public/mahasiswa/mhs_dashboard.php (Input Keyword)
   │
   ▼
app/api/search.php (Internal API)
   │
   ▼
app/classes/ApiClientEdamam.php
   │
   │ (Request HTTP)
   ▼
Edamam API (External) ──► Mengembalikan Hasil Resep (JSON)
   │
   ▼
app/api/search.php (Parsing Data)
   │
   ▼
public/mahasiswa/mhs_dashboard.php (Tampil Hasil di Grid Card)
```

**Alur Melihat Detail Makanan (View Detail)**
```
User Klik "Card Resep"
   │
   ▼
Javascript (dashboard.js) ──► Simpan data ke SessionStorage
   │
   ▼
Navigasi ke public/mahasiswa/detail.php
   │
   ▼
Javascript (detail.js) ──► Baca SessionStorage & Render UI
```

**Alur Menambahkan ke Favorit (Add to Favorite)**
```
User Klik "Add to Favorite" (di halaman detail)
   │
   ▼
Javascript (detail.js) ──► Request POST
   │
   ▼
app/api/favorite.php
   │
   ▼
app/models/ (Interaksi Database)
   │
   ▼
Database (Tabel favorites)
```
