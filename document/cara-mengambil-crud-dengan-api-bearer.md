# Cara Mengambil CRUD Dengan API Bearer

Dokumen ini menjelaskan cara mengambil data CRUD memakai API Bearer Token di aplikasi Quantum Laravel. Contoh utama memakai CRUD **Kelas** dengan endpoint:

- Login token: `POST /api/v1/login`
- Ambil data Kelas: `GET /api/v1/kelas`
- Tambah data Kelas: `POST /api/v1/kelas`
- Update data Kelas: `PUT /api/v1/kelas/{kode}`
- Hapus data Kelas: `DELETE /api/v1/kelas/{kode}`

## 1. Konsep Alur Bearer Token

Bearer Token tidak diisi sembarang. Token harus didapat dari proses login API terlebih dahulu.

Alurnya:

1. Client mengirim username dan password user aplikasi SANDI ke `/api/v1/login`.
2. Laravel mengecek user tersebut di tabel `SANDI`.
3. Jika benar, Laravel membuat token acak.
4. Token disimpan di cache file Laravel.
5. Client menyimpan token dari response.
6. Saat memanggil endpoint CRUD, client mengirim header `Authorization: Bearer {token}`.
7. Middleware `api.token` membaca token tersebut.
8. Jika token valid, request diteruskan ke controller CRUD.
9. Controller mengembalikan JSON.

## 2. Route API Yang Digunakan

File:

`routes/api.php`

Kode route:

```php
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'apiLogin']);

    Route::middleware('api.token')->group(function () {
        Route::get('/me', [AuthController::class, 'apiMe']);
        Route::post('/logout', [AuthController::class, 'apiLogout']);

        Route::get('/kelas', [KelasController::class, 'index']);
        Route::post('/kelas', [KelasController::class, 'store']);
        Route::match(['put', 'patch'], '/kelas/{kode}', [KelasController::class, 'update']);
        Route::delete('/kelas/{kode}', [KelasController::class, 'destroy']);
    });
});
```

Penjelasan:

- `/api/v1/login` tidak memakai middleware `api.token`, karena endpoint ini dipakai untuk mendapatkan token.
- `/api/v1/kelas` berada di dalam `Route::middleware('api.token')`, jadi wajib memakai Bearer Token.
- Jika request ke `/api/v1/kelas` tidak membawa token valid, response akan `401 Unauthenticated`.

## 3. Login API Untuk Mendapatkan Token

File:

`app/Http/Controllers/AuthController.php`

Method:

```php
public function apiLogin(Request $request)
{
    $username = trim((string) $request->input('username'));
    $password = trim((string) $request->input('password'));

    if ($username === '' || $password === '') {
        return $this->respondError($request, 'Username dan password wajib diisi.', 422);
    }

    $user = $this->findUserByKode($username);

    if (!$user || trim((string) $user->Passw) !== $password) {
        return $this->respondError($request, 'Login failed', 401);
    }

    $plainToken = Str::random(64);
    $tokenKey = $this->apiTokenCacheKey($plainToken);
    $now = now();
    $payload = [
        'user' => trim((string) $user->Kode),
        'role' => trim((string) $user->Nama),
        'issued_at' => $now->toIso8601String(),
        'expires_at' => $now->copy()->addHours(self::API_TOKEN_TTL_HOURS)->toIso8601String(),
    ];

    Cache::store('file')->put($tokenKey, $payload, now()->addHours(self::API_TOKEN_TTL_HOURS));

    return $this->respondAfterMutation($request, '/api/me', 'Login berhasil.', [
        'token_type' => 'Bearer',
        'access_token' => $plainToken,
        'expires_in' => self::API_TOKEN_TTL_HOURS * 3600,
        'user' => $payload['user'],
        'role' => $payload['role'],
    ], 201);
}
```

Penjelasan detail:

- `$request->input('username')` mengambil username dari body JSON.
- `$request->input('password')` mengambil password dari body JSON.
- `findUserByKode($username)` mencari user di tabel `SANDI`.
- Password yang dibandingkan adalah field `Passw` di tabel `SANDI`.
- `Str::random(64)` membuat token acak sepanjang 64 karakter.
- Token asli dikirim ke client sebagai `access_token`.
- Token tidak disimpan mentah-mentah sebagai key cache. Token di-hash dulu.

Method pembuat key cache:

```php
private function apiTokenCacheKey(string $plainToken): string
{
    return 'api_token:' . hash('sha256', $plainToken);
}
```

Artinya token:

```text
abc123
```

akan disimpan dengan key cache:

```text
api_token:{hasil_hash_sha256}
```

## 4. Contoh Request Login

Endpoint:

```http
POST /api/v1/login
```

Headers:

```http
Accept: application/json
Content-Type: application/json
```

