@extends('layouts.app')

@section('title', 'Package Transactions')

@section('content')

@php
    $avgNominal = $summary['total'] > 0 ? ($summary['nominal'] / $summary['total']) : 0;
    $oldCodes = old('ItemCode', []);
    $oldQtys = old('Qty', []);
    $oldPrices = old('Price', []);
    $initialRows = [];

    if (!empty($oldCodes)) {
        foreach ($oldCodes as $index => $code) {
            $initialRows[] = [
                'kode' => trim((string) $code),
                'qty' => $oldQtys[$index] ?? '1',
                'price' => $oldPrices[$index] ?? '',
            ];
        }
    }

    while (count($initialRows) < 3) {
        $initialRows[] = [
            'kode' => '',
            'qty' => '1',
            'price' => '',
        ];
    }
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
.package-actions { display:flex; gap:.75rem; margin-top:.75rem; flex-wrap:wrap; }
.package-btn-primary { border:0; border-radius:999px; padding:.75rem 1.4rem; font-weight:700; background:linear-gradient(135deg,#173761 0%,#1e4b80 55%,#b38a51 150%); box-shadow:0 12px 26px rgba(23,55,97,.2); color:#fff; }
.package-btn-secondary { border-radius:999px; padding:.75rem 1.3rem; font-weight:700; border:1px solid rgba(16,35,59,.12); background:rgba(255,255,255,.78); color:#173761; }
.package-btn-add { border-radius:999px; padding:.72rem 1.2rem; font-weight:700; border:1px dashed rgba(23,55,97,.22); background:rgba(23,55,97,.05); color:#173761; }
.package-btn-add:hover { background:rgba(23,55,97,.1); }
.package-alert, .package-error { border:0; border-radius:18px; padding:.95rem 1.15rem; box-shadow:0 14px 30px rgba(16,35,59,.08); }
.package-alert { background:linear-gradient(135deg, rgba(33,150,83,.16), rgba(33,150,83,.08)); color:#1c6b40; }
.package-error { background:linear-gradient(135deg, rgba(179,52,70,.16), rgba(179,52,70,.08)); color:#8f2435; }
.package-grid-wrap { border:1px solid rgba(16,35,59,.08); border-radius:22px; background:rgba(255,255,255,.58); overflow:hidden; }
.package-grid-toolbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1rem 1.15rem; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.03), rgba(16,35,59,.01)); }
.package-grid-title { margin:0; font-size:.98rem; font-weight:700; color:#173761; }
.package-grid-note { margin:.3rem 0 0; font-size:.84rem; color:#6b7b90; }
.package-grid-table-wrap { overflow:auto; }
.package-grid-table { width:100%; min-width:980px; border-collapse:separate; border-spacing:0; }
.package-grid-table thead th { position:sticky; top:0; z-index:1; border:0; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.05), rgba(16,35,59,.02)); color:#53657d; text-transform:uppercase; letter-spacing:.08em; font-size:.74rem; font-weight:700; padding:.9rem .85rem; }
.package-grid-table tbody td { border:0; border-bottom:1px solid rgba(16,35,59,.06); padding:.7rem .75rem; background:rgba(255,255,255,.72); vertical-align:middle; }
.package-grid-table tbody tr:nth-child(odd) td { background:rgba(16,35,59,.03); }
.package-grid-table tbody tr:hover td { background:rgba(179,138,81,.05); }
.package-row-index { width:72px; color:#6b7b90; font-weight:700; }
.package-grid-table .package-input,
.package-grid-table .package-select { height:calc(2.35rem + 2px); border-radius:12px; }
.package-grid-table .item-name { background:rgba(236,242,249,.9); }
.package-grid-table .line-total { font-weight:700; color:#173761; min-width:130px; display:inline-block; text-align:right; }
.package-row-remove { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; border:1px solid rgba(178,34,34,.12); background:rgba(178,34,34,.08); color:#aa2f2f; text-decoration:none; }
.package-row-remove:hover { background:#aa2f2f; color:#fff; text-decoration:none; }
.package-grid-hint { margin-top:.8rem; color:#6b7b90; font-size:.84rem; }
.package-table-wrap { border-radius:0 0 24px 24px; overflow:hidden; }
.package-table { margin-bottom:0; }
.package-table thead th { border-top:0; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.02), rgba(16,35,59,.06)); color:#53657d; text-transform:uppercase; letter-spacing:.08em; font-size:.76rem; font-weight:700; padding:1rem 1.2rem; }
.package-table tbody tr { transition:transform .18s ease, box-shadow .18s ease, background-color .18s ease; cursor:pointer; }
.package-table tbody tr:nth-child(odd) { background:rgba(16,35,59,.045); }
.package-table tbody tr:nth-child(even) { background:rgba(255,255,255,.96); }
.package-table tbody tr:hover { background:rgba(179,138,81,.06); transform:translateY(-1px); box-shadow:inset 4px 0 0 #b38a51; }
.package-table tbody td { border-top:1px solid rgba(16,35,59,.06); padding:1rem 1.2rem; vertical-align:middle; color:#10233b; }
.package-code { display:inline-flex; align-items:center; min-width:120px; justify-content:center; padding:.45rem .7rem; border-radius:999px; background:rgba(23,55,97,.08); color:#173761; font-weight:700; letter-spacing:.06em; }
.package-delete { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; background:rgba(178,34,34,.08); color:#aa2f2f; border:1px solid rgba(178,34,34,.12); text-decoration:none; font-size:1rem; transition:all .18s ease; }
.package-delete:hover { background:#aa2f2f; color:#fff; text-decoration:none; transform:translateY(-1px); }
.package-empty { text-align:center; padding:2.2rem 1rem; color:#6b7b90; }
@media (max-width:767.98px){ .package-shell-header, .package-grid-toolbar { flex-direction:column; align-items:flex-start; } }
</style>

<div class="container-fluid package-page">
    @if(session('success'))<div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error mb-4">{{ session('error') }}</div>@endif

    <section class="package-hero">
        <div class="package-kicker"><span>Quantum Hotel</span><span>Package Transactions</span></div>
        <h1>Package Transactions</h1>
        <p>Create package transactions manually by selecting items from Package Items. This flexible line-item grid behaves like a lightweight spreadsheet so users can keep adding rows as needed.</p>
        <div class="row package-summary">
            <div class="col-md-4 mb-3 mb-md-0"><div class="package-stat"><span class="package-stat-label">Total Package</span><span class="package-stat-value">{{ number_format($summary['total'], 0, ',', '.') }}</span></div></div>
            <div class="col-md-4 mb-3 mb-md-0"><div class="package-stat"><span class="package-stat-label">Total Nominal</span><span class="package-stat-value">Rp {{ number_format($summary['nominal'], 0, ',', '.') }}</span></div></div>
            <div class="col-md-4"><div class="package-stat"><span class="package-stat-label">Average Package</span><span class="package-stat-value">Rp {{ number_format($avgNominal, 0, ',', '.') }}</span></div></div>
        </div>
    </section>

    <section class="package-shell">
        <div class="package-shell-header"><div><h2 class="package-shell-title">Manual Package Transaction Input</h2><p class="package-shell-subtitle">Build package transactions with a flexible grid instead of a fixed three-line form.</p></div><span class="package-shell-badge">Transaction CRUD</span></div>
        <div class="package-shell-body">
            <form method="POST" action="/menu-package-transaction" id="formPackageTransaction">
                @csrf
                <input type="hidden" id="CurrentNofak" value="">
                <div class="form-row">
                    <div class="form-group col-md-4"><label class="package-label">Package Invoice</label><input type="text" id="DisplayNofak" class="form-control package-input" value="Generated after save" readonly></div>
                    <div class="form-group col-md-4"><label class="package-label" for="Meja">Package Code</label><input type="text" name="Meja" id="Meja" class="form-control package-input" value="{{ old('Meja') }}" required></div>
                    <div class="form-group col-md-4"><label class="package-label" for="Expired">Expired</label><input type="date" name="Expired" id="Expired" class="form-control package-input" value="{{ old('Expired', now()->format('Y-m-d')) }}" required></div>
                </div>

                <div class="package-grid-wrap">
                    <div class="package-grid-toolbar">
                        <div>
                            <h3 class="package-grid-title">Package Line Grid</h3>
                            <p class="package-grid-note">Default starts with 3 rows, but you can keep adding more lines without a fixed limit.</p>
                        </div>
                        <button type="button" class="btn package-btn-add" id="addRowButton"><i class="fa-solid fa-plus mr-2"></i>Add Row</button>
                    </div>
                    <div class="package-grid-table-wrap">
                        <table class="package-grid-table">
                            <thead>
                                <tr>
                                    <th width="70">Line</th>
                                    <th width="300">Item Code</th>
                                    <th width="260">Item Name</th>
                                    <th width="120">Qty</th>
                                    <th width="170">Price</th>
                                    <th width="170" class="text-right">Amount</th>
                                    <th width="90" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="detailGridBody"></tbody>
                        </table>
                    </div>
                </div>

                <p class="package-grid-hint">The last row stays available as a ready-to-use blank line, and totals update automatically while you type.</p>

                <div class="form-row mt-3">
                    <div class="form-group col-md-6"><label class="package-label">Total Nominal</label><input type="text" id="TotalNominal" class="form-control package-input text-right" value="0" readonly></div>
                </div>

                <div class="package-actions"><button class="btn package-btn-primary" id="saveButton">Save Package Transaction</button><button type="button" class="btn package-btn-secondary" id="resetButton">Reset Form</button></div>
            </form>
        </div>
    </section>

    <section class="package-shell">
        <div class="package-shell-header"><div><h2 class="package-shell-title">Transaction Directory</h2><p class="package-shell-subtitle">Click a row to load the transaction into the form and continue in update mode.</p></div><span class="package-shell-badge">{{ number_format($summary['total'], 0, ',', '.') }} Records</span></div>
        <div class="package-table-wrap"><div class="table-responsive"><table class="table package-table" id="tablePackageTransaction"><thead><tr><th>Invoice</th><th>Package Code</th><th>Items</th><th>Expired</th><th class="text-right">Nominal</th><th class="text-center" width="90">Action</th></tr></thead><tbody>
            @forelse($packages as $package)
            <tr data-nofak="{{ $package->Nofak }}" data-meja="{{ $package->Meja }}" data-expired="{{ \Carbon\Carbon::parse($package->Expired)->format('Y-m-d') }}" data-details='@json(json_decode($package->detail_json, true))'>
                <td><span class="package-code">{{ $package->Nofak }}</span></td>
                <td>{{ $package->Meja }}</td>
                <td>{{ $package->detail_summary }}</td>
                <td>{{ \Carbon\Carbon::parse($package->Expired)->format('d/m/Y') }}</td>
                <td class="text-right">Rp {{ number_format($package->JumlahRes ?? 0, 0, ',', '.') }}</td>
                <td class="text-center"><a href="/menu-package-transaction/{{ $package->Nofak }}/delete" class="package-delete" title="Delete" aria-label="Delete" data-confirm-delete="Do you want to delete this package transaction?">&#128465;</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="package-empty">No package transactions yet. Create the first manual package transaction to get started.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </section>
</div>

<template id="detailRowTemplate">
    <tr class="detail-grid-row" data-row>
        <td class="package-row-index" data-line-number>1</td>
        <td>
            <select name="ItemCode[]" class="form-control package-select item-code">
                <option value="">Select item</option>
                @foreach($items as $item)
                <option value="{{ $item->KodeBrg }}" data-name="{{ $item->NamaBrg }}" data-price="{{ $item->Hj }}">{{ $item->KodeBrg }} - {{ $item->NamaBrg }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="text" class="form-control package-input item-name" readonly></td>
        <td><input type="text" name="Qty[]" class="form-control package-input text-right item-qty" value="1"></td>
        <td><input type="text" name="Price[]" class="form-control package-input text-right item-price" inputmode="numeric"></td>
        <td class="text-right"><span class="line-total">Rp 0</span></td>
        <td class="text-center"><button type="button" class="package-row-remove" title="Remove line" aria-label="Remove line"><i class="fa-solid fa-trash"></i></button></td>
    </tr>
</template>

<script>
function normalizeNumber(value){if(value===null||value===undefined){return '';}const raw=value.toString().trim();if(raw===''){return '';}if(raw.includes('.')){const [integerPart,decimalPart='']=raw.split('.');const cleanInteger=integerPart.replace(/\D/g,'');if(/^0+$/.test(decimalPart)){return cleanInteger;}}return raw.replace(/\D/g,'');}
function formatRibuan(value){const normalized=normalizeNumber(value);if(!normalized){return '';}return normalized.replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function unformat(value){return (value||'').toString().replace(/\./g,'');}
function qtyValue(value){const normalized=(value||'').toString().replace(',', '.').replace(/[^\d.]/g,'');const parsed=parseFloat(normalized);return parsed>0?parsed:0;}
function escapeHtml(value){return (value||'').toString().replace(/[&<>"']/g, function(char){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[char];});}

const form=document.getElementById('formPackageTransaction');
const mejaField=document.getElementById('Meja');
const expiredField=document.getElementById('Expired');
const totalNominalField=document.getElementById('TotalNominal');
const currentNofakField=document.getElementById('CurrentNofak');
const displayNofakField=document.getElementById('DisplayNofak');
const saveButton=document.getElementById('saveButton');
const resetButton=document.getElementById('resetButton');
const addRowButton=document.getElementById('addRowButton');
const detailGridBody=document.getElementById('detailGridBody');
const rowTemplate=document.getElementById('detailRowTemplate');
const transactionTableBody=document.querySelector('#tablePackageTransaction tbody');
const initialRows=@json($initialRows);

function getRows(){return Array.from(detailGridBody.querySelectorAll('[data-row]'));}
function rowHasMeaningfulData(row){if(!row){return false;}const code=row.querySelector('.item-code')?.value?.trim()||'';const qty=row.querySelector('.item-qty')?.value?.trim()||'';const price=row.querySelector('.item-price')?.value?.trim()||'';return code!==''||qty!==''&&qty!=='1'||price!=='';}
function renameRows(){getRows().forEach((row,index)=>{row.querySelector('[data-line-number]').textContent=index+1;});}
function createRow(detail={}){
    const fragment=rowTemplate.content.cloneNode(true);
    const row=fragment.querySelector('[data-row]');
    const codeSelect=row.querySelector('.item-code');
    const nameField=row.querySelector('.item-name');
    const qtyField=row.querySelector('.item-qty');
    const priceField=row.querySelector('.item-price');
    if(detail.kode){codeSelect.value=detail.kode;}
    const selected=codeSelect.options[codeSelect.selectedIndex];
    nameField.value=detail.name || selected?.dataset.name || '';
    qtyField.value=(detail.qty ?? '1').toString();
    priceField.value=detail.price!==undefined && detail.price!==null && detail.price!=='' ? formatRibuan(detail.price) : '';
    priceField.dataset.autofill=detail.price!==undefined && detail.price!==null && detail.price!=='' ? '0' : '1';
    detailGridBody.appendChild(fragment);
    renameRows();
    updateRow(row);
    return row;
}
function ensureTrailingBlankRow(){const rows=getRows(); if(rows.length===0){createRow(); return;} const lastRow=rows[rows.length-1]; if(rowHasMeaningfulData(lastRow)){createRow({qty:'1'});} }
function updateRow(row){
    const codeSelect=row.querySelector('.item-code');
    const selected=codeSelect.options[codeSelect.selectedIndex];
    const nameField=row.querySelector('.item-name');
    const qtyField=row.querySelector('.item-qty');
    const priceField=row.querySelector('.item-price');
    const lineTotal=row.querySelector('.line-total');
    if(selected&&selected.value&&nameField.value===''){nameField.value=selected.dataset.name||'';}
    if(selected&&selected.value&&(!priceField.value||priceField.dataset.autofill==='1')){priceField.value=formatRibuan(selected.dataset.price||'0'); priceField.dataset.autofill='1';}
    if(!selected||!selected.value){nameField.value=''; if(priceField.dataset.autofill==='1'){priceField.value='';}}
    const qty=qtyValue(qtyField.value||'1');
    const price=parseFloat(unformat(priceField.value)||'0')||0;
    lineTotal.textContent='Rp '+formatRibuan(Math.round(qty*price).toString()||'0');
    return qty*price;
}
function updateTotals(){let total=0; getRows().forEach((row)=>{total+=updateRow(row);}); totalNominalField.value=formatRibuan(Math.round(total).toString()); ensureTrailingBlankRow(); renameRows();}
function resetGrid(details=[]){detailGridBody.innerHTML=''; const rows=(details&&details.length)?details:initialRows; rows.forEach((detail)=>createRow(detail)); while(getRows().length<3){createRow({qty:'1'});} ensureTrailingBlankRow(); updateTotals();}

addRowButton.addEventListener('click', function(){const row=createRow({qty:'1'}); const select=row.querySelector('.item-code'); if(select){select.focus();}});

detailGridBody.addEventListener('change', function(event){const row=event.target.closest('[data-row]'); if(!row){return;} if(event.target.classList.contains('item-code')){const selected=event.target.options[event.target.selectedIndex]; row.querySelector('.item-name').value=selected?.dataset.name||''; const priceField=row.querySelector('.item-price'); priceField.dataset.autofill='1';} updateTotals();});

detailGridBody.addEventListener('input', function(event){const row=event.target.closest('[data-row]'); if(!row){return;} if(event.target.classList.contains('item-price')){event.target.value=formatRibuan(event.target.value.replace(/\D/g,'')); event.target.dataset.autofill='0';} if(event.target.classList.contains('item-qty')){event.target.value=event.target.value.replace(/[^\d.,]/g,'');} updateTotals();});

detailGridBody.addEventListener('click', function(event){const removeButton=event.target.closest('.package-row-remove'); if(!removeButton){return;} const row=removeButton.closest('[data-row]'); if(!row){return;} const rows=getRows(); if(rows.length<=1){row.querySelector('.item-code').value=''; row.querySelector('.item-name').value=''; row.querySelector('.item-qty').value='1'; row.querySelector('.item-price').value=''; row.querySelector('.item-price').dataset.autofill='1'; updateTotals(); return;} row.remove(); while(getRows().length<3){createRow({qty:'1'});} updateTotals();});

transactionTableBody.addEventListener('click', function(event){if(event.target.closest('a')){return;} const row=event.target.closest('tr'); if(!row||!row.dataset.nofak){return;} const details=JSON.parse(row.dataset.details||'[]'); currentNofakField.value=row.dataset.nofak; displayNofakField.value=row.dataset.nofak; mejaField.value=row.dataset.meja||''; expiredField.value=row.dataset.expired||''; form.action='/menu-package-transaction/'+row.dataset.nofak+'/update'; saveButton.textContent='Update Package Transaction'; resetButton.textContent='Cancel Edit'; resetGrid(details); mejaField.focus();});

resetButton.addEventListener('click', function(){form.reset(); form.action='/menu-package-transaction'; currentNofakField.value=''; displayNofakField.value='Generated after save'; saveButton.textContent='Save Package Transaction'; resetButton.textContent='Reset Form'; expiredField.value='{{ now()->format('Y-m-d') }}'; resetGrid([{kode:'',qty:'1',price:''},{kode:'',qty:'1',price:''},{kode:'',qty:'1',price:''}]); mejaField.focus();});

form.addEventListener('submit', function(){mejaField.value=(mejaField.value||'').toString().trim().toUpperCase(); getRows().forEach((row)=>{const priceField=row.querySelector('.item-price'); if(priceField){priceField.value=unformat(priceField.value);} const qtyField=row.querySelector('.item-qty'); if(qtyField&&qtyField.value.trim()===''){qtyField.value='1';}});});

const successAlert=document.getElementById('successAlert');
if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);}

resetGrid();
mejaField.focus();
</script>

@endsection
