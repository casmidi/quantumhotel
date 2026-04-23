<?php

declare(strict_types=1);

use App\Http\Controllers\CheckinController;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function makeJsonRequest(string $method, string $uri, array $payload = []): Request
{
    $request = Request::create($uri, strtoupper($method), $payload, [], [], [
        'HTTP_ACCEPT' => 'application/json',
        'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
    ]);
    $request->headers->set('Accept', 'application/json');

    return $request;
}

function decodeJsonResponse(JsonResponse $response): array
{
    return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
}

function callCheckinIndex(CheckinController $controller): array
{
    $response = $controller->index(makeJsonRequest('GET', '/checkin'));

    if (!$response instanceof JsonResponse) {
        throw new RuntimeException('Check-in index did not return JSON.');
    }

    return decodeJsonResponse($response);
}

function callCheckinStore(CheckinController $controller, array $payload): array
{
    try {
        $response = $controller->store(makeJsonRequest('POST', '/checkin', $payload));

        if (!$response instanceof JsonResponse) {
            throw new RuntimeException('Check-in store did not return JSON.');
        }

        return [
            'status' => $response->getStatusCode(),
            'body' => decodeJsonResponse($response),
        ];
    } catch (ValidationException $exception) {
        return [
            'status' => 422,
            'body' => [
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ],
        ];
    }
}

function callScanValidation(CheckinController $controller): array
{
    try {
        $response = $controller->scanKtp(makeJsonRequest('POST', '/checkin/scan-ktp'));

        if (!$response instanceof JsonResponse) {
            throw new RuntimeException('Scan endpoint did not return JSON.');
        }

        return [
            'status' => $response->getStatusCode(),
            'body' => decodeJsonResponse($response),
        ];
    } catch (ValidationException $exception) {
        return [
            'status' => 422,
            'body' => [
                'success' => false,
                'message' => $exception->getMessage(),
                'errors' => $exception->errors(),
            ],
        ];
    }
}

function chooseFirstContaining(array $values, string $needle, ?string $fallback = null): string
{
    foreach ($values as $value) {
        if (stripos((string) $value, $needle) !== false) {
            return (string) $value;
        }
    }

    if ($fallback !== null) {
        return $fallback;
    }

    return (string) ($values[0] ?? '');
}

