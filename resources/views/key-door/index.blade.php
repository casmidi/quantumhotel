@extends('layouts.app')

@section('title', '')

@section('content')

@php
    $card = $selectedCard ?? [
        'regno' => request('regno', ''),
        'regno2' => request('detail_key', ''),
        'room_code' => request('room_code', ''),
        'guest_name' => '',
        'checkin_date' => now()->format('Y-m-d'),
        'checkin_time' => now()->format('H:i'),
        'checkout_date' => now()->addDay()->format('Y-m-d'),
        'checkout_time' => '12:00',
        'deposit_amount' => 0,
        'sector_number' => '',
        'status' => 'Prepared',
        'access_state' => 'Pending',
        'card_uid' => '',
        'notes' => '',
        'written_at' => null,
        'last_verified_at' => null,
    ];
@endphp

@include('partials.crud-package-theme')

<style>
.keydoor-page { padding:0 0 2rem; color:#10233b; }
.keydoor-shell + .keydoor-shell { margin-top:1.5rem; }
.keydoor-stage { display:grid; grid-template-columns:minmax(0, 1.6fr) minmax(320px, .9fr); gap:1.2rem; }
.keydoor-board { border-radius:26px; border:1px solid rgba(199,165,106,.28); background:linear-gradient(180deg, rgba(255,248,234,.96), rgba(255,255,255,.92)); box-shadow:inset 0 1px 0 rgba(255,255,255,.72), 0 20px 40px rgba(125,96,42,.12); padding:1rem; }
.keydoor-board-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
.keydoor-board-title strong { display:block; font-size:1.15rem; color:#173761; }
.keydoor-board-title span { display:block; margin-top:.25rem; color:#8f6a2d; font-size:.82rem; text-transform:uppercase; letter-spacing:.08em; }
.keydoor-status-pill { display:inline-flex; align-items:center; gap:.45rem; padding:.5rem .85rem; border-radius:999px; font-weight:800; font-size:.84rem; background:rgba(23,55,97,.08); color:#173761; }
.keydoor-status-pill.is-active { background:rgba(34,139,88,.14); color:#1b6f47; }
.keydoor-status-pill.is-expired { background:rgba(178,34,34,.12); color:#9a2332; }
.keydoor-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:.85rem 1rem; }
.keydoor-field { display:grid; gap:.45rem; }
.keydoor-field.is-wide { grid-column:1 / -1; }
.keydoor-label { font-size:.78rem; font-weight:900; color:#233f6b; text-transform:uppercase; letter-spacing:.08em; }
.keydoor-inline { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:.75rem; }
.keydoor-room-strip { display:flex; gap:.55rem; flex-wrap:wrap; margin-bottom:.85rem; }
.keydoor-room-chip { min-width:68px; display:inline-flex; justify-content:center; align-items:center; padding:.55rem .8rem; border-radius:14px; background:rgba(23,55,97,.08); color:#173761; font-weight:800; }
.keydoor-side { display:grid; gap:1rem; }
.keydoor-action-stack { display:grid; gap:.9rem; }
.keydoor-action-card { border-radius:22px; border:1px solid rgba(199,165,106,.24); background:linear-gradient(180deg, rgba(255,255,255,.94), rgba(248,240,223,.9)); padding:1rem; box-shadow:0 16px 30px rgba(125,96,42,.1); }
.keydoor-action-card h4 { margin:0 0 .35rem; font-size:1rem; color:#173761; }
.keydoor-action-card p { margin:0 0 .9rem; color:#6b7b90; font-size:.84rem; }
.keydoor-action-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:.75rem; }
.keydoor-action-grid .package-btn-primary, .keydoor-action-grid .package-btn-secondary { width:100%; justify-content:center; }
.keydoor-meta { display:grid; gap:.65rem; }
.keydoor-meta-row { display:flex; justify-content:space-between; gap:1rem; font-size:.88rem; }
.keydoor-meta-row span { color:#6b7b90; }
.keydoor-meta-row strong { color:#173761; text-align:right; }
.keydoor-warning { border-radius:18px; padding:.9rem 1rem; background:linear-gradient(135deg, rgba(178,34,34,.12), rgba(178,34,34,.05)); color:#922535; }
.keydoor-list-table .status-tag { display:inline-flex; align-items:center; justify-content:center; padding:.36rem .7rem; border-radius:999px; font-weight:800; font-size:.78rem; background:rgba(23,55,97,.08); color:#173761; }
.keydoor-list-table .status-tag.is-active { background:rgba(34,139,88,.14); color:#1b6f47; }
.keydoor-list-table .status-tag.is-expired { background:rgba(178,34,34,.12); color:#9a2332; }
@media (max-width:1199.98px){ .keydoor-stage { grid-template-columns:1fr; } }
@media (max-width:767.98px){ .keydoor-grid, .keydoor-inline, .keydoor-action-grid { grid-template-columns:1fr; } }
</style>

<div class="container-fluid keydoor-page">
    @if(session('success'))<div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error mb-4">{{ session('error') }}</div>@endif

    <section class="package-shell keydoor-shell">
        <div class="package-shell-header">
            <div class="package-shell-heading-block">
                <h1 class="package-shell-title">Key Door</h1>
                <p class="package-shell-subtitle">Akses kartu kamar mengikuti nomor room, nama tamu, check in, checkout, deposit, dan batas expired akses.</p>
            </div>
        </div>
        <div class="package-shell-body">
            @if(!$storageReady)
                <div class="keydoor-warning mb-4">Tabel penyimpanan akses pintu belum tersedia. File migrasi sudah dibuat, tetapi database belum menjalankan migrasi tersebut.</div>
            @endif

            <form method="GET" action="/key-door" class="checkin-search-form mb-4">
                <div class="checkin-search-group">
                    <label class="package-label" for="searchKeyword">Search Detail</label>
                    <input type="text" name="search" id="searchKeyword" class="form-control package-input" value="{{ $search }}" placeholder="Search reg number, detail key, room, or guest">
                </div>
                <div class="checkin-search-group">
                    <label class="package-label" for="detailKeySearch">Load Detail Key</label>
                    <input type="text" name="detail_key" id="detailKeySearch" class="form-control package-input" value="{{ $detailKey }}" placeholder="Paste RegNo2 or choose from Checkin">
                </div>
                <div class="checkin-search-actions">
                    <button type="submit" class="btn package-btn-primary"><i class="fa-solid fa-magnifying-glass mr-2"></i>Open</button>
                    <a href="/key-door" class="btn package-btn-secondary">Clear</a>
                </div>
            </form>

            <div class="keydoor-stage">
                <form method="POST" action="/key-door/save" id="keyDoorForm" class="keydoor-board">
                    @csrf
                    <input type="hidden" name="RegNo" id="RegNo" value="{{ old('RegNo', $card['regno']) }}">
                    <input type="hidden" name="RegNo2" id="RegNo2" value="{{ old('RegNo2', $card['regno2']) }}">

                    <div class="keydoor-board-header">
                        <div class="keydoor-board-title">
                            <strong>Guest Information</strong>
                            <span>{{ old('RegNo2', $card['regno2']) ?: 'Belum ada detail terpilih' }}</span>
                        </div>
                        <span class="keydoor-status-pill {{ strtolower($card['access_state']) === 'active' ? 'is-active' : (strtolower($card['access_state']) === 'expired' ? 'is-expired' : '') }}" id="accessStateBadge">
                            <i class="fa-solid fa-key"></i>{{ old('AccessState', $card['access_state']) }}
                        </span>
                    </div>

                    <div class="keydoor-room-strip">
                        <span class="keydoor-room-chip" id="roomChipA">{{ old('RoomCode', $card['room_code']) ?: '-' }}</span>
                        <span class="keydoor-room-chip" id="roomChipB">{{ old('RoomCode', $card['room_code']) ?: '-' }}</span>
                        <span class="keydoor-room-chip" id="roomChipC">{{ old('RoomCode', $card['room_code']) ?: '-' }}</span>
                        <span class="keydoor-room-chip" id="roomChipD">{{ old('RoomCode', $card['room_code']) ?: '-' }}</span>
                    </div>

                    <div class="keydoor-grid">
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="RoomCode">Room</label>
                            <input type="text" name="RoomCode" id="RoomCode" class="form-control package-input" value="{{ old('RoomCode', $card['room_code']) }}" readonly>
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="SectorNumber">Sector Number</label>
                            <input type="text" name="SectorNumber" id="SectorNumber" class="form-control package-input" value="{{ old('SectorNumber', $card['sector_number']) }}">
                        </div>
                        <div class="keydoor-field is-wide">
                            <label class="keydoor-label" for="GuestName">Guest Name</label>
                            <input type="text" name="GuestName" id="GuestName" class="form-control package-input" value="{{ old('GuestName', $card['guest_name']) }}" readonly>
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="CheckInDateDisplay">Check In Date</label>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="CheckInDate" id="CheckInDate" value="{{ old('CheckInDate', $card['checkin_date']) }}">
                                <input type="text" id="CheckInDateDisplay" class="form-control package-input" value="{{ \Carbon\Carbon::parse(old('CheckInDate', $card['checkin_date']))->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric">
                                <button type="button" class="package-date-picker" data-date-button><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native value="{{ old('CheckInDate', $card['checkin_date']) }}" tabindex="-1" aria-hidden="true">
                            </div>
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="CheckInTime">Time In</label>
                            <input type="time" name="CheckInTime" id="CheckInTime" class="form-control package-input" value="{{ old('CheckInTime', $card['checkin_time']) }}">
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="CheckOutDateDisplay">Check Out Date</label>
                            <div class="package-date-group" data-date-field>
                                <input type="hidden" name="CheckOutDate" id="CheckOutDate" value="{{ old('CheckOutDate', $card['checkout_date']) }}">
                                <input type="text" id="CheckOutDateDisplay" class="form-control package-input" value="{{ \Carbon\Carbon::parse(old('CheckOutDate', $card['checkout_date']))->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric">
                                <button type="button" class="package-date-picker" data-date-button><i class="fa-regular fa-calendar"></i></button>
                                <input type="date" class="package-date-native" data-date-native value="{{ old('CheckOutDate', $card['checkout_date']) }}" tabindex="-1" aria-hidden="true">
                            </div>
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="CheckOutTime">Time Out</label>
                            <input type="time" name="CheckOutTime" id="CheckOutTime" class="form-control package-input" value="{{ old('CheckOutTime', $card['checkout_time']) }}">
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="DepositAmountDisplay">Deposit</label>
                            <input type="text" name="DepositAmount" id="DepositAmountDisplay" class="form-control package-input text-right" value="{{ number_format((float) old('DepositAmount', $card['deposit_amount']), 0, ',', '.') }}" inputmode="numeric">
                        </div>
                        <div class="keydoor-field">
                            <label class="keydoor-label" for="CardUid">Card UID</label>
                            <input type="text" name="CardUid" id="CardUid" class="form-control package-input" value="{{ old('CardUid', $card['card_uid']) }}">
                        </div>
                        <div class="keydoor-field is-wide">
                            <label class="keydoor-label" for="Notes">Notes</label>
                            <textarea name="Notes" id="Notes" class="form-control package-input" rows="3">{{ old('Notes', $card['notes']) }}</textarea>
                        </div>
                    </div>
                </form>

                <div class="keydoor-side">
                    <div class="keydoor-action-card">
                        <h4>Command Panel</h4>
                        <p>Gunakan tombol di bawah untuk simpan payload kartu, tulis akses kamar, baca ulang, atau verifikasi masa aktif kartu.</p>
                        <div class="keydoor-action-grid">
                            <button type="button" class="btn package-btn-primary" data-door-action="save">Kirim</button>
                            <button type="button" class="btn package-btn-primary" data-door-action="write">Write Sector</button>
                            <button type="button" class="btn package-btn-secondary" id="readSectorButton">Read Sector</button>
                            <button type="button" class="btn package-btn-secondary" data-door-action="verify">Verify</button>
                        </div>
                    </div>

                    <div class="keydoor-action-card">
                        <h4>Access Information</h4>
                        <div class="keydoor-meta">
                            <div class="keydoor-meta-row"><span>Status</span><strong id="statusValue">{{ old('Status', $card['status']) }}</strong></div>
                            <div class="keydoor-meta-row"><span>Access</span><strong id="accessStateValue">{{ old('AccessState', $card['access_state']) }}</strong></div>
                            <div class="keydoor-meta-row"><span>Expired At</span><strong id="expiredAtValue">{{ \Carbon\Carbon::createFromFormat('Y-m-d H:i', old('CheckOutDate', $card['checkout_date']) . ' ' . old('CheckOutTime', $card['checkout_time']))->format('d-m-Y H:i') }}</strong></div>
                            <div class="keydoor-meta-row"><span>Written</span><strong>{{ $card['written_at'] ? \Carbon\Carbon::parse($card['written_at'])->format('d-m-Y H:i') : '-' }}</strong></div>
                            <div class="keydoor-meta-row"><span>Verified</span><strong>{{ $card['last_verified_at'] ? \Carbon\Carbon::parse($card['last_verified_at'])->format('d-m-Y H:i') : '-' }}</strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="package-shell keydoor-shell">
        <div class="package-shell-body">
            <div class="checkin-directory-head">
                <div>
                    <h3 class="package-grid-title mb-1">Recent Card Access</h3>
                    <p class="package-grid-note mb-0">Klik salah satu baris untuk membuka kembali data kartu akses yang sudah tersimpan.</p>
                </div>
            </div>
            <div class="package-table-wrap">
                <table class="table package-table keydoor-list-table mb-0">
                    <thead>
                        <tr>
                            <th>RegNo2</th>
                            <th>Room</th>
                            <th>Guest</th>
                            <th>Sector</th>
                            <th>Checkout</th>
                            <th>Deposit</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $item)
                            <tr onclick="window.location='/key-door?detail_key={{ urlencode($item->regno2) }}'">
                                <td><span class="package-code">{{ $item->regno2 }}</span></td>
                                <td><span class="room-pill">{{ $item->room_code }}</span></td>
                                <td>{{ $item->guest_name }}</td>
                                <td>{{ $item->sector_number ?: '-' }}</td>
                                <td>{{ $item->checkout_display }}</td>
                                <td class="text-right">Rp {{ $item->deposit_display }}</td>
                                <td><span class="status-tag {{ strtolower($item->access_state) === 'active' ? 'is-active' : (strtolower($item->access_state) === 'expired' ? 'is-expired' : '') }}">{{ $item->status }} / {{ $item->access_state }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="package-empty">Belum ada data akses kartu yang tersimpan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script>
function normalizeNumber(value){return (value||'').toString().replace(/[^\d]/g,'');}
function formatRibuan(value){const normalized=normalizeNumber(value); return normalized ? normalized.replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '';}
function formatDisplayDate(value){if(!value){return '';} const normalized=value.toString().trim().replace(/\//g,'-'); const parts=normalized.split('-'); if(parts.length===3 && parts[0].length===4){return [parts[2],parts[1],parts[0]].join('-');} if(parts.length===3 && parts[2].length===4){return [parts[0].padStart(2,'0'),parts[1].padStart(2,'0'),parts[2]].join('-');} return value;}
function normalizeDisplayDate(value){const normalized=(value||'').toString().trim().replace(/\//g,'-'); if(!normalized){return '';} const parts=normalized.split('-'); if(parts.length!==3){return '';} const day=parts[0].padStart(2,'0'); const month=parts[1].padStart(2,'0'); const year=parts[2]; if(year.length!==4){return '';} return year + '-' + month + '-' + day;}
const keyDoorForm=document.getElementById('keyDoorForm');
const regNo2Field=document.getElementById('RegNo2');
const roomCodeField=document.getElementById('RoomCode');
const guestNameField=document.getElementById('GuestName');
const depositField=document.getElementById('DepositAmountDisplay');
const roomChips=['roomChipA','roomChipB','roomChipC','roomChipD'].map(id => document.getElementById(id));
const checkOutDateField=document.getElementById('CheckOutDate');
const checkOutDateDisplay=document.getElementById('CheckOutDateDisplay');
const checkOutTimeField=document.getElementById('CheckOutTime');
const expiredAtValue=document.getElementById('expiredAtValue');
const readSectorButton=document.getElementById('readSectorButton');
function bindDateGroup(group){const hidden=group.querySelector('input[type="hidden"]'); const display=group.querySelector('input[type="text"]'); const native=group.querySelector('[data-date-native]'); const button=group.querySelector('[data-date-button]'); if(!hidden||!display||!native){return;} display.addEventListener('blur', function(){if(!this.value.trim()){hidden.value=''; native.value=''; return;} const iso=normalizeDisplayDate(this.value); if(!iso){showCrudAlert('Tanggal harus memakai format dd-MM-yyyy.', 'Key Door'); this.focus(); return;} hidden.value=iso; native.value=iso; this.value=formatDisplayDate(iso); updateExpiredPreview();}); native.addEventListener('change', function(){hidden.value=this.value||''; display.value=formatDisplayDate(this.value); updateExpiredPreview();}); if(button){button.addEventListener('click', function(){if(typeof native.showPicker === 'function'){native.showPicker();} else {native.focus(); native.click();}});} display.value=formatDisplayDate(hidden.value); native.value=hidden.value;}
function updateRoomChips(){const value=(roomCodeField.value || '-').toUpperCase(); roomChips.forEach(chip => chip.textContent=value);}
function updateExpiredPreview(){if(!checkOutDateField.value){expiredAtValue.textContent='-'; return;} expiredAtValue.textContent=formatDisplayDate(checkOutDateField.value) + ' ' + (checkOutTimeField.value || '00:00');}
Array.from(document.querySelectorAll('[data-date-field]')).forEach(bindDateGroup);
depositField.addEventListener('input', function(){this.value=formatRibuan(this.value);});
roomCodeField.addEventListener('input', updateRoomChips);
checkOutTimeField.addEventListener('input', updateExpiredPreview);
readSectorButton.addEventListener('click', function(){if(!regNo2Field.value.trim()){showCrudAlert('Pilih detail check in terlebih dahulu.', 'Key Door'); return;} window.location='/key-door?detail_key='+encodeURIComponent(regNo2Field.value.trim());});
Array.from(document.querySelectorAll('[data-door-action]')).forEach(function(button){button.addEventListener('click', function(){if(!regNo2Field.value.trim()){showCrudAlert('Pilih detail check in terlebih dahulu.', 'Key Door'); return;} if(!keyDoorForm){return;} if(this.dataset.doorAction === 'write'){keyDoorForm.action='/key-door/write';} else if(this.dataset.doorAction === 'verify'){keyDoorForm.action='/key-door/verify';} else {keyDoorForm.action='/key-door/save';} if(checkOutDateDisplay.value.trim()){const iso=normalizeDisplayDate(checkOutDateDisplay.value); if(iso){checkOutDateField.value=iso;}} depositField.value=normalizeNumber(depositField.value); keyDoorForm.submit();});});
updateRoomChips();
updateExpiredPreview();
const successAlert=document.getElementById('successAlert'); if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);}
</script>

@endsection