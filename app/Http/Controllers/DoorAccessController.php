<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DoorAccessController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $detailKey = trim((string) $request->query('detail_key', $request->query('regno2', '')));
        $storageReady = Schema::hasTable('door_access_cards');
        $selectedCard = $detailKey !== '' ? $this->loadDoorCardSeed($detailKey, $storageReady) : null;
        $cards = $this->loadDoorCards($request, $search, $storageReady);

        return view('key-door.index', [
            'search' => $search,
            'detailKey' => $detailKey,
            'storageReady' => $storageReady,
            'selectedCard' => $selectedCard,
            'cards' => $cards,
        ]);
    }

    public function save(Request $request)
    {
        return $this->persistDoorCard($request, 'Prepared');
    }

    public function write(Request $request)
    {
        return $this->persistDoorCard($request, 'Written');
    }

    public function verify(Request $request)
    {
        if (!Schema::hasTable('door_access_cards')) {
            return redirect('/key-door')->with('error', 'Tabel akses pintu belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $validated = $request->validate([
            'RegNo2' => 'required|string|max:60',
        ]);

        $existing = DB::table('door_access_cards')->where('regno2', $validated['RegNo2'])->first();

        if (!$existing) {
            return redirect('/key-door?detail_key=' . urlencode($validated['RegNo2']))->with('error', 'Data kartu untuk detail ini belum tersimpan.');
        }

        $accessState = $this->resolveAccessState(Carbon::parse($existing->expires_at));

        DB::table('door_access_cards')
            ->where('regno2', $validated['RegNo2'])
            ->update([
                'access_state' => $accessState,
                'status' => $accessState === 'Expired' ? 'Expired' : 'Verified',
                'last_verified_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect('/key-door?detail_key=' . urlencode($validated['RegNo2']))->with('success', 'Status kartu berhasil diverifikasi: ' . $accessState . '.');
    }

    private function persistDoorCard(Request $request, string $mode)
    {
        if (!Schema::hasTable('door_access_cards')) {
            return redirect('/key-door')->with('error', 'Tabel akses pintu belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $validated = $request->validate([
            'RegNo' => 'required|string|max:30',
            'RegNo2' => 'required|string|max:60',
            'RoomCode' => 'required|string|max:10',
            'GuestName' => 'required|string|max:120',
            'CheckInDate' => 'required|date_format:Y-m-d',
            'CheckInTime' => 'required|string|max:20',
            'CheckOutDate' => 'required|date_format:Y-m-d',
            'CheckOutTime' => 'required|string|max:20',
            'DepositAmount' => 'nullable|string|max:30',
            'SectorNumber' => 'nullable|string|max:30',
            'CardUid' => 'nullable|string|max:60',
            'Notes' => 'nullable|string|max:500',
        ]);

        $checkinAt = $this->combineDateTime($validated['CheckInDate'], $validated['CheckInTime']);
        $checkoutAt = $this->combineDateTime($validated['CheckOutDate'], $validated['CheckOutTime']);
        $accessState = $this->resolveAccessState($checkoutAt);
        $status = $mode;
        if ($accessState === 'Expired') {
            $status = 'Expired';
        }

        $payload = [
            'regno' => strtoupper(trim((string) $validated['RegNo'])),
            'regno2' => strtoupper(trim((string) $validated['RegNo2'])),
            'room_code' => strtoupper(trim((string) $validated['RoomCode'])),
            'guest_name' => strtoupper(trim((string) $validated['GuestName'])),
            'checkin_at' => $checkinAt->format('Y-m-d H:i:s'),
            'checkout_at' => $checkoutAt->format('Y-m-d H:i:s'),
            'expires_at' => $checkoutAt->format('Y-m-d H:i:s'),
            'deposit_amount' => $this->normalizeMoney($validated['DepositAmount'] ?? null),
            'sector_number' => trim((string) ($validated['SectorNumber'] ?? '')),
            'status' => $status,
            'access_state' => $accessState,
            'card_uid' => trim((string) ($validated['CardUid'] ?? '')),
            'issued_by' => strtoupper(trim((string) session('user', 'SYSTEM'))),
            'notes' => trim((string) ($validated['Notes'] ?? '')),
            'updated_at' => now(),
        ];

        $existing = DB::table('door_access_cards')->where('regno2', $payload['regno2'])->first();

        if ($existing) {
            DB::table('door_access_cards')
                ->where('regno2', $payload['regno2'])
                ->update(array_merge($payload, [
                    'written_at' => $mode === 'Written' ? now() : $existing->written_at,
                    'last_verified_at' => $mode === 'Verified' ? now() : $existing->last_verified_at,
                ]));
        } else {
            DB::table('door_access_cards')->insert(array_merge($payload, [
                'written_at' => $mode === 'Written' ? now() : null,
                'last_verified_at' => null,
                'created_at' => now(),
            ]));
        }

        $message = $mode === 'Written'
            ? 'Kartu akses berhasil ditulis.'
            : 'Data akses pintu berhasil disimpan.';

        return redirect('/key-door?detail_key=' . urlencode($payload['regno2']))->with('success', $message);
    }

    private function loadDoorCardSeed(string $detailKey, bool $storageReady): ?array
    {
        $detail = DB::table('DATA2')
            ->leftJoin('Deposit', function ($join) {
                $join->on(DB::raw('RTRIM(Deposit.RegNo)'), '=', DB::raw('RTRIM(DATA2.RegNo)'));
                $join->on(DB::raw('RTRIM(Deposit.Kode)'), '=', DB::raw('RTRIM(DATA2.Kode)'));
            })
            ->selectRaw("RTRIM(DATA2.RegNo) as RegNo, RTRIM(DATA2.RegNo2) as RegNo2, RTRIM(DATA2.Kode) as Kode, RTRIM(DATA2.Guest) as Guest, DATA2.TglIn, DATA2.JamIn, DATA2.TglKeluar, DATA2.JamOut, ISNULL(SUM(CAST(Deposit.Deposit as float)), 0) as DepositAmount")
            ->whereRaw('RTRIM(DATA2.RegNo2) = ?', [$detailKey])
            ->groupBy(DB::raw('RTRIM(DATA2.RegNo)'), DB::raw('RTRIM(DATA2.RegNo2)'), DB::raw('RTRIM(DATA2.Kode)'), DB::raw('RTRIM(DATA2.Guest)'), 'DATA2.TglIn', 'DATA2.JamIn', 'DATA2.TglKeluar', 'DATA2.JamOut')
            ->first();

        $card = $storageReady
            ? DB::table('door_access_cards')->where('regno2', $detailKey)->first()
            : null;

        if (!$detail && !$card) {
            return null;
        }

        $checkinDate = $detail && !empty($detail->TglIn) ? Carbon::parse($detail->TglIn)->format('Y-m-d') : ($card ? Carbon::parse($card->checkin_at)->format('Y-m-d') : now()->format('Y-m-d'));
        $checkinTime = $detail && !empty($detail->JamIn) ? $this->displayTime($detail->JamIn) : ($card ? Carbon::parse($card->checkin_at)->format('H:i') : now()->format('H:i'));
        $checkoutDate = $detail && !empty($detail->TglKeluar) ? Carbon::parse($detail->TglKeluar)->format('Y-m-d') : ($card ? Carbon::parse($card->checkout_at)->format('Y-m-d') : now()->addDay()->format('Y-m-d'));
        $checkoutTime = $detail && !empty($detail->JamOut) ? $this->displayTime($detail->JamOut) : ($card ? Carbon::parse($card->checkout_at)->format('H:i') : '12:00');
        $expiresAt = Carbon::createFromFormat('Y-m-d H:i', $checkoutDate . ' ' . $checkoutTime);
        $accessState = $card->access_state ?? $this->resolveAccessState($expiresAt);

        return [
            'regno' => $detail->RegNo ?? $card->regno,
            'regno2' => $detail->RegNo2 ?? $card->regno2,
            'room_code' => $detail->Kode ?? $card->room_code,
            'guest_name' => $detail->Guest ?? $card->guest_name,
            'checkin_date' => $checkinDate,
            'checkin_time' => $checkinTime,
            'checkout_date' => $checkoutDate,
            'checkout_time' => $checkoutTime,
            'deposit_amount' => (float) ($detail->DepositAmount ?? $card->deposit_amount ?? 0),
            'sector_number' => $card->sector_number ?? '',
            'status' => $card->status ?? 'Prepared',
            'access_state' => $accessState,
            'card_uid' => $card->card_uid ?? '',
            'notes' => $card->notes ?? '',
            'written_at' => $card->written_at ?? null,
            'last_verified_at' => $card->last_verified_at ?? null,
        ];
    }

    private function loadDoorCards(Request $request, string $search, bool $storageReady)
    {
        if (!$storageReady) {
            return $this->paginateCollection(collect(), 10, $request);
        }

        $query = DB::table('door_access_cards')
            ->select('regno', 'regno2', 'room_code', 'guest_name', 'sector_number', 'status', 'access_state', 'deposit_amount', 'checkout_at', 'written_at', 'last_verified_at', 'updated_at')
            ->orderByDesc('updated_at');

        if ($search !== '') {
            $keyword = '%' . strtoupper($search) . '%';
            $query->where(function ($builder) use ($keyword) {
                $builder->whereRaw('UPPER(regno) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(regno2) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(room_code) LIKE ?', [$keyword])
                    ->orWhereRaw('UPPER(guest_name) LIKE ?', [$keyword]);
            });
        }

        $cards = $query->get()->map(function ($card) {
            $card->checkout_display = Carbon::parse($card->checkout_at)->format('d-m-Y H:i');
            $card->deposit_display = number_format((float) ($card->deposit_amount ?? 0), 0, ',', '.');
            return $card;
        });

        return $this->paginateCollection($cards, 10, $request);
    }

    private function combineDateTime(string $date, string $time): Carbon
    {
        $safeTime = trim($time) === '' ? '00:00' : trim($time);
        foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            try {
                return Carbon::createFromFormat($format, $date . ' ' . $safeTime);
            } catch (\Throwable $exception) {
            }
        }

        return Carbon::parse($date . ' ' . $safeTime);
    }

    private function resolveAccessState(Carbon $expiresAt): string
    {
        return now()->greaterThan($expiresAt) ? 'Expired' : 'Active';
    }

    private function normalizeMoney($value): float
    {
        $normalized = preg_replace('/[^\d]/', '', (string) $value);
        return is_numeric($normalized) ? (float) $normalized : 0;
    }

    private function displayTime($value): string
    {
        if (empty($value)) {
            return '12:00';
        }

        try {
            return Carbon::parse((string) $value)->format('H:i');
        } catch (\Throwable $exception) {
            return substr(trim((string) $value), 0, 5) ?: '12:00';
        }
    }
}