function summarizeStoredCheckin(string $regNo): array
{
    $header = DB::table('DATA')
        ->selectRaw("RTRIM(RegNo) as reg_no, RTRIM(Kode) as room_code, RTRIM(Tipe) as type, RTRIM(KodeCust) as company, RTRIM(ResNo) as reservation_no, RTRIM(Sales) as sales, Periksa as check_deposit")
        ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
        ->first();

    $details = DB::table('DATA2')
        ->selectRaw("
            RTRIM(RegNo2) as reg_no2,
            RTRIM(Kode) as room_code,
            RTRIM(Guest) as guest_name,
            RTRIM(TypeId) as id_type,
            RTRIM(KTP) as id_number,
            RTRIM(Payment) as payment_method,
            RTRIM(Segment) as segment,
            RTRIM(Package) as package_code,
            RTRIM(Posisi) as group_position,
            RTRIM(Phone) as phone,
            RTRIM(Email) as email,
            RTRIM(Alamat) as address,
            RTRIM(Kelurahan) as kelurahan,
            RTRIM(Kecamatan) as kecamatan,
            RTRIM(Kota) as city,
            RTRIM(Propinsi) as province,
            RTRIM(Agama) as religion,
            RTRIM(KodeNegara) as nationality,
            RTRIM(PlaceBirth) as place_of_birth,
            RTRIM(Usaha) as company,
            RTRIM(Profesi) as occupation,
            RTRIM(Status) as stay_status,
            RTRIM(Pst) as pst,
            RTRIM(Receipt) as reservation_no,
            RTRIM(CardNumber) as credit_card,
            RTRIM(Remark) as remarks,
            RTRIM(Member) as member,
            RTRIM(Sales) as sales,
            Person as person,
            BF as breakfast,
            Nominal as nominal,
            TglIn as check_in_date,
            TglKeluar as check_out_date,
            TglLahir as birth_date
        ")
        ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
        ->orderBy('RegNo2')
        ->get()
        ->map(fn ($row) => (array) $row)
        ->all();

    $depositRows = DB::table('Deposit')
        ->selectRaw("RTRIM(RegNo) as reg_no, RTRIM(Kode) as room_code, Deposit as deposit, TglIn as check_in_date")
        ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
        ->orderBy('Kode')
        ->get()
        ->map(fn ($row) => (array) $row)
        ->all();

    $dataMoveRows = DB::table('DataMove')
        ->selectRaw("RTRIM(RegNo2) as reg_no2, RTRIM(Kode) as room_code, RTRIM(Package) as package_code, Nominal as nominal")
        ->whereRaw('RTRIM(RegNo) = ?', [$regNo])
        ->orderBy('RegNo2')
        ->get()
        ->map(fn ($row) => (array) $row)
        ->all();

    $roomStatuses = [];
    $roomCodes = array_values(array_filter(array_map(static fn (array $detail) => (string) ($detail['room_code'] ?? ''), $details)));
    if ($roomCodes !== []) {
        $roomStatuses = DB::table('ROOM')
            ->selectRaw("RTRIM(Kode) as room_code, RTRIM(Status) as status, RTRIM(Status2) as status2")
            ->whereIn(DB::raw('RTRIM(Kode)'), $roomCodes)
            ->orderBy('Kode')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    return [
        'header' => $header ? (array) $header : null,
        'details' => $details,
        'deposits' => $depositRows,
        'data_move' => $dataMoveRows,
        'room_statuses' => $roomStatuses,
    ];
}

function buildWalkinPayload(array $catalog, string $roomCode, array $package): array
{
    $today = Carbon::today();

    return [
        'GeneratedRegNo' => (string) $catalog['next_reg_no'],
        'CheckInDate' => $today->format('Y-m-d'),
        'CheckInTime' => '14:15',
        'ReservationNumber' => '',
        'GuestName' => 'TEST WALKIN API 01',
        'GuestName2' => 'TEST PARTNER WALKIN',
        'Address' => 'JL TEST WALKIN NO 1',
        'Kelurahan' => 'GAMBIR',
        'Kecamatan' => 'GAMBIR',
        'KabCity' => 'JAKARTA PUSAT',
        'ProvinceCountry' => chooseFirstContaining($catalog['options']['province'], 'DKI', (string) ($catalog['options']['province'][0] ?? 'DKI JAKARTA')),
        'ProvinceCountrySource' => 'manual',
        'TypeOfId' => chooseFirstContaining($catalog['options']['id_type'], 'KTP', (string) ($catalog['options']['id_type'][0] ?? 'KTP')),
        'IdNumber' => '3171010101010001',
        'ExpiredDate' => '',
        'TypeOfCheckIn' => chooseFirstContaining(array_filter($catalog['options']['type'], static fn ($value) => stripos((string) $value, 'GROUP') === false), 'WALK', (string) ($catalog['options']['type'][0] ?? 'WALK IN')),
        'PlaceOfBirth' => 'JAKARTA',
        'BirthDate' => '1990-01-01',
        'Religion' => chooseFirstContaining($catalog['options']['religion'], 'ISLAM', (string) ($catalog['options']['religion'][0] ?? 'ISLAM')),
        'Nationality' => chooseFirstContaining($catalog['options']['nationality'], 'INA', (string) ($catalog['options']['nationality'][0] ?? 'INA')),
        'NumberOfPerson' => 2,
        'EstimationOut' => $today->copy()->addDay()->format('Y-m-d'),
        'PaymentMethod' => chooseFirstContaining($catalog['options']['payment'], 'CASH', (string) ($catalog['options']['payment'][0] ?? 'CASH')),
        'Company' => '',
        'Occupation' => 'QA TESTER',
        'CreditCardNumber' => '',
        'CheckDeposit' => '',
        'Segment' => chooseFirstContaining($catalog['options']['segment'], 'DIRECT', (string) ($catalog['options']['segment'][0] ?? 'DIRECT')),
        'Phone' => '081234567801',
        'Email' => 'walkin-test@example.com',
        'Remarks' => 'API WALKIN SMOKE TEST',
        'Member' => 'MEMBER WALKIN',
        'Sales' => '',
        'RoomCodeList' => [$roomCode],
        'SameAsLeaderList' => ['1'],
        'PackageCodeList' => [(string) $package['kode']],
        'NominalList' => [number_format((float) $package['nominal'], 0, '', '')],
        'BreakfastList' => ['2'],
        'DetailKeyList' => [''],
        'RoomGroupPositionList' => [''],
        'RoomGuestNameList' => [''],
        'RoomGuestIdTypeList' => [''],
        'RoomGuestIdNumberList' => [''],
        'RoomGuestBirthDateList' => [''],
        'RoomGuestPhoneList' => [''],
        'RoomGuestEmailList' => [''],
        'RoomGuestAddressList' => [''],
        'RoomGuestNationalityList' => [''],
    ];
}

function buildGroupPayload(array $catalog, array $rooms, array $package): array
{
    $today = Carbon::today();
    $type = chooseFirstContaining($catalog['options']['type'], 'GROUP', (string) ($catalog['options']['type'][0] ?? 'GROUP'));
    $groupOptions = array_map(static fn ($value) => strtoupper((string) $value), $catalog['options']['group_position']);
    $leaderPosition = in_array('LEADER', $groupOptions, true) ? 'LEADER' : ((string) ($catalog['options']['group_position'][0] ?? 'LEADER'));
    $subPosition = in_array('SUB', $groupOptions, true) ? 'SUB' : ((string) ($catalog['options']['group_position'][1] ?? $leaderPosition));

    return [
        'GeneratedRegNo' => (string) $catalog['next_reg_no'],
        'CheckInDate' => $today->format('Y-m-d'),
        'CheckInTime' => '15:00',
        'ReservationNumber' => '',
        'GuestName' => 'TEST GROUP LEADER 01',
        'GuestName2' => 'GROUP LEADER ALT',
        'Address' => 'JL TEST GROUP NO 88',
        'Kelurahan' => 'SUKAMAJU',
        'Kecamatan' => 'CIBINONG',
        'KabCity' => 'BOGOR',
        'ProvinceCountry' => chooseFirstContaining($catalog['options']['province'], 'JAWA BARAT', (string) ($catalog['options']['province'][0] ?? 'JAWA BARAT')),
        'ProvinceCountrySource' => 'manual',
        'TypeOfId' => chooseFirstContaining($catalog['options']['id_type'], 'KTP', (string) ($catalog['options']['id_type'][0] ?? 'KTP')),
        'IdNumber' => '3201010101010002',
        'ExpiredDate' => '',
        'TypeOfCheckIn' => $type,
        'PlaceOfBirth' => 'BOGOR',
        'BirthDate' => '1988-02-20',
        'Religion' => chooseFirstContaining($catalog['options']['religion'], 'ISLAM', (string) ($catalog['options']['religion'][0] ?? 'ISLAM')),
        'Nationality' => chooseFirstContaining($catalog['options']['nationality'], 'INA', (string) ($catalog['options']['nationality'][0] ?? 'INA')),
        'NumberOfPerson' => 5,
        'EstimationOut' => $today->copy()->addDays(2)->format('Y-m-d'),
        'PaymentMethod' => chooseFirstContaining($catalog['options']['payment'], 'CASH', (string) ($catalog['options']['payment'][0] ?? 'CASH')),
        'Company' => '',
        'Occupation' => 'EVENT ORGANIZER',
        'CreditCardNumber' => '',
        'CheckDeposit' => '1',
        'Segment' => chooseFirstContaining($catalog['options']['segment'], 'GROUP', chooseFirstContaining($catalog['options']['segment'], 'DIRECT', (string) ($catalog['options']['segment'][0] ?? 'GROUP'))),
        'Phone' => '081234567802',
        'Email' => 'group-test@example.com',
        'Remarks' => 'API GROUP 3 ROOM SMOKE TEST',
        'Member' => 'GROUP MEMBER REF',
        'Sales' => (string) ($catalog['options']['sales'][0] ?? ''),
        'RoomCodeList' => array_values($rooms),
        'SameAsLeaderList' => ['1', '0', '0'],
        'PackageCodeList' => [(string) $package['kode'], (string) $package['kode'], (string) $package['kode']],
        'NominalList' => [
            number_format((float) $package['nominal'], 0, '', ''),
            number_format((float) $package['nominal'], 0, '', ''),
            number_format((float) $package['nominal'], 0, '', ''),
        ],
        'BreakfastList' => ['2', '1', '2'],
        'DetailKeyList' => ['', '', ''],
        'RoomGroupPositionList' => [$leaderPosition, $subPosition, $subPosition],
        'RoomGuestNameList' => ['', 'TEST GROUP GUEST 02', 'TEST GROUP GUEST 03'],
        'RoomGuestIdTypeList' => ['', 'KTP', 'KTP'],
        'RoomGuestIdNumberList' => ['', '3201010101010003', '3201010101010004'],
        'RoomGuestBirthDateList' => ['', '1992-03-12', '1994-09-08'],
        'RoomGuestPhoneList' => ['', '081234567803', '081234567804'],
        'RoomGuestEmailList' => ['', 'guest02@example.com', 'guest03@example.com'],
        'RoomGuestAddressList' => ['', 'JL TEST GROUP TAMU 02', 'JL TEST GROUP TAMU 03'],
        'RoomGuestNationalityList' => ['', 'INA', 'INA'],
    ];
}

function formatJsonBlock(mixed $data): string
{
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

$controller = $app->make(CheckinController::class);
$reportPath = __DIR__ . '/../storage/app/checkin_test_report_2026-04-23.md';
$persist = in_array('--persist', $argv, true);
$output = [
    'generated_at' => Carbon::now()->toDateTimeString(),
    'database' => DB::connection()->getDatabaseName(),
    'mode' => $persist ? 'persist' : 'rollback',
    'api_checks' => [],
    'scenarios' => [],
];

DB::beginTransaction();

try {
    $catalogResponse = callCheckinIndex($controller);
    $catalog = $catalogResponse['data'] ?? [];
    $availableRooms = array_values(array_map(static fn ($room) => $room['kode'], array_slice($catalog['rooms'] ?? [], 0, 4)));
    $package = $catalog['packages'][0] ?? null;

    if (count($availableRooms) < 4) {
        throw new RuntimeException('Available rooms are fewer than 4, cannot run both smoke tests safely.');
    }

    if (!$package) {
        throw new RuntimeException('No active package found, check-in smoke test cannot proceed because package is required.');
    }

    $output['api_checks']['checkin_index'] = [
        'status' => 200,
        'summary' => [
            'next_reg_no' => $catalog['next_reg_no'] ?? '',
            'available_room_count' => count($catalog['rooms'] ?? []),
            'package_count' => count($catalog['packages'] ?? []),
            'type_options' => $catalog['options']['type'] ?? [],
        ],
    ];

    $output['api_checks']['scan_ktp_validation'] = callScanValidation($controller);

    $walkinPayload = buildWalkinPayload($catalog, $availableRooms[0], $package);
    $walkinResult = callCheckinStore($controller, $walkinPayload);
    $walkinRegNo = $walkinResult['body']['data']['reg_no'] ?? $walkinPayload['GeneratedRegNo'];

    $output['scenarios']['walkin'] = [
        'request' => $walkinPayload,
        'response' => $walkinResult,
        'stored' => summarizeStoredCheckin($walkinRegNo),
    ];

    $catalogAfterWalkin = callCheckinIndex($controller);
    $catalog2 = $catalogAfterWalkin['data'] ?? $catalog;
    $remainingRooms = array_values(array_map(static fn ($room) => $room['kode'], array_slice($catalog2['rooms'] ?? [], 0, 6)));
    $remainingRooms = array_values(array_filter($remainingRooms, static fn ($room) => $room !== $availableRooms[0]));

    if (count($remainingRooms) < 3) {
        throw new RuntimeException('Remaining rooms are fewer than 3 after walk-in smoke test.');
    }

    $groupPayload = buildGroupPayload($catalog2, array_slice($remainingRooms, 0, 3), $package);
    $groupResult = callCheckinStore($controller, $groupPayload);
    $groupRegNo = $groupResult['body']['data']['reg_no'] ?? $groupPayload['GeneratedRegNo'];

    $output['scenarios']['group_3_rooms'] = [
        'request' => $groupPayload,
        'response' => $groupResult,
        'stored' => summarizeStoredCheckin($groupRegNo),
    ];

    $markdown = [];
    $markdown[] = '# Check-In Smoke Test Report';
    $markdown[] = '';
    $markdown[] = '- Generated at: ' . $output['generated_at'];
    $markdown[] = '- Database: ' . $output['database'];
    $markdown[] = '- Mode: ' . ($persist ? 'persist to database' : 'transaction rollback after verification');
    $markdown[] = '';
    $markdown[] = '## API Checks';
    $markdown[] = '';
    $markdown[] = '### GET /checkin';
    $markdown[] = '```json';
    $markdown[] = formatJsonBlock($output['api_checks']['checkin_index']);
    $markdown[] = '```';
    $markdown[] = '';
    $markdown[] = '### POST /checkin/scan-ktp without image';
    $markdown[] = '```json';
    $markdown[] = formatJsonBlock($output['api_checks']['scan_ktp_validation']);
    $markdown[] = '```';
    $markdown[] = '';

    foreach ($output['scenarios'] as $scenarioName => $scenarioData) {
        $markdown[] = '## Scenario: ' . str_replace('_', ' ', $scenarioName);
        $markdown[] = '';
        $markdown[] = '### Submitted Payload';
        $markdown[] = '```json';
        $markdown[] = formatJsonBlock($scenarioData['request']);
        $markdown[] = '```';
        $markdown[] = '';
        $markdown[] = '### API Response';
        $markdown[] = '```json';
        $markdown[] = formatJsonBlock($scenarioData['response']);
        $markdown[] = '```';
        $markdown[] = '';
        $markdown[] = '### Stored Rows';
        $markdown[] = '```json';
        $markdown[] = formatJsonBlock($scenarioData['stored']);
        $markdown[] = '```';
        $markdown[] = '';
    }

    file_put_contents($reportPath, implode(PHP_EOL, $markdown) . PHP_EOL);

    if ($persist) {
        DB::commit();
    } else {
        DB::rollBack();
    }

    echo formatJsonBlock([
        'success' => true,
        'report_path' => $reportPath,
        'summary' => [
            'mode' => $persist ? 'persist' : 'rollback',
            'walkin_status' => $output['scenarios']['walkin']['response']['status'] ?? null,
            'group_status' => $output['scenarios']['group_3_rooms']['response']['status'] ?? null,
            'scan_validation_status' => $output['api_checks']['scan_ktp_validation']['status'] ?? null,
            'walkin_reg_no' => $output['scenarios']['walkin']['response']['body']['data']['reg_no'] ?? null,
            'group_reg_no' => $output['scenarios']['group_3_rooms']['response']['body']['data']['reg_no'] ?? null,
            'walkin_rooms' => $output['scenarios']['walkin']['response']['body']['data']['rooms'] ?? [],
            'group_rooms' => $output['scenarios']['group_3_rooms']['response']['body']['data']['rooms'] ?? [],
        ],
    ]);
} catch (Throwable $throwable) {
    if (DB::transactionLevel() > 0) {
        DB::rollBack();
    }

    $failure = [
        'success' => false,
        'message' => $throwable->getMessage(),
        'trace' => [
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
        ],
    ];

    file_put_contents($reportPath, formatJsonBlock($failure) . PHP_EOL);
    echo formatJsonBlock($failure);
    exit(1);
}