Body:

```json
{
  "username": "S",
  "password": "password_user_sandi"
}
```

Contoh response sukses:

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token_type": "Bearer",
    "access_token": "TOKEN_PANJANG_DARI_SERVER",
    "expires_in": 43200,
    "user": "S",
    "role": "SUPERVISOR"
  }
}
```

Yang dipakai untuk request CRUD adalah nilai:

```json
"access_token": "TOKEN_PANJANG_DARI_SERVER"
```

## 5. Middleware Yang Membaca Bearer Token

File:

`app/Http/Middleware/ApiTokenAuth.php`

Bagian utama:

```php
$token = (string) $request->bearerToken();

if ($token === '') {
    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated.',
    ], 401);
}

$cacheKey = 'api_token:' . hash('sha256', $token);
$payload = Cache::store('file')->get($cacheKey);

if (!is_array($payload) || empty($payload['user'])) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated.',
    ], 401);
}

Cache::store('file')->put($cacheKey, $payload, now()->addHours(self::TOKEN_TTL_HOURS));
$request->attributes->set('api_user', $payload);

return $next($request);
```

Penjelasan detail:

- `$request->bearerToken()` membaca header:

```http
Authorization: Bearer TOKEN_PANJANG_DARI_SERVER
```

- Jika token kosong, API membalas `401 Unauthenticated`.
- Token di-hash dengan `sha256`, lalu dicari di cache file Laravel.
- Jika cache tidak ada, token dianggap tidak valid.
- Jika token valid, masa berlaku token diperpanjang lagi 12 jam:

```php
Cache::store('file')->put($cacheKey, $payload, now()->addHours(self::TOKEN_TTL_HOURS));
```

- Data user token disimpan ke request:

```php
$request->attributes->set('api_user', $payload);
```

Dengan begitu controller bisa membaca user API memakai:

```php
$request->attributes->get('api_user');
```

## 6. Mengambil Data CRUD Kelas Dengan Bearer Token

Endpoint:

```http
GET /api/v1/kelas
```

Headers:

```http
Accept: application/json
Authorization: Bearer TOKEN_PANJANG_DARI_SERVER
```

Controller yang dipanggil:

`app/Http/Controllers/KelasController.php`

Method:

```php
public function index(Request $request)
{
    $idSelect = $this->legacyIdSelect('KELAS');
    $query = DB::table('KELAS')
        ->selectRaw("$idSelect, RTRIM(Kode) as Kode, RTRIM(Nama) as Nama, Rate1, Depo1");

    if ($request->q) {
        $keyword = trim((string) $request->q);
        $query->where(function ($builder) use ($keyword) {
            $builder->whereRaw('RTRIM(Kode) like ?', ['%' . $keyword . '%'])
                ->orWhereRaw('RTRIM(Nama) like ?', ['%' . $keyword . '%']);
        });
    }

    $kelasCollection = $query->orderBy('Kode')->get();
    $kelas = $this->paginateCollection($kelasCollection, 10, $request);

    return $this->respond($request, 'kelas.index', [
        'kelas' => $kelas,
    ], $kelas);
}
```

Penjelasan:

- Data diambil dari tabel `KELAS`.
- Field yang dikembalikan:
  - `id`
  - `Kode`
  - `Nama`
  - `Rate1`
  - `Depo1`
- Jika request membawa parameter `q`, data akan difilter berdasarkan `Kode` atau `Nama`.
- Karena request API membawa `Accept: application/json`, method `respond()` akan mengembalikan JSON, bukan view Blade.

Method `respond()` ada di:

`app/Http/Controllers/Controller.php`

```php
protected function respond(Request $request, string $view, array $viewData = [], mixed $payload = null, int $status = 200): View|JsonResponse
{
    if ($this->isApiRequest($request)) {
        return response()->json([
            'success' => true,
            'data' => $this->normalizeResponseData($payload ?? $viewData),
        ], $status);
    }

    return view($view, $viewData);
}
```

Jadi controller yang sama bisa dipakai untuk:

- Browser: tampil sebagai halaman Blade.
- API: tampil sebagai JSON.

## 7. Contoh Response GET Kelas

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "Kode": "DLX",
        "Nama": "DELUXE",
        "Rate1": 350000,
        "Depo1": 100000
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  }
}
```

Catatan:

- Data list berada di `data.items`.
- Informasi pagination berada di `data.meta`.

## 8. Tambah Data Kelas

Endpoint:

```http
POST /api/v1/kelas
```

Headers:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer TOKEN_PANJANG_DARI_SERVER
```

Body:

```json
{
  "Kode": "TEST",
  "Nama": "TEST API",
  "Rate1": 250000,
  "Depo1": 100000
}
```

Controller:

`KelasController::store()`

Bagian penting:

```php
$kode = trim((string) $request->Kode);

