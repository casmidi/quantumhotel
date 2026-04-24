# Manual Pemakaian Modul Night Audit

Project: Quantum Hotel System  
Modul: Night Audit  
URL: `/night-audit`  
Tanggal dokumen: 2026-04-24  
Standar operasional: Hotel bintang 5 ke atas

## 1. Tujuan Modul

Modul Night Audit digunakan untuk menutup satu business date hotel secara terkontrol. Modul ini membantu Night Auditor, Front Office, Housekeeping, Duty Manager, dan Finance memastikan data operasional harian sudah benar sebelum hotel masuk ke tanggal bisnis berikutnya.

Modul ini mencakup kontrol:

- Status kamar dan tamu in-house.
- Arrival, departure, stay-over, walk-in, complimentary, dan house use.
- Preview room revenue.
- Rekonsiliasi kasir, deposit, refund, dan metode pembayaran.
- Housekeeping discrepancy.
- Checklist night audit.
- Adjustment audit.
- Close audit dan approval.

## 2. Role Pengguna

### Night Auditor

Pengguna utama modul. Bertugas menjalankan preview, membuat batch audit, memeriksa semua tab, mengisi checklist, mencatat adjustment, dan melakukan close audit.

### Front Office Supervisor

Memastikan status registrasi, check-in, check-out, no-show, stay-over, dan room rack sudah benar.

### Housekeeping Supervisor

Memastikan status kamar sesuai kondisi lapangan, terutama vacant dirty, occupied clean, occupied dirty, out of order, dan discrepancy.

### Duty Manager

Meninjau exception kritis dan memberi approval operasional setelah audit ditutup.

### Financial Controller / Accounting

Meninjau revenue, kasir, deposit, city ledger, OTA, company billing, dan approval final finance.

## 3. Syarat Sebelum Menjalankan Night Audit

Sebelum membuka modul Night Audit, pastikan:

- Semua check-in hari berjalan sudah selesai dimasukkan.
- Semua check-out hari berjalan sudah diproses.
- Semua room move sudah diperbarui.
- Semua pembayaran, deposit, refund, dan koreksi kasir sudah dicatat.
- Front Office sudah mencocokkan guest in-house list.
- Housekeeping sudah mengirim status kamar terakhir.
- Outlet terkait, seperti restaurant, minibar, laundry, spa, dan telephone, sudah mengirim posting transaksi jika modul/interface tersedia.

Night Audit sebaiknya tidak ditutup bila masih ada:

- Kamar occupied tanpa guest aktif.
- Guest aktif tetapi status kamar vacant.
- Complimentary atau house use tanpa approval.
- Cashier variance belum dijelaskan.
- Adjustment belum jelas alasan dan approval-nya.
- Checklist critical masih `Pending` atau `Blocked`.

## 4. Membuka Modul

1. Login ke Quantum Hotel System.
2. Pilih menu **Night Audit** di sidebar.
3. Sistem akan membuka halaman `/night-audit`.
4. Pilih **Business Date** yang akan diaudit.
5. Klik **Preview**.

Preview hanya membaca data live dan belum menyimpan batch audit.

## 5. Memahami Business Date

Business date adalah tanggal operasional hotel yang ditutup oleh Night Audit. Tanggal ini tidak selalu sama dengan jam komputer saat proses audit dijalankan.

Contoh:

- Audit dijalankan tanggal 25 April pukul 01:00.
- Business date yang ditutup bisa tetap 24 April.

Gunakan tanggal operasional yang sesuai dengan kebijakan hotel.

## 6. Preview Night Audit

Preview menampilkan kondisi live dari sistem sebelum batch dibuat.

Gunakan preview untuk memeriksa:

- Occupancy.
- Total occupied room.
- In-house guest.
- Arrival dan departure.
- Room revenue.
- Cash dan non-cash receipt.
- Complimentary dan house use.
- Jumlah exception.

Jika preview masih menunjukkan masalah besar, selesaikan dulu di modul terkait seperti Check-in, Check-out, Room, atau Cashier.

## 7. Start Batch

Klik **Start Batch** jika data preview sudah siap diaudit.

Saat Start Batch dilakukan, sistem membuat snapshot ke tabel Night Audit:

- `night_audit_batches`
- `night_audit_room_snapshots`
- `night_audit_revenue_lines`
- `night_audit_cashier_summaries`
- `night_audit_housekeeping_exceptions`
- `night_audit_checklists`
- `night_audit_approvals`

Setelah batch dibuat, Night Auditor bekerja pada data batch tersebut.

Status awal batch adalah `Draft`.

## 8. Refresh Batch

Tombol **Refresh** digunakan untuk mengambil ulang data terbaru dari transaksi live ke batch audit.

Gunakan Refresh bila:

