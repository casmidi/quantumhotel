# Quantum Hotel - Project Checkpoint

Terakhir diperbarui: 2026-04-17

## Ringkasan Cepat

Project aktif ada di:

```text
C:\Users\midip\OneDrive\Documents\New project\quantum
```

Domain publik:

```text
https://quantum.or.id
```

Status terakhir:

- Domain `https://quantum.or.id` sudah `200 OK`.
- Cloudflare Tunnel sudah dipasang sebagai Windows Service.
- Laravel aktif diarahkan ke `127.0.0.1:8001`.
- Modul `/checkin` sudah tidak lagi placeholder.
- Halaman `/checkin` sudah bisa dibuka setelah login dan mengandung form `checkinForm`.
- Responsif mobile dasar sudah ditambahkan di layout dan halaman checkin.

## Cloudflare Tunnel

Service Windows:

```text
Service name : cloudflared
Display name : Cloudflared agent
Startup      : Automatic
Status       : Running
Run as       : LocalSystem
```

Command service yang benar:

```text
"C:\cloudflared\cloudflared.exe" --config "C:\cloudflared\config.yml" tunnel run quantum-tunnel
```

Config tunnel:

```text
C:\cloudflared\config.yml
```

Isi penting config:

```yaml
tunnel: quantum-tunnel
credentials-file: C:\cloudflared\cba8010b-c025-46f8-a8f9-0c623d1a53fa.json

ingress:
  - hostname: quantum.or.id
    service: http://127.0.0.1:8001
  - service: http_status:404
```

Catatan penting:

- Dulu tunnel mengarah ke `8000`.
- Port `8000` masih pernah dipegang proses Laravel lama dari `D:\laravel\quantum` dan sulit dimatikan.
- Karena itu origin dipindah ke `8001`.
- Jangan balikin tunnel ke `8000` kecuali proses lama sudah benar-benar bersih.

Command cek cepat:

```powershell
Get-Service cloudflared
C:\cloudflared\cloudflared.exe tunnel --origincert C:\cloudflared\cert.pem list
Invoke-WebRequest -UseBasicParsing -Uri https://quantum.or.id -TimeoutSec 30
netstat -ano | findstr ":8001"
```

## Laravel Server

Launcher:

```text
C:\cloudflared\quantum-web.ps1
```

Launcher sudah diarahkan ke:

```text
C:\Users\midip\OneDrive\Documents\New project\quantum
```

Port Laravel aktif:

```text
127.0.0.1:8001
```

Startup Windows masih memanggil:

```text
C:\Users\midip\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup\quantum-web.bat
```

Catatan:

- Cloudflare Tunnel sudah service.
- Laravel app masih perlu proses `php artisan serve --host=127.0.0.1 --port=8001`.
- Saat terakhir dicek, launcher bisa menyalakan Laravel di `8001`.
- Kalau domain `502`, cek dulu apakah Laravel di `8001` hidup.

## Database

Database:

```text
SQL Server
Server   : SERVERDEV
Database : BGF
```

Laravel `.env` memakai:

```text
DB_CONNECTION=sqlsrv
DB_HOST=SERVERDEV
DB_PORT=1433
DB_DATABASE=BGF
```

Perubahan penting di `config/database.php`:

- Blok `sqlsrv` duplikat dirapikan.
- Opsi SQL Server legacy ditambahkan:

```php
'encrypt' => env('DB_ENCRYPT', 'no'),
'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'true'),
```

Catatan SQL Server 2008:

- Beberapa command Laravel 12 seperti `artisan db:table` bisa gagal karena memakai fungsi SQL Server baru seperti `IIF`.
- Untuk cek schema, lebih aman pakai `sqlcmd`.

Contoh:

```powershell
sqlcmd -S SERVERDEV -U casmidi -P Laravel2026 -d BGF -W -s "," -Q "SELECT TOP 5 TABLE_NAME FROM INFORMATION_SCHEMA.TABLES ORDER BY TABLE_NAME"
```

## Modul Checkin

Route aktif:

```text
GET  /checkin
POST /checkin
POST /checkin/{regNo2}/update
GET  /checkin/{regNo2}/delete
```

File utama:

```text
app\Http\Controllers\CheckinController.php
resources\views\checkin\index.blade.php
resources\views\partials\crud-package-theme.blade.php
routes\web.php
app\Http\Controllers\Controller.php
resources\views\layouts\app.blade.php
```

Sumber adaptasi modul checkin lama:

```text
D:\laravel\quantum\app\Http\Controllers\CheckinController.php
D:\laravel\quantum\resources\views\checkin\index.blade.php
D:\laravel\quantum\resources\views\partials\crud-package-theme.blade.php
```

Tabel utama checkin menurut dokumen:

```text
DATA
DATA2
DataMove
Deposit
ROOM
```

Logika bisnis utama:

- Header transaksi disimpan di `DATA` dengan key `RegNo`.
- Detail kamar disimpan di `DATA2` dengan key `RegNo2 = RegNo + Kode kamar`.
- Satu `RegNo` bisa punya lebih dari satu `DATA2`.
- Simpan detail baru:
  - upsert `DATA`
  - insert `DATA2`
  - insert `DataMove`
  - insert `Deposit`
  - update `ROOM.Status2` menjadi `Occupied Clean`