if ($kode === '') {
    return $this->respondError($request, 'Kode wajib diisi.');
}

DB::table('KELAS')->insert([
    'Kode' => $kode,
    'Nama' => $request->Nama,
    'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
    'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
]);
```

Jika `Kode` sudah ada, kode saat ini akan melakukan update data existing, bukan insert baru.

## 9. Update Data Kelas

Endpoint:

```http
PUT /api/v1/kelas/TEST
```

Headers:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer TOKEN_PANJANG_DARI_SERVER
```

Body:

```json
{
  "Nama": "TEST API UPDATED",
  "Rate1": 275000,
  "Depo1": 125000
}
```

Controller:

`KelasController::update()`

Bagian penting:

```php
$kode = trim((string) $kode);
$existing = $this->findKelas($kode);

if (!$existing) {
    return $this->respondError($request, 'Data kelas tidak ditemukan.', 404, [], '/kelas', false);
}

DB::table('KELAS')
    ->whereRaw('RTRIM(Kode) = ?', [$kode])
    ->update([
        'Nama' => $request->Nama,
        'Rate1' => is_numeric($request->Rate1) ? $request->Rate1 : 0,
        'Depo1' => is_numeric($request->Depo1) ? $request->Depo1 : 0,
    ]);
```

## 10. Hapus Data Kelas

Endpoint:

```http
DELETE /api/v1/kelas/TEST
```

Headers:

```http
Accept: application/json
Authorization: Bearer TOKEN_PANJANG_DARI_SERVER
```

Controller:

`KelasController::destroy()`

Bagian penting:

```php
$kode = trim((string) $kode);
$existing = $this->findKelas($kode);

if (!$existing) {
    return $this->respondError($request, 'Data kelas tidak ditemukan.', 404, [], '/kelas', false);
}

DB::table('KELAS')
    ->whereRaw('RTRIM(Kode) = ?', [$kode])
    ->delete();
```

## 11. Contoh Program Laravel Internal: Tools Class Test

File:

`app/Http/Controllers/ClassTestController.php`

Program ini dibuat untuk contoh kecil di menu:

```text
Tools > Class Test
```

Fungsinya:

1. User mengisi username/password SANDI.
2. Controller membuat Bearer token.
3. Controller meneruskan token untuk mengambil data Kelas.
4. Response JSON ditampilkan dalam grid.

Bagian login token:

```php
$plainToken = Str::random(64);
$payload = [
    'user' => trim((string) $user->Kode),
    'role' => trim((string) $user->Nama),
    'issued_at' => $now->toIso8601String(),
    'expires_at' => $now->copy()->addHours(self::API_TOKEN_TTL_HOURS)->toIso8601String(),
];

Cache::store('file')->put(
    'api_token:' . hash('sha256', $plainToken),
    $payload,
    now()->addHours(self::API_TOKEN_TTL_HOURS)
);
```

Bagian meneruskan token:

```php
$server = [
    'HTTP_ACCEPT' => 'application/json',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
    'CONTENT_TYPE' => 'application/json',
];

$request = Request::create('/api/v1/kelas', 'GET', [], [], [], $server);
$request->headers->set('Authorization', 'Bearer ' . $token);
$request->attributes->set('api_user', $apiUser);

$response = app(KelasController::class)->index($request);
```

Penjelasan:

- `HTTP_AUTHORIZATION` adalah header Bearer Token.
- `Request::create('/api/v1/kelas', 'GET', ...)` membuat request internal.
- `app(KelasController::class)->index($request)` memanggil controller Kelas.
- Karena request membawa `Accept: application/json`, hasilnya JSON.

## 12. Error Yang Sering Muncul

### 401 Unauthenticated

Penyebab umum:

- Bearer token kosong.
- Token salah.
- Token sudah expired.
- Header tidak memakai format `Authorization: Bearer TOKEN`.
- API Settings masih mode Basic, bukan Token.

### 401 Login failed

Penyebab umum:

- Username tidak ada di tabel `SANDI`.
- Password tidak sama dengan field `Passw`.
- Ada spasi atau huruf yang tidak sesuai.

### 422 Username dan password wajib diisi

Penyebab umum:

- Body JSON kosong.
- Field bukan `username` dan `password`.
- Header `Content-Type: application/json` tidak dikirim.

## 13. Ringkasan Paling Pendek

1. Login:

```http
POST /api/v1/login
```

Body:

```json
{
  "username": "S",
  "password": "password_sandi"
}
```

2. Ambil token:

```json
"access_token": "TOKEN"
```

3. Pakai token:

```http
GET /api/v1/kelas
Authorization: Bearer TOKEN
Accept: application/json
```

4. Ambil data grid dari:

```json
data.items
```
