@extends('layouts.app')

@section('title', 'Automatic Package')

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

<style>
.content-wrapper { background: radial-gradient(circle at top right, rgba(183,148,92,.12), transparent 22%), radial-gradient(circle at left top, rgba(17,24,39,.08), transparent 28%), linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%); min-height: 100vh; }
.content-wrapper > h3 { display:none; }
.package-page { padding:0 0 2rem; color:#10233b; }
.package-shell { background:rgba(255,255,255,.72); border:1px solid rgba(255,255,255,.6); box-shadow:0 18px 50px rgba(16,35,59,.1); backdrop-filter:blur(16px); border-radius:24px; overflow:hidden; }
.package-shell-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.35rem 1.5rem 1rem; border-bottom:1px solid rgba(16,35,59,.08); }
.package-shell-title { margin:0; font-size:1.1rem; font-weight:700; color:#10233b; }
.package-shell-subtitle { margin:.35rem 0 0; font-size:.9rem; color:#5f6f84; }
.package-shell-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem .8rem; border-radius:999px; background:rgba(179,138,81,.12); color:#8b6232; font-weight:700; font-size:.82rem; }
.package-shell-body { padding:1.5rem; }
.package-label { display:block; font-size:.84rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#5f6f84; margin-bottom:.55rem; }
.package-input { height:calc(2.6rem + 2px); border-radius:14px; border:1px solid rgba(16,35,59,.12); box-shadow:inset 0 1px 2px rgba(16,35,59,.04); background:rgba(255,255,255,.92); color:#10233b; font-weight:600; }
.package-input:focus { border-color:rgba(179,138,81,.78); box-shadow:0 0 0 .2rem rgba(179,138,81,.14); }
.package-btn-primary { border:0; border-radius:999px; padding:.75rem 1.4rem; font-weight:700; background:linear-gradient(135deg,#173761 0%,#1e4b80 55%,#b38a51 150%); box-shadow:0 12px 26px rgba(23,55,97,.2); color:#fff; }
.package-alert, .package-error { border:0; border-radius:18px; padding:.95rem 1.15rem; box-shadow:0 14px 30px rgba(16,35,59,.08); margin-bottom:1.2rem; }
.package-alert { background:linear-gradient(135deg, rgba(33,150,83,.16), rgba(33,150,83,.08)); color:#1c6b40; }
.package-error { background:linear-gradient(135deg, rgba(179,52,70,.16), rgba(179,52,70,.08)); color:#8f2435; }
.formula-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.85rem; margin-bottom:1rem; }
.formula-option { display:flex; align-items:center; gap:.7rem; padding:.9rem 1rem; border-radius:16px; border:1px solid rgba(16,35,59,.1); background:rgba(255,255,255,.72); cursor:pointer; }
.formula-option strong { display:block; color:#10233b; font-size:.92rem; }
.formula-option span { display:block; color:#6b7b90; font-size:.78rem; }
.package-preview { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; margin-bottom:1rem; }
.preview-card { border-radius:20px; background:linear-gradient(180deg, rgba(16,35,59,.04), rgba(16,35,59,.08)); border:1px solid rgba(16,35,59,.08); padding:1rem; }
.preview-card h4 { margin:0 0 .75rem; font-size:.96rem; font-weight:700; color:#173761; }
.preview-meta { display:grid; gap:.65rem; }
.preview-field small { display:block; color:#6b7b90; text-transform:uppercase; letter-spacing:.08em; font-size:.72rem; margin-bottom:.25rem; }
.preview-field strong { display:block; color:#10233b; font-size:.95rem; word-break:break-word; }
.process-notes { margin:1rem 0 0; padding-left:1.1rem; color:#5f6f84; }
.process-notes li + li { margin-top:.45rem; }
@media (max-width:767.98px){ .formula-grid, .package-preview { grid-template-columns:1fr; } }
</style>

<div class="container-fluid package-page">
    @if(session('success'))<div class="alert package-alert" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error">{{ session('error') }}</div>@endif

    <section class="package-shell">
        <div class="package-shell-header">
            <div>
                <h2 class="package-shell-title">Automatic Package Process</h2>
                <p class="package-shell-subtitle">Converted from the legacy VB6 formula flow so the user can generate package items and package transactions through one fast process.</p>
            </div>
            <span class="package-shell-badge">VB6 Logic</span>
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
                    <div class="form-group col-md-6"><label class="package-label" for="Expired">Expired</label><input type="date" name="Expired" id="Expired" class="form-control package-input" value="{{ old('Expired', $process['expired']) }}" required></div>
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
const nominalField=document.getElementById('Nominal'); const packageCodeField=document.getElementById('PackageCode'); const processButton=document.getElementById('processButton');
function defaultPackageCodeFromNominal(){const nominal=formatRibuan(nominalField.value); if(!nominal){return '';} return 'P- ' + nominal;}
nominalField.addEventListener('input', function(){this.value=formatRibuan(this.value.replace(/\D/g,'')); if(!packageCodeField.dataset.manual||packageCodeField.dataset.manual==='0'){packageCodeField.value=defaultPackageCodeFromNominal();}});
packageCodeField.addEventListener('input', function(){this.dataset.manual=this.value.trim()!==''?'1':'0';});
if(packageCodeField.value.trim()!==''){packageCodeField.dataset.manual='1';} else {packageCodeField.value=defaultPackageCodeFromNominal(); packageCodeField.dataset.manual='0';}
if(nominalField.value){nominalField.value=formatRibuan(nominalField.value);} processButton.addEventListener('click', function(){nominalField.value=unformat(nominalField.value); packageCodeField.value=packageCodeField.value.trim().toUpperCase();});
const successAlert=document.getElementById('successAlert'); if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);} nominalField.focus();
</script>

@endsection