- Hapus detail:
  - delete `DATA2`, `DataMove`, dan `Deposit`
  - jika tidak ada detail lain dengan `RegNo` yang sama, header `DATA` ikut dihapus

Perubahan performa yang sudah dilakukan:

- Directory checkin tidak lagi menarik semua record `DATA2`.
- `loadActiveCheckins()` dibatasi ke 80 record terbaru.
- Summary active memakai `countActiveCheckins()`.

Alasan:

- Tabel `DATA2` besar.
- Sebelum dibatasi, `/checkin` sempat timeout.

## Responsif Mobile

Perubahan utama:

- Layout global punya tombol menu mobile `data-widget="pushmenu"`.
- Sidebar tidak lagi menutup konten di mobile.
- Tabel dan grid besar bisa horizontal scroll.
- Checkin toolbar, badge, search, dan action button dibuat stack di layar kecil.
- Partial theme `crud-package-theme` sudah diubah agar table wrapper memakai horizontal scroll, bukan `overflow: hidden`.
- Checkin mobile mendapat breakpoint tambahan untuk layar kecil (`max-width: 420px`).
- Field checkin di mobile dibuat satu kolom, touch target lebih tinggi, dan note kosong disembunyikan agar form tidak terlalu panjang.

File:

```text
resources\views\layouts\app.blade.php
resources\views\checkin\index.blade.php
```

Verifikasi terakhir:

```text
/dashboard  -> 200, mobile menu ada
/kelas      -> 200, mobile menu ada
/checkin    -> 200, checkinForm ada, mobile menu ada
domain      -> https://quantum.or.id 200 OK
```

Catatan:

- `/room` di workspace aktif masih placeholder pendek.
- Ada versi RoomController dan view lengkap di `D:\laravel\quantum` bila nanti mau diadaptasi.

## Verifikasi Terakhir

Command yang sudah lolos:

```powershell
C:\xampp\php\php.exe -l app\Http\Controllers\Controller.php
C:\xampp\php\php.exe -l app\Http\Controllers\CheckinController.php
C:\xampp\php\php.exe artisan route:list --path=checkin
C:\xampp\php\php.exe artisan view:clear
C:\xampp\php\php.exe artisan view:cache
```

Domain/public check:

```text
https://quantum.or.id -> 200 OK
https://quantum.or.id/checkin -> 200 setelah login
```

Login test yang pernah dipakai untuk verifikasi:

```text
username: ANDREAS
password: ADR54321
```

## Kondisi Git / Working Tree

Repo sedang punya banyak perubahan lokal sebelum dan selama pekerjaan ini.

Jangan lakukan:

```text
git reset --hard
git checkout -- .
```

Perubahan yang sudah ada sebelum pekerjaan ini antara lain:

```text
app/Http/Controllers/AuthController.php
app/Http/Controllers/KelasController.php
bootstrap/app.php
composer.json
composer.lock
resources/views/dashboard.blade.php
resources/views/kelas/index.blade.php
routes/web.php
app/Events/
config/broadcasting.php
routes/channels.php
```

Perubahan yang ditambahkan untuk pekerjaan terakhir:

```text
app/Http/Controllers/CheckinController.php
resources/views/checkin/index.blade.php
resources/views/partials/crud-package-theme.blade.php
app/Http/Controllers/Controller.php
config/database.php
resources/views/layouts/app.blade.php
routes/web.php
C:\cloudflared\config.yml
C:\cloudflared\quantum-web.ps1
Windows Service: cloudflared
```

## Lanjut Coding Checkin

Prioritas lanjut yang disarankan:

1. Validasi alur simpan checkin tanpa membuat data palsu sembarangan.
2. Kalau perlu test insert, gunakan satu room test yang disepakati user.
3. Cek payload insert `DATA`, `DATA2`, `DataMove`, dan `Deposit` terhadap schema live.
4. Rapikan UX edit/hapus detail checkin.
5. Tambahkan guard agar room yang occupied tidak bisa dipakai ulang.
6. Setelah checkin stabil, lanjut adaptasi modul Room dari `D:\laravel\quantum`.

## Instruksi Hemat Context Untuk Thread Baru

Jika lanjut di thread baru, cukup beri pesan:

```text
Lanjut project Quantum Hotel. Baca PROJECT-CHECKPOINT.md dulu, jangan baca file besar kecuali perlu. Fokus lanjut coding checkin dari status terakhir.
```

Aturan hemat:

- Jangan baca ulang seluruh `storage/logs/laravel.log` kecuali ada error baru.
- Jangan dump seluruh file Blade besar.
- Pakai `Select-String` atau baca potongan file spesifik.
- Untuk schema SQL Server, pakai query `INFORMATION_SCHEMA.COLUMNS` via `sqlcmd`.
- Jangan menyentuh perubahan lokal yang tidak terkait checkin.
