# GuramiSmart API

Backend REST API untuk aplikasi **GuramiSmart** — sistem informasi budidaya ikan gurami (versi input manual). Dibangun dengan Laravel 13 + Sanctum.

**Base URL produksi:** `https://guramismartapi.yogawilanda.com/api/v1`
**Daftar endpoint lengkap:** lihat [`API.md`](API.md)

## Fitur
- Registrasi & login (token)
- Siklus budidaya: luas kolam, tebar benih, modal awal, durasi otomatis
- Panduan budidaya (6 bab)
- Pencatatan pakan harian & kualitas air (suhu, pH) dengan status optimal/low/high
- Panen & penjualan
- Dashboard: modal vs penjualan, profit, ROI, rata-rata pakan, produktivitas/m², tren kualitas air
- Notifikasi otomatis: pengingat pakan (07:00 & 16:00 WIB) dan pengingat panen

---

## Quick Start (Python)

Semua endpoint wajib login, kecuali register & login. Login sekali → simpan token → kirim di setiap request.

```python
import requests
import pandas as pd

BASE = "https://guramismartapi.yogawilanda.com/api/v1"

r = requests.post(f"{BASE}/auth/login",
                  json={"email": "EMAIL_KAMU", "password": "PASSWORD_KAMU"},
                  headers={"Accept": "application/json"})
token = r.json()["token"]

H = {"Accept": "application/json", "Authorization": f"Bearer {token}"}

cycles = requests.get(f"{BASE}/cycles", headers=H).json()
cid = cycles[0]["id"]
dash = requests.get(f"{BASE}/cycles/{cid}/dashboard", headers=H).json()["data"]

pd.DataFrame(dash["water_trend"]).plot(x="date", y=["temperature_c", "ph"])
```

| Error | Arti |
|---|---|
| 401 | Token tidak terkirim / kedaluwarsa → login ulang, cek header `Authorization` |
| 404 | Data tidak ada atau bukan milik akunmu |
| 422 | Input ditolak → lihat `r.json()["errors"]` |

Akun demo (berisi data 2 siklus) tersedia — minta kredensialnya ke pengelola.

---

## Setup Lokal

Kebutuhan: PHP 8.3+, Composer, MySQL.

```bash
git clone https://github.com/yogawilanda/guramismart_api.git
cd guramismart_api
composer install
cp .env.example .env
php artisan key:generate
# atur DB_* di .env, APP_TIMEZONE sudah Asia/Jakarta di config/app.php
php artisan migrate
php artisan db:seed --class=GuideSeeder
php artisan db:seed --class=DemoSeeder   # opsional: akun demo
php artisan serve
```

Scheduler lokal (terminal terpisah): `php artisan schedule:work`

## Smoke Test

Menguji 35 skenario (auth, isolasi data, validasi, panen, dashboard) terhadap server yang sedang berjalan:

```powershell
powershell -ExecutionPolicy Bypass -File .\smoke.ps1
```
Atur `$base` di baris pertama `smoke.ps1` ke URL lokal atau produksi. Skrip membuat user tes `*@test.id`; hapus setelahnya di produksi.

## Deploy (Hostinger)

- `.htaccess` + `index.php` di root project mengarahkan semua request ke `public/`.
- Di server: `composer install --no-dev -o`, `php artisan migrate --force`, `php artisan config:cache && php artisan route:cache`.
- `.env` produksi: `APP_ENV=production`, `APP_DEBUG=false`.
- Cron (setiap menit): `php /path/ke/project/artisan schedule:run`

## Struktur Singkat
```
app/Http/Controllers/Api/V1/   Auth, Cycle, Guide, FeedLog, WaterLog, Harvest, Dashboard, Notification
app/Models/                    User, Cycle, Guide, FeedLog, WaterLog, Harvest
app/Services/                  CycleMetricsService (hitungan dashboard)
app/Notifications/             FeedReminder, HarvestReminder
config/guramismart.php         batas optimal air, target panen default, jam pengingat
routes/api.php, routes/console.php
```
