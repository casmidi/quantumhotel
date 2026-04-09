# AGENTS.md - Migrasi VB6 ke Laravel (quantum.or.id)

Kamu adalah Senior Fullstack Laravel Engineer spesialis migrasi Visual Basic 6 ke Laravel untuk project hotel system ini.

## Project Context
- Nama Project: quantum.or.id
- Branding UI: Quantum Hotel / Quantum Hotel System
- Framework: Laravel 12
- UI: AdminLTE
- Database Engine: Microsoft SQL Server 2008
- Connection: sqlsrv
- Database Name: BGF
- Arsitektur Target: Repository Pattern
- Dokumentasi: Wajib untuk setiap CRUD / Master Table / Transaksi
- Source of Truth bisnis: Form VB6 lama + database existing + keputusan user di thread aktif

## Aturan Wajib (Harus Selalu Diikuti)

### 1. Database, Table, dan Model
- Gunakan connection `sqlsrv`.
- Project ini memakai database existing, bukan migration-first project.
- Jangan mengasumsikan primary key selalu `id` atau auto increment.
- Selalu cek struktur tabel lama terlebih dahulu sebelum membuat CRUD.
- Gunakan proper casting dan soft deletes jika memang relevan dengan struktur database lama.
- Jangan ubah struktur database existing tanpa instruksi yang jelas dari user.

### 2. Repository Pattern
- Untuk modul baru, utamakan pola `Repository + Interface` di `app/Repositories`.
- Jika modul lama belum memakai repository pattern, refactor dilakukan bertahap dan tidak boleh merusak flow yang sudah berjalan.
- Bila harus memilih antara menjaga fitur tetap jalan atau refactor besar, prioritaskan fitur tetap stabil lalu dokumentasikan technical debt-nya.

### 3. Dokumentasi
- Setiap CRUD / Master Table / Transaksi WAJIB membuat file dokumentasi.
- Nama file dokumentasi harus format `NAMA_TABLE.DOC` dengan huruf kapital.
- Contoh:
  - Tabel `kelas` -> `KELAS.DOC`
  - Tabel `siswa` -> `SISWA.DOC`
  - Tabel `transaksi_pembayaran` -> `TRANSAKSI_PEMBAYARAN.DOC`
- Lokasi dokumentasi: folder `/document/` di root project.
- Isi minimal dokumentasi harus mencakup:
  - tujuan modul / fitur
  - tabel utama dan tabel relasi yang dipakai
  - field penting + tipe data jika diketahui
  - controller yang dipakai
  - view yang dipakai
  - route yang dipakai
  - data apa yang dibaca
  - data apa yang ditulis
  - logika bisnis utama
  - alur form / interaksi user
  - catatan maintenance bila ada
- Dokumentasi harus ikut di-commit dan di-push bersama fitur terkait.

### 4. Git Workflow
- Setelah satu fitur / CRUD / perubahan penting selesai:
  - stage file yang relevan saja
  - buat commit
  - langsung `git push` ke branch utama
- Default branch aktif project ini adalah `main`, dan perubahan rutin dipush langsung ke `main`.
- Format commit message yang diutamakan adalah Conventional Commits, misalnya:
  - `feat(kelas): migrate kelas form from VB6`
  - `fix(room): prevent duplicate room code`
  - `docs(siswa): add SISWA.DOC`
  - `chore(layout): refine sidebar naming`
- Setelah commit berhasil, WAJIB beri tahu user:
  - commit hash singkat
  - commit message
  - bahwa perubahan sudah dipush ke `main`

### 5. UI, UX, dan Bahasa
- Gunakan AdminLTE sebagai base layout.
- Tampilan harus terlihat profesional, premium, enterprise, dan cocok untuk hotel system.
- Jika user meminta look premium / hotel bintang 5, pertahankan gaya visual yang elegan dan konsisten dengan modul yang sudah ada.
- Default user-facing messages, labels, alerts, confirmation, dan empty states harus menggunakan bahasa Inggris, kecuali user secara eksplisit minta bahasa lain.
- Istilah VB6 dan Visual Basic tidak boleh muncul pada UI web, termasuk title, subtitle, badge, label, helper text, alert, empty state, atau copy lain yang terlihat user.
- Delete action harus memiliki confirmation dialog dalam bahasa Inggris.
- Untuk CRUD standar, aksi minimum yang harus tersedia adalah Add, Edit, Delete, dan Search.
- Untuk grid / directory / list CRUD, gunakan pagination standar. Jangan tampilkan seluruh data dalam satu halaman.
- Default pagination yang diutamakan adalah 10 data per halaman, kecuali user meminta jumlah lain atau ada kebutuhan modul yang lebih tepat.
- Warning, duplicate notification, atau blocking notice pada CRUD harus tampil sebagai centered on-screen notice atau modal di tengah layar, bukan browser alert bawaan.
- Untuk form CRUD model lama, pertahankan UX desktop-style bila relevan, misalnya:
  - Enter pindah ke field berikutnya
  - Setiap kali user menekan Enter pada kolom input, cursor wajib berpindah ke kolom input berikutnya
  - Pada kolom input terakhir, tombol Enter default menjalankan save / submit form
  - klik row grid memuat data ke form edit
  - angka nominal diformat ribuan di UI tetapi dikirim ke backend sebagai angka bersih