- Ada check-in/check-out yang baru diperbaiki.
- Status kamar baru diperbarui.
- Kasir baru menyelesaikan input pembayaran.
- Ada transaksi atau koreksi yang baru dimasukkan.

Refresh hanya tersedia saat batch masih `Draft`.

Jangan gunakan Refresh setelah audit dianggap final kecuali memang ada koreksi resmi yang harus masuk ke batch.

## 9. Daily Control Summary

Bagian ini adalah ringkasan utama audit.

### Occupancy

Persentase kamar terjual terhadap kamar operasional.

Periksa apakah angka ini masuk akal dibanding laporan Front Office.

### Occupied

Jumlah kamar occupied dibanding total kamar.

Jika jumlah berbeda dengan room rack manual, cek tab Rooms dan Discrepancy.

### In House

Jumlah record tamu/kamar aktif dari data registrasi.

Untuk group check-in, satu registration dapat memiliki lebih dari satu detail kamar.

### Arrival / Departure

Jumlah kedatangan dan keberangkatan pada business date.

Gunakan untuk mencocokkan:

- Arrival list.
- Departure list.
- No-show.
- Stay-over.

### Room Revenue

Estimasi revenue kamar dari tamu aktif.

Nilai ini digunakan sebagai kontrol awal. Jika hotel memakai posting folio final di modul lain, cocokkan lagi dengan laporan revenue final.

### Total Receipt

Total penerimaan kasir/deposit yang terbaca pada tanggal audit.

### Cash dan Non Cash

Memisahkan penerimaan tunai dan non-tunai untuk kontrol cashier closing.

### Exceptions

Jumlah masalah yang ditemukan sistem.

Format tampilan:

```text
Critical/High / Total Exception
```

Jika angka kiri lebih dari 0, audit belum boleh ditutup tanpa review supervisor.

## 10. Tab Overview

Tab Overview berisi tiga kontrol utama.

### Approval Flow

Menampilkan status approval:

- Night Auditor.
- Duty Manager.
- Financial Controller.

Approval flow membantu memastikan audit tidak ditutup sepihak.

### Revenue Mix

Menampilkan gross revenue dan city ledger.

Gunakan untuk mengecek proporsi revenue cash, non-cash, OTA, company, dan travel agent.

### Control Flags

Menampilkan indikator seperti walk-in dan critical/high exception.

Walk-in harus diperiksa karena sering terkait deposit, identitas tamu, rate approval, atau pembayaran langsung.

## 11. Tab Rooms

Tab Rooms menampilkan snapshot kamar in-house.

Kolom penting:

- `Room`: kode kamar dan kelas kamar.
- `Guest`: nama tamu.
- `RegNo / RegNo2`: nomor registration dan detail kamar.
- `Segment`: tipe check-in atau market segment.
- `Payment`: metode pembayaran.
- `Package`: package/rate plan.
- `Stay`: jumlah malam menginap.
- `Rate`: net room rate.
- `HK`: status housekeeping.
- `Flag`: tanda risiko.

### Pemeriksaan wajib di tab Rooms

Pastikan:

- Semua kamar occupied memiliki nama tamu.
- Payment method sudah benar.
- Package/rate sudah sesuai reservasi.
- Complimentary dan house use punya approval.
- Stay night tidak aneh.
- Expected checkout tidak lewat tanpa status stay-over.
- Kamar group check-in tampil sesuai jumlah kamar.

### Risk Flag

Contoh risk flag:

- `OVERSTAY`: expected checkout sudah lewat business date.
- `APPROVAL`: complimentary atau house use perlu approval.
- `ZERO_RATE_APPROVAL`: room revenue nol dan perlu review.

Jika ada risk flag, Night Auditor wajib menulis catatan atau meminta approval sesuai SOP hotel.

## 12. Tab Revenue

Tab Revenue menampilkan preview revenue yang dibaca dari data transaksi.

Kolom penting:

- Date.
- Department.
- Revenue Code.
- Room.
- Guest.
- Description.
- Debit.
- Credit.
- Net.
- Status atau risk flag.

### Pemeriksaan wajib di tab Revenue

Pastikan:

- Room charge muncul untuk semua kamar occupied yang chargeable.
- Complimentary dan house use tidak dihitung sebagai revenue tanpa approval.
- Discount besar punya alasan.
- Package rate sesuai kontrak.
- Posting outlet sudah lengkap bila digunakan.
- Tidak ada revenue nol untuk kamar yang seharusnya charge.

Jika revenue tidak cocok, lakukan investigasi sebelum close audit.

## 13. Tab Cashier

Tab Cashier digunakan untuk rekonsiliasi pembayaran.

Kolom penting:

