@extends('layouts.app')

@section('title', 'Item Package - Global')

@section('content')

@php
    $avgSellingPrice = $items->avg('Hj') ?? 0;
@endphp

<style>
    .content-wrapper { background: radial-gradient(circle at top right, rgba(183,148,92,.12), transparent 22%), radial-gradient(circle at left top, rgba(17,24,39,.08), transparent 28%), linear-gradient(180deg, #f8f4ec 0%, #eef1f6 45%, #e7edf5 100%); min-height: 100vh; }
    .content-wrapper > h3 { display:none; }
    .package-page { padding:0 0 2rem; color:#10233b; }
    .package-hero { position:relative; overflow:hidden; background:linear-gradient(135deg,#10233b 0%,#19395f 55%,#b38a51 140%); border-radius:24px; color:#fff; padding:2rem; margin-bottom:1.5rem; box-shadow:0 24px 60px rgba(16,35,59,.2); }
    .package-hero::after { content:''; position:absolute; top:-80px; right:-20px; width:240px; height:240px; border-radius:50%; background:radial-gradient(circle, rgba(255,255,255,.22), rgba(255,255,255,0)); }
    .package-kicker { display:inline-flex; align-items:center; gap:.55rem; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); padding:.45rem .85rem; border-radius:999px; font-size:.76rem; font-weight:700; letter-spacing:.16em; text-transform:uppercase; margin-bottom:1rem; }
    .package-hero h1 { font-size:2.2rem; font-weight:700; line-height:1.1; margin:0 0 .75rem; }
    .package-hero p { max-width:760px; margin:0; color:rgba(255,255,255,.82); font-size:1rem; }
    .package-summary { margin-top:1.5rem; }
    .package-stat { background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15); border-radius:18px; padding:1rem 1.1rem; backdrop-filter:blur(12px); min-height:100%; }
    .package-stat-label { display:block; font-size:.78rem; text-transform:uppercase; letter-spacing:.12em; color:rgba(255,255,255,.72); margin-bottom:.5rem; }
    .package-stat-value { display:block; font-size:1.35rem; font-weight:700; color:#fff; }
    .package-shell { background:rgba(255,255,255,.72); border:1px solid rgba(255,255,255,.6); box-shadow:0 18px 50px rgba(16,35,59,.1); backdrop-filter:blur(16px); border-radius:24px; overflow:hidden; }
    .package-shell + .package-shell { margin-top:1.5rem; }
    .package-shell-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.35rem 1.5rem 1rem; border-bottom:1px solid rgba(16,35,59,.08); }
    .package-shell-title { margin:0; font-size:1.1rem; font-weight:700; color:#10233b; }
    .package-shell-subtitle { margin:.35rem 0 0; font-size:.9rem; color:#5f6f84; }
    .package-shell-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem .8rem; border-radius:999px; background:rgba(179,138,81,.12); color:#8b6232; font-weight:700; font-size:.82rem; }
    .package-shell-body { padding:1.5rem; }
    .package-label { display:block; font-size:.84rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#5f6f84; margin-bottom:.55rem; }
    .package-input, .package-select { height:calc(2.6rem + 2px); border-radius:14px; border:1px solid rgba(16,35,59,.12); box-shadow:inset 0 1px 2px rgba(16,35,59,.04); background:rgba(255,255,255,.92); color:#10233b; font-weight:600; }
    .package-input:focus, .package-select:focus { border-color:rgba(179,138,81,.78); box-shadow:0 0 0 .2rem rgba(179,138,81,.14); }
    .package-actions { display:flex; gap:.75rem; margin-top:.5rem; flex-wrap:wrap; }
    .package-btn-primary { border:0; border-radius:999px; padding:.75rem 1.4rem; font-weight:700; background:linear-gradient(135deg,#173761 0%,#1e4b80 55%,#b38a51 150%); box-shadow:0 12px 26px rgba(23,55,97,.2); color:#fff; }
    .package-btn-secondary { border-radius:999px; padding:.75rem 1.3rem; font-weight:700; border:1px solid rgba(16,35,59,.12); background:rgba(255,255,255,.78); color:#173761; }
    .package-alert, .package-error { border:0; border-radius:18px; padding:.95rem 1.15rem; box-shadow:0 14px 30px rgba(16,35,59,.08); }
    .package-alert { background:linear-gradient(135deg, rgba(33,150,83,.16), rgba(33,150,83,.08)); color:#1c6b40; }
    .package-error { background:linear-gradient(135deg, rgba(179,52,70,.16), rgba(179,52,70,.08)); color:#8f2435; }
    .package-table-wrap { border-radius:0 0 24px 24px; overflow:hidden; }
    .package-table { margin-bottom:0; }
    .package-table thead th { border-top:0; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.02), rgba(16,35,59,.06)); color:#53657d; text-transform:uppercase; letter-spacing:.08em; font-size:.76rem; font-weight:700; padding:1rem 1.2rem; }
    .package-table tbody tr { transition:transform .18s ease, box-shadow .18s ease, background-color .18s ease; cursor:pointer; }
    .package-table tbody tr:nth-child(odd) { background:rgba(16,35,59,.045); }
    .package-table tbody tr:nth-child(even) { background:rgba(255,255,255,.96); }
    .package-table tbody tr:hover { background:rgba(179,138,81,.06); transform:translateY(-1px); box-shadow:inset 4px 0 0 #b38a51; }
    .package-table tbody td { border-top:1px solid rgba(16,35,59,.06); padding:1rem 1.2rem; vertical-align:middle; color:#10233b; }
    .package-code { display:inline-flex; align-items:center; min-width:88px; justify-content:center; padding:.45rem .7rem; border-radius:999px; background:rgba(23,55,97,.08); color:#173761; font-weight:700; letter-spacing:.06em; }
    .kind-pill { display:inline-flex; padding:.42rem .7rem; border-radius:999px; font-size:.78rem; font-weight:700; }
    .kind-room { background:rgba(30,140,88,.12); color:#126b45; }
    .kind-restaurant { background:rgba(196,134,27,.14); color:#9b6616; }
    .package-delete { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; background:rgba(178,34,34,.08); color:#aa2f2f; border:1px solid rgba(178,34,34,.12); text-decoration:none; font-size:1rem; transition:all .18s ease; }
    .package-delete:hover { background:#aa2f2f; color:#fff; text-decoration:none; transform:translateY(-1px); }
    .package-empty { text-align:center; padding:2.2rem 1rem; color:#6b7b90; }
</style>

<div class="container-fluid package-page">
    @if(session('success'))<div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error mb-4">{{ session('error') }}</div>@endif

    <section class="package-hero">
        <div class="package-kicker"><span>Quantum Hotel</span><span>Item Package - Global</span></div>
        <h1>Item Package Global</h1>
        <p>Manage the global package items stored in the StockPackage table. This is the master source used by manual package transactions and the automatic package builder.</p>
        <div class="row package-summary">
            <div class="col-md-4 mb-3 mb-md-0"><div class="package-stat"><span class="package-stat-label">Total Items</span><span class="package-stat-value">{{ number_format($summary['total'], 0, ',', '.') }}</span></div></div>
            <div class="col-md-4 mb-3 mb-md-0"><div class="package-stat"><span class="package-stat-label">Room Items</span><span class="package-stat-value">{{ number_format($summary['room'], 0, ',', '.') }}</span></div></div>
            <div class="col-md-4"><div class="package-stat"><span class="package-stat-label">Average Selling Price</span><span class="package-stat-value">Rp {{ number_format($avgSellingPrice, 0, ',', '.') }}</span></div></div>
        </div>
    </section>

    <section class="package-shell">
        <div class="package-shell-header"><div><h2 class="package-shell-title">Stock Package Input</h2><p class="package-shell-subtitle">Manual CRUD for the global package item master.</p></div><span class="package-shell-badge">Master CRUD</span></div>
        <div class="package-shell-body">
            <form method="POST" action="/item-package-global" id="formStockPackage">
                @csrf
                <div class="form-group"><label class="package-label" for="KodeBrg">Item Code</label><input type="text" name="KodeBrg" id="KodeBrg" class="form-control package-input" required></div>
                <div class="form-group"><label class="package-label" for="NamaBrg">Item Name</label><input type="text" name="NamaBrg" id="NamaBrg" class="form-control package-input" required></div>
                <div class="form-row">
                    <div class="form-group col-md-6"><label class="package-label" for="Satuan">Unit</label><input type="text" name="Satuan" id="Satuan" class="form-control package-input" value="PAX"></div>
                    <div class="form-group col-md-6"><label class="package-label" for="Kind">Category</label><select name="Kind" id="Kind" class="form-control package-select"><option value="ROOM">ROOM</option><option value="RESTAURANT">RESTAURANT</option></select></div>
                </div>
                <div class="form-group"><label class="package-label" for="Hj">Selling Price</label><input type="text" name="Hj" id="Hj" class="form-control package-input text-right" inputmode="numeric" required></div>
                <div class="package-actions"><button class="btn package-btn-primary" id="saveButton">Save Item</button><button type="button" class="btn package-btn-secondary" id="resetButton">Reset Form</button></div>
            </form>
        </div>
    </section>

    <section class="package-shell">
        <div class="package-shell-header"><div><h2 class="package-shell-title">Global Item Directory</h2><p class="package-shell-subtitle">Click a row to load the item into the input form and continue in update mode.</p></div><span class="package-shell-badge">{{ number_format($summary['total'], 0, ',', '.') }} Records</span></div>
        <div class="package-table-wrap"><div class="table-responsive"><table class="table package-table" id="tableStockPackage"><thead><tr><th>Item Code</th><th>Item Name</th><th>Unit</th><th>Category</th><th class="text-right">Selling Price</th><th class="text-center" width="90">Action</th></tr></thead><tbody>
            @forelse($items as $item)
            <tr data-kode="{{ $item->KodeBrg }}" data-nama="{{ $item->NamaBrg }}" data-satuan="{{ $item->Satuan }}" data-kind="{{ $item->Kind }}" data-hj="{{ $item->Hj }}">
                <td><span class="package-code">{{ $item->KodeBrg }}</span></td>
                <td>{{ $item->NamaBrg }}</td>
                <td>{{ $item->Satuan }}</td>
                <td><span class="kind-pill {{ strtoupper(trim($item->Kind)) === 'RESTAURANT' ? 'kind-restaurant' : 'kind-room' }}">{{ $item->Kind }}</span></td>
                <td class="text-right">Rp {{ number_format($item->Hj ?? 0, 0, ',', '.') }}</td>
                <td class="text-center"><a href="/item-package-global/{{ $item->KodeBrg }}/delete" class="package-delete" title="Delete" aria-label="Delete" data-confirm-delete="Do you want to delete this package item?">&#128465;</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="package-empty">No package items yet. Create the first global item to get started.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </section>
</div>

<script>
function normalizeNumber(value){if(value===null||value===undefined){return '';}const raw=value.toString().trim();if(raw===''){return '';}if(raw.includes('.')){const [integerPart,decimalPart='']=raw.split('.');const cleanInteger=integerPart.replace(/\D/g,'');if(/^0+$/.test(decimalPart)){return cleanInteger;}}return raw.replace(/\D/g,'');}
function formatRibuan(value){const normalized=normalizeNumber(value);if(!normalized){return '';}return normalized.replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function unformat(value){return (value||'').toString().replace(/\./g,'');}
function normalizeCode(value){return (value||'').toString().trim().toUpperCase();}
const formStockPackage=document.getElementById('formStockPackage'); const kodeBrgField=document.getElementById('KodeBrg'); const namaBrgField=document.getElementById('NamaBrg'); const satuanField=document.getElementById('Satuan'); const kindField=document.getElementById('Kind'); const hjField=document.getElementById('Hj'); const saveButton=document.getElementById('saveButton'); const resetButton=document.getElementById('resetButton'); const rows=Array.from(document.querySelectorAll('#tableStockPackage tbody tr[data-kode]'));
function findRow(kode){const normalized=normalizeCode(kode); return rows.find((row)=>normalizeCode(row.dataset.kode)===normalized)||null;}
function loadRow(row){kodeBrgField.value=row.dataset.kode; namaBrgField.value=row.dataset.nama; satuanField.value=row.dataset.satuan||'PAX'; kindField.value=normalizeCode(row.dataset.kind||'ROOM'); hjField.value=formatRibuan(row.dataset.hj||''); kodeBrgField.readOnly=true; formStockPackage.action='/item-package-global/'+row.dataset.kode+'/update'; saveButton.textContent='Update Item'; resetButton.textContent='Cancel Edit'; namaBrgField.focus();}
hjField.addEventListener('input', function(){this.value=formatRibuan(this.value.replace(/\D/g,''));});
[kodeBrgField,namaBrgField,satuanField,hjField].forEach((field,index,allFields)=>{field.addEventListener('keydown', function(event){if(event.key!=='Enter'){return;} event.preventDefault(); if(field===kodeBrgField){const existing=findRow(kodeBrgField.value); if(existing){loadRow(existing); return;} kodeBrgField.value=normalizeCode(kodeBrgField.value);} if(index<allFields.length-1){allFields[index+1].focus(); if(typeof allFields[index+1].select==='function'){allFields[index+1].select();} return;} formStockPackage.requestSubmit();});});
document.querySelector('#tableStockPackage tbody').addEventListener('click', function(event){if(event.target.closest('a')){return;} const row=event.target.closest('tr'); if(!row||!row.dataset.kode){return;} loadRow(row);});
resetButton.addEventListener('click', function(){formStockPackage.reset(); kodeBrgField.readOnly=false; formStockPackage.action='/item-package-global'; saveButton.textContent='Save Item'; resetButton.textContent='Reset Form'; satuanField.value='PAX'; kindField.value='ROOM'; kodeBrgField.focus();});
formStockPackage.addEventListener('submit', function(){kodeBrgField.value=normalizeCode(kodeBrgField.value); namaBrgField.value=normalizeCode(namaBrgField.value); satuanField.value=normalizeCode(satuanField.value); kindField.value=normalizeCode(kindField.value); hjField.value=unformat(hjField.value);});
const successAlert=document.getElementById('successAlert'); if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);} kodeBrgField.focus();
</script>

@endsection
