@extends('layouts.app')

@section('title', '')

@section('content')

@php
    $process = $processResult ?? [
        'room_code' => '',
        'room_name' => 'Room',
        'room_amount' => 0,
        'restaurant_code' => '',
        'restaurant_name' => 'Restaurant',
        'restaurant_amount' => 0,
        'package_code' => old('PackageCode', ''),
        'expired' => old('Expired', now()->format('Y-m-d')),
        'formula' => old('Formula', 'GROUP'),
        'nofak' => '',
    ];
@endphp

@include('partials.crud-package-theme')

<style>
.formula-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
    margin-bottom: 1rem;
}

.formula-option {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.9rem 1rem;
    border-radius: 16px;
    border: 1px solid rgba(199, 165, 106, 0.3);
    background: rgba(255, 255, 255, 0.72);
    cursor: pointer;
}

.formula-option strong {
    display: block;
    color: #10233b;
    font-size: 0.92rem;
}

.formula-option span {
    display: block;
    color: #6b7b90;
    font-size: 0.78rem;
}

.package-preview {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.preview-card {
    border-radius: 20px;
    background: linear-gradient(180deg, rgba(233, 213, 162, 0.18), rgba(255, 255, 255, 0.85));
    border: 1px solid rgba(199, 165, 106, 0.24);
    padding: 1rem;
}

.preview-card h4 {
    margin: 0 0 0.75rem;
    font-size: 0.96rem;
    font-weight: 700;
    color: #173761;
}

.preview-meta {
    display: grid;
    gap: 0.65rem;
}

.preview-field small {
    display: block;
    color: #8f6a2d;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.72rem;
    margin-bottom: 0.25rem;
}

.preview-field strong {
    display: block;
    color: #10233b;
    font-size: 0.95rem;
    word-break: break-word;
}

.process-notes {
    margin: 1rem 0 0;
    padding-left: 1.1rem;
    color: #5f6f84;
}

.process-notes li + li {
    margin-top: 0.45rem;
}

@media (max-width: 767.98px) {
    .formula-grid,
    .package-preview {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container-fluid package-page">
    @if(session('success'))<div class="alert package-alert" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error">{{ session('error') }}</div>@endif

    <section class="package-shell">
        <div class="package-shell-header">
            <div class="package-shell-heading-block">
                <h1 class="package-shell-title">Automatic Package</h1>
                <p class="package-shell-subtitle">Proses formula otomatis sekarang memakai shell CRUD yang sama dengan Package Transaction, jadi preview hasil dan form proses tetap satu bahasa visual.</p>
            </div>
        </div>
        <div class="package-shell-body">
            <div class="package-preview">
                <div class="preview-card"><h4>Room Item Output</h4><div class="preview-meta"><div class="preview-field"><small>Room Item Code</small><strong>{{ $process['room_code'] ?: '-' }}</strong></div><div class="preview-field"><small>Item Name 1</small><strong>{{ $process['room_name'] ?: 'Room' }}</strong></div><div class="preview-field"><small>Room Amount</small><strong>Rp {{ number_format($process['room_amount'] ?? 0, 0, ',', '.') }}</strong></div></div></div>
                <div class="preview-card"><h4>Restaurant Item Output</h4><div class="preview-meta"><div class="preview-field"><small>Restaurant Item Code</small><strong>{{ $process['restaurant_code'] ?: '-' }}</strong></div><div class="preview-field"><small>Item Name 2</small><strong>{{ $process['restaurant_name'] ?: 'Restaurant' }}</strong></div><div class="preview-field"><small>Restaurant Amount</small><strong>Rp {{ number_format($process['restaurant_amount'] ?? 0, 0, ',', '.') }}</strong></div></div></div>
            </div>

            <form method="POST" action="/automatic-package/process" id="formAutomaticPackage">
                @csrf
                <label class="package-label">Purpose Of The Formula</label>
                <div class="formula-grid">
                    <label class="formula-option"><input type="radio" name="Formula" value="GROUP" {{ ($process['formula'] ?? 'GROUP') === 'GROUP' ? 'checked' : '' }}><div><strong>GROUP [60 - 40]</strong><span>Room 60% and Restaurant 40%</span></div></label>
                    <label class="formula-option"><input type="radio" name="Formula" value="ROOM_ONLY" {{ ($process['formula'] ?? '') === 'ROOM_ONLY' ? 'checked' : '' }}><div><strong>ROOM ONLY [100 - 0]</strong><span>Full amount goes to room</span></div></label>
                    <label class="formula-option"><input type="radio" name="Formula" value="OTA" {{ ($process['formula'] ?? '') === 'OTA' ? 'checked' : '' }}><div><strong>OTA [100K]</strong><span>Restaurant keeps 100,000</span></div></label>
                    <label class="formula-option"><input type="radio" name="Formula" value="EXECUTIVE" {{ ($process['formula'] ?? '') === 'EXECUTIVE' ? 'checked' : '' }}><div><strong>EXECUTIVE [200K]</strong><span>Restaurant keeps 200,000</span></div></label>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6"><label class="package-label" for="Nominal">Nominal Package</label><input type="text" name="Nominal" id="Nominal" class="form-control package-input text-right" inputmode="numeric" value="{{ old('Nominal') }}" required></div>
                    <div class="form-group col-md-6"><label class="package-label" for="PackageCode">Package Code</label><input type="text" name="PackageCode" id="PackageCode" class="form-control package-input" value="{{ old('PackageCode', $process['package_code']) }}"></div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6"><label class="package-label" for="ExpiredDisplay">Expired</label><div class="package-date-group"><input type="hidden" name="Expired" id="Expired" value="{{ old('Expired', $process['expired']) }}"><input type="text" id="ExpiredDisplay" class="form-control package-input" value="{{ \Carbon\Carbon::parse(old('Expired', $process['expired']))->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric" required><button type="button" class="package-date-picker" id="expiredPickerButton" aria-label="Open system date picker"><i class="fa-regular fa-calendar"></i></button><input type="date" id="ExpiredNative" class="package-date-native" value="{{ old('Expired', $process['expired']) }}" min="{{ now()->format('Y-m-d') }}" tabindex="-1" aria-hidden="true"></div></div>
                    <div class="form-group col-md-6"><label class="package-label">Generated Package Invoice</label><input type="text" class="form-control package-input" value="{{ $process['nofak'] ?: 'Generated after process' }}" readonly></div>
                </div>
                <button class="btn package-btn-primary" id="processButton">Process Package</button>
            </form>

            <ol class="process-notes">
                <li>Default expired date is today and can be changed as needed.</li>
                <li>Package code is generated from the package nominal and can still be edited manually.</li>
                <li>The same nominal and expired date combination will be rejected to avoid duplicate packages.</li>
            </ol>
        </div>
    </section>
</div>

<script>
function normalizeNumber(value){if(value===null||value===undefined){return '';}const raw=value.toString().trim();if(raw===''){return '';}if(raw.includes('.')){const [integerPart,decimalPart='']=raw.split('.');const cleanInteger=integerPart.replace(/\D/g,'');if(/^0+$/.test(decimalPart)){return cleanInteger;}}return raw.replace(/\D/g,'');}
function formatRibuan(value){const normalized=normalizeNumber(value);if(!normalized){return '';}return normalized.replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function unformat(value){return (value||'').toString().replace(/\./g,'');}
function formatDisplayDate(value){if(!value){return '';} const normalized=value.toString().trim().replace(/\//g,'-'); const parts=normalized.split('-'); if(parts.length===3 && parts[0].length===4){return [parts[2],parts[1],parts[0]].join('-');} if(parts.length===3 && parts[2].length===4){return [parts[0].padStart(2,'0'),parts[1].padStart(2,'0'),parts[2]].join('-');} return value;}
function normalizeDisplayDate(value){const normalized=(value||'').toString().trim().replace(/\//g,'-'); const parts=normalized.split('-'); if(parts.length!==3){return '';} const day=parts[0].padStart(2,'0'); const month=parts[1].padStart(2,'0'); const year=parts[2]; if(year.length!==4){return '';} const iso=year + '-' + month + '-' + day; const testDate=new Date(iso + 'T00:00:00'); if(Number.isNaN(testDate.getTime())){return '';} return testDate.getFullYear().toString()===year && (testDate.getMonth()+1).toString().padStart(2,'0')===month && testDate.getDate().toString().padStart(2,'0')===day ? iso : '';}
function showDateNotice(message){if(typeof window.showCrudNotice === 'function'){window.showCrudNotice(message, 'Invalid Date'); return;} window.alert(message);}
function isNotPastDate(value){return value && value >= todayIso;}
const todayIso='{{ now()->format('Y-m-d') }}';
const processForm=document.getElementById('formAutomaticPackage');
const nominalField=document.getElementById('Nominal');
const packageCodeField=document.getElementById('PackageCode');
const expiredField=document.getElementById('Expired');
const expiredDisplayField=document.getElementById('ExpiredDisplay');
const expiredNativeField=document.getElementById('ExpiredNative');
const expiredPickerButton=document.getElementById('expiredPickerButton');
function defaultPackageCodeFromNominal(){const nominal=formatRibuan(nominalField.value); if(!nominal){return '';} return 'P- ' + nominal;}
nominalField.addEventListener('input', function(){this.value=formatRibuan(this.value.replace(/\D/g,'')); if(!packageCodeField.dataset.manual||packageCodeField.dataset.manual==='0'){packageCodeField.value=defaultPackageCodeFromNominal();}});
packageCodeField.addEventListener('input', function(){this.dataset.manual=this.value.trim()!==''?'1':'0';});
if(packageCodeField.value.trim()!==''){packageCodeField.dataset.manual='1';} else {packageCodeField.value=defaultPackageCodeFromNominal(); packageCodeField.dataset.manual='0';}
if(nominalField.value){nominalField.value=formatRibuan(nominalField.value);} 
expiredDisplayField.addEventListener('blur', function(){if(!this.value){return;} const normalizedExpired=normalizeDisplayDate(this.value); if(!normalizedExpired){showDateNotice('Expired date must use format dd-MM-yyyy.'); this.focus(); return;} if(!isNotPastDate(normalizedExpired)){showDateNotice('Expired date must be greater than or equal to today.'); this.value=formatDisplayDate(expiredField.value || todayIso); this.focus(); return;} expiredField.value=normalizedExpired; expiredNativeField.value=normalizedExpired; this.value=formatDisplayDate(normalizedExpired);});
expiredPickerButton.addEventListener('click', function(){if(typeof expiredNativeField.showPicker === 'function'){expiredNativeField.showPicker();} else {expiredNativeField.focus(); expiredNativeField.click();}});
expiredNativeField.addEventListener('change', function(){if(!this.value){return;} if(!isNotPastDate(this.value)){showDateNotice('Expired date must be greater than or equal to today.'); this.value=expiredField.value || todayIso; expiredDisplayField.value=formatDisplayDate(this.value); return;} expiredField.value=this.value; expiredDisplayField.value=formatDisplayDate(this.value);});
processForm.addEventListener('submit', function(event){const normalizedExpired=normalizeDisplayDate(expiredDisplayField.value); if(!normalizedExpired){event.preventDefault(); showDateNotice('Expired date must use format dd-MM-yyyy.'); expiredDisplayField.focus(); return;} if(!isNotPastDate(normalizedExpired)){event.preventDefault(); showDateNotice('Expired date must be greater than or equal to today.'); expiredDisplayField.focus(); return;} expiredField.value=normalizedExpired; nominalField.value=unformat(nominalField.value); packageCodeField.value=packageCodeField.value.trim().toUpperCase();});
expiredDisplayField.value=formatDisplayDate(expiredField.value);
expiredNativeField.value=expiredField.value;
const successAlert=document.getElementById('successAlert'); if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);} nominalField.focus();
</script>

@endsection