- Cashier.
- Shift.
- Payment.
- Receipt.
- Refund.
- Cash Drop.
- Variance.
- Transaction Count.
- Status.

### Pemeriksaan wajib di tab Cashier

Pastikan:

- Cash receipt sesuai uang fisik.
- Cash drop sudah disetor dan dicatat.
- Refund memiliki bukti dan approval.
- Non-cash sesuai settlement EDC/payment gateway.
- OTA dan company billing masuk kategori yang benar.
- Tidak ada variance tanpa penjelasan.

Jika ada selisih kasir, status checklist cashier tidak boleh `Done`.

## 14. Adjustment Audit

Adjustment digunakan untuk mencatat koreksi audit.

Contoh adjustment:

- Koreksi room charge.
- Koreksi package.
- Koreksi discount.
- Koreksi minibar/laundry/other revenue.
- Koreksi deposit atau refund.

Field adjustment:

- Room.
- RegNo.
- Department.
- Amount.
- Reason.
- Description.

### Aturan adjustment

- Isi reason dengan jelas.
- Jangan menggunakan alasan umum seperti `LAIN-LAIN` tanpa detail.
- Nilai minus hanya digunakan bila SOP mengizinkan koreksi pengurang.
- Adjustment harus mendapat approval sesuai nilai dan kebijakan hotel.
- Adjustment hanya bisa dicatat saat batch masih `Draft`.

## 15. Tab Discrepancy

Tab Discrepancy menampilkan perbedaan data PMS/registration dengan status kamar.

Contoh exception:

### PMS_ROOM_STATUS_MISMATCH

DATA2 menunjukkan kamar occupied, tetapi ROOM masih vacant atau check-out.

Tindakan:

1. Cek registration tamu.
2. Cek status kamar di room rack.
3. Konfirmasi ke Front Office.
4. Perbaiki status kamar.
5. Refresh batch.

### PHANTOM_OCCUPIED

ROOM terlihat occupied tetapi tidak ada guest aktif di DATA2.

Tindakan:

1. Cek apakah tamu sudah check-out.
2. Cek apakah room move belum sinkron.
3. Konfirmasi ke Housekeeping.
4. Perbaiki status kamar.
5. Refresh batch.

### VACANT_DIRTY_PENDING

Kamar vacant dirty masih perlu follow-up housekeeping.

Tindakan:

1. Konfirmasi status pembersihan.
2. Jika sudah selesai, ubah status menjadi clean/ready sesuai SOP hotel.
3. Jika belum selesai, catat sebagai pending housekeeping.

## 16. Severity Exception

### Critical

Masalah yang bisa membuat audit tidak valid, seperti guest active tetapi status kamar salah.

Audit tidak boleh ditutup tanpa penyelesaian atau approval Duty Manager.

### High

Masalah penting yang berdampak ke operasional atau revenue.

Harus direview supervisor sebelum close.

### Medium

Masalah operasional yang masih bisa ditutup dengan catatan, misalnya vacant dirty pending.

### Low

Masalah minor atau informasi tambahan.

## 17. Tab Checklist

Checklist adalah daftar kontrol kerja Night Audit.

Status checklist:

- `Pending`: belum dikerjakan.
- `Ready`: siap diproses atau sudah tidak ada blocking issue.
- `Done`: sudah selesai.
- `Blocked`: tertahan karena ada masalah.
- `Waived`: dilewati dengan alasan dan approval.

### Checklist standar

1. Verify arrivals, no-show, walk-in, and stay-over room rack.
2. Reconcile in-house list with active registration and room status.
3. Resolve room status discrepancy and vacant dirty follow up.
4. Close cashier shift, deposit, refund, and cash drop reconciliation.
5. Validate room revenue, complimentary, house-use, discount, and package rate.
6. Confirm outlet/interface postings.
7. Review city ledger, OTA, company, travel agent, and credit limit exposure.
8. Archive night audit report, folio exception, and system backup reference.
9. Duty manager review for critical exceptions.
10. Financial controller approval for closed business date.

Checklist critical sebaiknya tidak ditandai `Done` bila bukti atau review belum lengkap.

## 18. Close Audit

Close Audit menutup batch dari status `Draft` menjadi `Closed`.

Sebelum klik Close Audit, pastikan:

- Critical/high exception sudah selesai atau disetujui Duty Manager.
- Checklist utama sudah `Done` atau `Waived` dengan alasan.
- Cashier variance sudah nol atau dijelaskan.
- Adjustment sudah lengkap.
- Revenue sudah direview.
- Room snapshot sudah sesuai guest in-house.

Setelah Close Audit:

- Snapshot dianggap final.
- Batch siap masuk approval.
- Refresh tidak lagi tersedia.

## 19. Approve Audit

Approve digunakan untuk approval final setelah batch `Closed`.