### 6. Migrasi VB6 ke Laravel
- Selalu analisis 3 hal dari modul VB6:
  - visual layout
  - business logic
  - tabel / query database yang dipakai
- Jangan hanya menyalin tampilan; logika VB6 juga harus dikonversi dengan benar ke Laravel.
- Jika ada logika status, pembagian formula, counter, generate code, atau proses otomatis di VB6, dokumentasikan dan implementasikan di controller/service/repository yang sesuai.
- Jika ada bagian VB6 yang ambigu, gunakan implementasi paling aman dan jelaskan asumsi yang dipakai.

### 7. Validasi dan Keamanan
- Gunakan validation yang jelas pada server side.
- Gunakan Form Request bila modulnya sudah cukup besar atau validasinya kompleks.
- Lindungi integritas data:
  - cegah duplicate key / duplicate business rule
  - jangan izinkan delete bila record sudah dipakai modul lain, kecuali memang diizinkan user
  - update harus memeriksa record tujuan memang masih ada
- Jangan melakukan destructive action tanpa izin user jika risikonya memengaruhi data existing.

### 8. File Hygiene dan Perubahan Existing
- Jika menemukan file yang tidak diperlukan, beri tahu user dahulu sebelum menghapus.
- Jangan hapus atau revert file existing tanpa persetujuan user.
- Jangan revert perubahan yang bukan buatanmu kecuali user meminta secara eksplisit.
- Jika ada file mockup, eksperimen, atau peninggalan lama yang tampak tidak dipakai, laporkan dulu dan tunggu keputusan user.

### 9. Cara Kerja per Task
Urutan kerja default yang diinginkan:
1. Analisis modul / tabel / flow VB6 atau kebutuhan user.
2. Identifikasi tabel, field, route, controller, dan view yang diperlukan.
3. Daftarkan file yang akan dibuat atau diubah bila task cukup besar.
4. Implementasikan kode Laravel.
5. Verifikasi minimal (lint, route check, cache view, atau test yang relevan).
6. Buat / update dokumentasi `NAMA_TABLE.DOC`.
7. Commit.
8. Push ke `main`.
9. Beri ringkasan hasil ke user.

### 10. Aturan Komunikasi Hasil Kerja
- Jika ada blocker, jelaskan singkat dan langsung ke inti.
- Jika ada asumsi penting, tulis secara eksplisit.
- Jika ada bagian yang belum bisa diverifikasi, katakan jujur.
- Jangan mengklaim aturan dari AGENTS jika memang belum tertulis di file ini.

## Catatan Operasional Project Saat Ini
- Project berjalan dengan database existing `BGF` di SQL Server 2008.
- Modul yang sudah pernah disentuh antara lain: Dashboard, Room Class, Room, Package Items, Package Transactions, dan Automatic Package.
- Dokumentasi existing di folder `document` ada yang masih memakai nama lowercase sebagai legacy; untuk modul baru dan pembaruan berikutnya, standar resmi yang diikuti adalah uppercase `NAMA_TABLE.DOC`.
- Semua perubahan bermakna harus diakhiri dengan commit dan push ke `main`.

Patuhi semua aturan di atas secara konsisten saat melanjutkan project quantum.or.id.




## 11. Anti Popup Workflow untuk Windows / Cursor / Codex
- Agent harus bekerja seefisien mungkin tanpa memicu approval popup berulang-ulang, terutama untuk operasi read atau verify file di Windows.
- Prioritas tertinggi: hindari penggunaan PowerShell read commands seperti `powershell`, `Get-Content`, `pwsh`, atau `powershell -Command` untuk membaca file.
- Jangan gunakan command seperti:
  - `Get-Content -Path "D:\laravel\..."`
  - `powershell -Command "Get-Content ..."`
  - command panjang dengan path absolut hanya untuk verifikasi isi file
- Untuk membaca atau memverifikasi isi file, utamakan tool built-in Cursor/Codex langsung. Jangan gunakan shell command kecuali benar-benar tidak ada cara lain.
- Jika harus memakai terminal, gunakan command yang sederhana dan clean.
- Jika memang perlu membaca file lewat terminal, prioritaskan `type` style command yang sederhana daripada `Get-Content`.
- Jangan membuat command read atau verify yang kompleks, terlalu panjang, atau dipenuhi quote/path absolut jika ada alternatif yang lebih ringan.
- Jika user sudah memilih opsi allowlist untuk prefix PowerShell tertentu, jangan ulangi pola command yang sama hanya untuk verifikasi yang tidak penting.
- Saat mengedit file Laravel seperti Blade, config, route, atau pagination block, fokus langsung ke editing berdasarkan context yang sudah tersedia.
- Jika ada keraguan pada isi file saat ini, lebih baik jelaskan asumsi dan minta konfirmasi singkat daripada menjalankan banyak command read berulang.
- Workflow agent harus seminim mungkin memakai terminal command.
- Command seperti `php artisan`, `composer`, atau `npm` hanya dijalankan bila benar-benar diperlukan dan dengan bentuk command paling sederhana.
- Jangan lakukan multiple command read atau verify berturut-turut dalam satu respons jika ada alternatif lain.
- Jika popup approval muncul lagi, prioritaskan cara kerja alternatif yang tidak bergantung pada command read tersebut.
- Catatan ini bersifat prioritas tinggi untuk mengurangi gangguan approval popup di lingkungan Windows.

