# Manual Postman API Quantum Hotel

Tanggal: 2026-04-20

## Tujuan

Dokumen ini dipakai untuk pengecekan API Quantum Hotel melalui Postman setelah jalur `/api/v1/...` diaktifkan.

## Base URL

Pilih salah satu sesuai kebutuhan:

```text
Local  : http://127.0.0.1:8001
Public : https://quantum.or.id
```

Semua endpoint API memakai prefix:

```text
/api/v1
```

Contoh:

```text
http://127.0.0.1:8001/api/v1/login
https://quantum.or.id/api/v1/login
```

## Header Standar

Untuk semua request API, gunakan header:

```text
Accept: application/json
Content-Type: application/json
```

Untuk endpoint yang sudah login, tambahkan:

```text
Authorization: Bearer {access_token}
```

## Alur Test Dasar

Urutan test yang disarankan:

1. Login API
2. Cek profile login
3. Cek list data
4. Coba insert / update / delete
5. Logout API

## 1. Login API

Method:

```text
POST /api/v1/login
```

Body JSON:

```json
{
  "username": "ANDREAS",
  "password": "ADR54321"
}
```

Expected response:

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token_type": "Bearer",
    "access_token": "TOKEN_DI_SINI",
    "expires_in": 43200,
    "user": "ANDREAS",
    "role": "..."
  }
}
```

Catatan:

- `access_token` dipakai untuk request berikutnya.
- Token saat ini berlaku 12 jam.

## 2. Cek User Login

Method:

```text
GET /api/v1/me
```

Header:

```text
Authorization: Bearer {access_token}
```

Expected response:

```json
{
  "success": true,
  "data": {
    "user": "ANDREAS",
    "role": "...",
    "issued_at": "...",
    "expires_at": "..."
  }
}
```

## 3. Test Master Data Kelas

### 3.1 List Kelas

Method:

```text
GET /api/v1/kelas
```

Optional query:

```text
?q=DLX
```

### 3.2 Tambah Kelas

Method:

```text
POST /api/v1/kelas
```

Body JSON:

```json
{
  "Kode": "TS01",
  "Nama": "TEST SUITE 01",
  "Rate1": 350000,
  "Depo1": 100000
}
```

Expected response:

```json
{
  "success": true,
  "message": "Data saved successfully",
  "data": {
    "Kode": "TS01",
    "Nama": "TEST SUITE 01"
  }
}
```

### 3.3 Update Kelas

Method:

```text
PUT /api/v1/kelas/TS01
```

Body JSON:

```json
{
  "Nama": "TEST SUITE 01 UPDATE",
  "Rate1": 450000,
  "Depo1": 150000
}
```

### 3.4 Delete Kelas

Method:

```text
DELETE /api/v1/kelas/TS01
```

Expected response:

```json
{
  "success": true,
  "message": "Data deleted successfully",
  "data": {
    "Kode": "TS01"
  }
}
```

## 4. Test Checkin

### 4.1 List Checkin

Method:

```text
GET /api/v1/checkin
```

Optional query:

```text
?search=ROOM
```

Expected response ringkas:

```json
{
  "success": true,
  "data": {
    "checkins": {
      "items": [],
      "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 0
      }
    }
  }
}
```

### 4.2 Insert Checkin

Method:

```text
POST /api/v1/checkin
```

Body JSON contoh minimum:

```json
{
  "GeneratedRegNo": "20260400880001",
  "CheckInDate": "2026-04-20",
  "CheckInTime": "14:00",
  "GuestName": "TEST GUEST API",
  "TypeOfCheckIn": "INDIVIDUAL",
  "NumberOfPerson": 2,
  "EstimationOut": "2026-04-21",
  "PaymentMethod": "CASH",
  "RoomCodeList": ["101"],
  "PackageCodeList": [""],
  "NominalList": ["0"],
  "BreakfastList": [2],
  "DetailKeyList": [""]
}
```

Catatan penting:

- Jangan test insert ke data live tanpa room test yang sudah disepakati.
- Jika room sedang dipakai, API akan menolak dengan pesan error JSON.

### 4.3 Update Checkin

Method:

```text
PUT /api/v1/checkin/{regNo2}
```

Gunakan payload yang sama seperti insert, lalu sesuaikan `regNo2` target.

### 4.4 Delete Checkin

Method:

```text
DELETE /api/v1/checkin/{regNo2}
```

## 5. Placeholder Endpoint

Endpoint berikut sudah tersedia sebagai placeholder API:

```text
GET /api/v1/room
GET /api/v1/checkout
GET /api/v1/guest-in-house
GET /api/v1/expected-departure
GET /api/v1/user
GET /api/v1/change-password
```

Expected response:

```json
{
  "success": true,
  "data": {
    "message": "..."
  }
}
```

## 6. Logout API

Method:

```text
POST /api/v1/logout
```

Header:

```text
Authorization: Bearer {access_token}
```

Expected response:

```json
{
  "success": true,
  "message": "Logout berhasil.",
  "data": null
}
```

## 7. Error yang Perlu Dicek

### Token tidak dikirim

Expected:

```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

Status:

```text
401
```

### Login gagal

Expected:

```json
{
  "success": false,
  "message": "Login failed"
}
```

Status:

```text
401
```

### Validasi gagal

Expected umum:

```json
{
  "success": false,
  "message": "..."
}
```

## 8. Saran Setup Postman

Disarankan buat collection:

```text
Quantum Hotel API
```

Variable collection:

```text
base_url = http://127.0.0.1:8001
token    = isi dari login
```

Contoh pemakaian:

```text
{{base_url}}/api/v1/login
{{base_url}}/api/v1/kelas
```

Authorization tab:

```text
Type  : Bearer Token
Token : {{token}}
```

## 9. Penutup

API baru ini dibuat agar testing dari Postman tidak tergantung session web. Untuk menu lain yang belum selesai, tinggal ikuti pola route `/api/v1/...`, bearer token middleware, lalu controller dengan `Request $request` dan helper response standar.