Approval wajib dilakukan oleh role yang berwenang sesuai kebijakan hotel.

Setelah audit `Approved`:

- Data batch tidak boleh diubah.
- Koreksi berikutnya harus melalui prosedur adjustment atau audit correction terpisah.
- Dokumen audit dapat disimpan sebagai arsip harian.

## 20. Alur Kerja Harian yang Disarankan

### Sebelum tengah malam

1. Front Office menyelesaikan check-in dan check-out.
2. Kasir menyelesaikan posting pembayaran dan deposit.
3. Housekeeping update status kamar.
4. Outlet mengirim transaksi terakhir.

### Saat Night Audit dimulai

1. Buka `/night-audit`.
2. Pilih business date.
3. Klik Preview.
4. Review Daily Control Summary.
5. Jika preview masuk akal, klik Start Batch.

### Review batch

1. Review tab Rooms.
2. Review tab Revenue.
3. Review tab Cashier.
4. Review tab Discrepancy.
5. Isi Checklist.
6. Catat Adjustment bila perlu.
7. Refresh Batch jika ada koreksi di modul lain.

### Penutupan

1. Pastikan checklist critical selesai.
2. Klik Close Audit.
3. Duty Manager melakukan review.
4. Finance melakukan approval final.
5. Simpan laporan sesuai arsip hotel.

## 21. SOP Jika Ada Masalah

### Ada error occupancy

Tindakan:

1. Cocokkan jumlah occupied dengan dashboard dan room rack.
2. Buka tab Rooms.
3. Cek kamar yang statusnya tidak sesuai.
4. Koreksi di modul terkait.
5. Klik Refresh Batch.

### Ada kamar occupied tanpa tamu

Tindakan:

1. Cek apakah tamu sudah checkout.
2. Cek room move.
3. Koreksi status ROOM.
4. Refresh Batch.

### Ada tamu aktif tetapi kamar vacant

Tindakan:

1. Cek DATA2/check-in.
2. Pastikan kamar belum checkout.
3. Update status kamar.
4. Refresh Batch.

### Ada cash variance

Tindakan:

1. Hitung ulang cash fisik.
2. Cocokkan dengan payment record.
3. Cek refund dan void.
4. Minta approval supervisor bila variance tetap ada.
5. Catat remarks pada checklist.

### Revenue tidak cocok

Tindakan:

1. Cek room rate dan package.
2. Cek discount.
3. Cek complimentary/house use.
4. Cek outlet posting.
5. Catat adjustment bila perlu.

## 22. Catatan Kontrol Internal

Untuk standar hotel bintang 5 ke atas:

- Night Audit tidak boleh hanya menjadi proses tekan tombol.
- Setiap exception harus punya owner department.
- Complimentary dan house use harus ada approval.
- Cashier variance harus tercatat.
- Adjustment harus punya reason dan approval.
- Checklist critical harus menjadi kontrol utama sebelum close.
- Audit yang sudah approved tidak boleh diedit langsung.

## 23. Data yang Disimpan Sistem

### night_audit_batches

Header batch audit per business date.

Menyimpan status audit, summary occupancy, revenue, receipt, exception, dan approval utama.

### night_audit_room_snapshots

Snapshot kamar dan tamu in-house saat audit.

### night_audit_revenue_lines

Preview revenue dan kontrol posting.

### night_audit_cashier_summaries

Ringkasan kasir, payment type, receipt, refund, cash drop, dan variance.

### night_audit_housekeeping_exceptions

Daftar discrepancy room status dan housekeeping.

### night_audit_checklists

Checklist operasional night audit.

### night_audit_adjustments

Catatan koreksi audit.

### night_audit_approvals

Approval trail untuk Night Auditor, Duty Manager, dan Finance.

## 24. Batasan Saat Ini

Pada versi awal ini, modul Night Audit sudah berfungsi sebagai:

- Control dashboard.
- Snapshot audit.
- Checklist.
- Discrepancy monitor.
- Cashier summary.
- Adjustment log.
- Close dan approval flow.

Modul belum melakukan posting final room charge ke folio legacy secara otomatis. Jika hotel membutuhkan auto-posting room charge, fitur tersebut harus diaktifkan sebagai tahap lanjutan setelah rule revenue, tax, service, package, dan folio posting dipastikan sesuai SOP hotel.

## 25. Rekomendasi Lanjutan

Fitur lanjutan yang disarankan:

- Export Night Audit Report ke PDF/Excel.
- Auto-post daily room charge.
- Lock business date setelah approval.
- Interface revenue outlet.
- Cashier drop form per user/shift.
- Approval matrix berdasarkan limit adjustment.
- Audit correction untuk batch yang sudah approved.
- Daily manager report.

