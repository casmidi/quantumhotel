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

    while (count($initialRows) < 2) {
        $initialRows[] = [
            'kode' => '',
            'qty' => '1',
            'price' => '',
        ];
    }

    $sortBy = $sortBy ?? 'invoice';
    $sortDir = $sortDir ?? 'desc';
    $sortUrl = function (string $column) use ($sortBy, $sortDir) {
        $query = array_merge(request()->except('page'), [
            'sort_by' => $column,
            'sort_dir' => $sortBy === $column && $sortDir === 'asc' ? 'desc' : 'asc',
        ]);

        $queryString = http_build_query($query);

        return url()->current() . ($queryString !== '' ? '?' . $queryString : '');
    };
    $sortIcon = function (string $column) use ($sortBy, $sortDir) {
        if ($sortBy !== $column) {
            return 'fa-solid fa-sort';
        }

        return $sortDir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
    };
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
.package-shell.package-directory-loading { opacity:.72; pointer-events:none; transition:opacity .18s ease; }
.package-shell-header { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:1.35rem 1.5rem 1rem; border-bottom:1px solid rgba(16,35,59,.08); }
.package-shell-title { margin:0; font-size:1.1rem; font-weight:700; color:#10233b; }
.package-shell-subtitle { margin:.35rem 0 0; font-size:.9rem; color:#5f6f84; }
.package-shell-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem .8rem; border-radius:999px; background:rgba(179,138,81,.12); color:#8b6232; font-weight:700; font-size:.82rem; }
.package-shell-body { padding:1.5rem; }
.package-label { display:block; font-size:.84rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#5f6f84; margin-bottom:.55rem; }
.package-input, .package-select { height:calc(2.6rem + 2px); border-radius:14px; border:1px solid rgba(16,35,59,.12); box-shadow:inset 0 1px 2px rgba(16,35,59,.04); background:rgba(255,255,255,.92); color:#10233b; font-weight:600; }
.package-input:focus, .package-select:focus { border-color:rgba(179,138,81,.78); box-shadow:0 0 0 .2rem rgba(179,138,81,.14); }
.package-actions { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:.95rem; flex-wrap:wrap; }
.package-actions-main { display:flex; gap:.75rem; flex-wrap:wrap; }
.package-btn-primary { border:0; border-radius:999px; padding:.75rem 1.4rem; font-weight:700; background:linear-gradient(135deg,#173761 0%,#1e4b80 55%,#b38a51 150%); box-shadow:0 12px 26px rgba(23,55,97,.2); color:#fff; }
.package-btn-secondary { border-radius:999px; padding:.75rem 1.3rem; font-weight:700; border:1px solid rgba(16,35,59,.12); background:rgba(255,255,255,.78); color:#173761; }
.package-btn-add { border-radius:999px; padding:.48rem .9rem; font-size:.82rem; font-weight:700; line-height:1.1; border:1px dashed rgba(23,55,97,.22); background:rgba(23,55,97,.05); color:#173761; }
.package-btn-add:hover { background:rgba(23,55,97,.1); }
.package-alert, .package-error { border:0; border-radius:18px; padding:.95rem 1.15rem; box-shadow:0 14px 30px rgba(16,35,59,.08); }
.package-alert { background:linear-gradient(135deg, rgba(33,150,83,.16), rgba(33,150,83,.08)); color:#1c6b40; }
.package-error { background:linear-gradient(135deg, rgba(179,52,70,.16), rgba(179,52,70,.08)); color:#8f2435; }
.package-grid-wrap { position:relative; border:1px solid rgba(16,35,59,.08); border-radius:22px; background:rgba(255,255,255,.58); overflow:hidden; }
.package-grid-toolbar { position:relative; display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; min-height:92px; padding:1rem 1.15rem 2.35rem; padding-right:9.75rem; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.03), rgba(16,35,59,.01)); }
.package-grid-title { margin:0; font-size:.98rem; font-weight:700; color:#173761; }
.package-grid-note { margin:.3rem 0 0; font-size:.84rem; color:#6b7b90; }
.package-grid-total { position:absolute; top:1rem; text-align:right; pointer-events:none; opacity:0; visibility:hidden; }
.package-grid-total.is-ready { opacity:1; visibility:visible; }
.package-grid-add-row { position:absolute; top:.85rem; right:1.15rem; }
.package-grid-total-label { display:block; margin-bottom:.2rem; font-size:.74rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#8b9ab0; }
.package-grid-total-value { display:inline-flex; align-items:baseline; justify-content:flex-end; gap:.35rem; width:100%; white-space:nowrap; font-size:2.1rem; line-height:1; font-weight:800; color:#173761; letter-spacing:.03em; }
.package-grid-total-currency { font-size:1.15rem; line-height:1; }
.package-grid-table-wrap { overflow:auto; }
.package-grid-table { width:100%; min-width:980px; border-collapse:separate; border-spacing:0; }
.package-grid-table thead th { position:sticky; top:0; z-index:1; border:0; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.05), rgba(16,35,59,.02)); color:#53657d; text-transform:uppercase; letter-spacing:.08em; font-size:.74rem; font-weight:700; padding:.9rem .85rem; }
.package-grid-table thead th.text-right { text-align:right; }
.package-grid-table tbody td { border:0; border-bottom:1px solid rgba(16,35,59,.06); padding:.7rem .75rem; background:rgba(255,255,255,.72); vertical-align:middle; }
.package-grid-table tbody tr:nth-child(odd) td { background:rgba(16,35,59,.03); }
.package-grid-table tbody tr:hover td { background:rgba(179,138,81,.05); }
.package-row-index { width:72px; color:#6b7b90; font-weight:700; }
.package-grid-table .package-input,
.package-grid-table .package-select { height:calc(2.35rem + 2px); border-radius:12px; }
.package-grid-table .item-name { background:rgba(236,242,249,.9); }
.package-grid-table .item-price { background:rgba(236,242,249,.9); }
.package-grid-table .item-code.is-invalid { border-color:#c94c5e; box-shadow:0 0 0 .18rem rgba(201,76,94,.14); background:rgba(255,255,255,.92); }
.package-grid-table .line-total { font-weight:700; color:#173761; min-width:130px; display:inline-block; text-align:right; }
.package-row-remove { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; border:1px solid rgba(178,34,34,.12); background:rgba(178,34,34,.08); color:#aa2f2f; text-decoration:none; }
.package-row-remove:hover { background:#aa2f2f; color:#fff; text-decoration:none; }
.package-grid-hint { margin-top:.8rem; color:#6b7b90; font-size:.84rem; }
.package-search-form { display:flex; align-items:flex-end; gap:.75rem; flex-wrap:wrap; }
.package-search-group { min-width:180px; flex:1 1 180px; }
.package-search-actions { display:flex; gap:.65rem; flex-wrap:wrap; }
.package-table-wrap { border-radius:0 0 24px 24px; overflow:hidden; }
.package-table { margin-bottom:0; }
.package-table thead th { border-top:0; border-bottom:1px solid rgba(16,35,59,.08); background:linear-gradient(180deg, rgba(16,35,59,.02), rgba(16,35,59,.06)); color:#53657d; text-transform:uppercase; letter-spacing:.08em; font-size:.76rem; font-weight:700; padding:1rem 1.2rem; }
.package-sort-link { display:inline-flex; align-items:center; gap:.55rem; padding:.38rem .72rem; border-radius:999px; color:inherit; text-decoration:none; transition:all .18s ease; }
.package-sort-link:hover { color:#173761; text-decoration:none; background:rgba(23,55,97,.08); }
.package-sort-link.is-active { color:#fff; background:linear-gradient(135deg,#173761 0%,#1e4b80 55%,#b38a51 150%); box-shadow:0 10px 22px rgba(23,55,97,.18); }
.package-sort-link i { font-size:.82rem; opacity:.62; transition:transform .18s ease, opacity .18s ease; }
.package-sort-link.is-active i { opacity:1; transform:scale(1.14); }
.package-table tbody tr { transition:transform .18s ease, box-shadow .18s ease, background-color .18s ease; cursor:pointer; }
.package-table tbody tr:nth-child(odd) { background:rgba(16,35,59,.045); }
.package-table tbody tr:nth-child(even) { background:rgba(255,255,255,.96); }
.package-table tbody tr:hover { background:rgba(179,138,81,.06); transform:translateY(-1px); box-shadow:inset 4px 0 0 #b38a51; }
.package-table tbody tr.package-row-locked { cursor:default; }
.package-table tbody tr.package-row-locked:hover { background:inherit; transform:none; box-shadow:none; }
.package-table tbody td { border-top:1px solid rgba(16,35,59,.06); padding:1rem 1.2rem; vertical-align:middle; color:#10233b; }
.package-code { display:inline-flex; align-items:center; min-width:120px; justify-content:center; padding:.45rem .7rem; border-radius:999px; background:rgba(23,55,97,.08); color:#173761; font-weight:700; letter-spacing:.06em; }
.package-delete { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; background:rgba(178,34,34,.08); color:#aa2f2f; border:1px solid rgba(178,34,34,.12); text-decoration:none; font-size:1rem; transition:all .18s ease; }
.package-delete:hover { background:#aa2f2f; color:#fff; text-decoration:none; transform:translateY(-1px); }
.package-empty { text-align:center; padding:2.2rem 1rem; color:#6b7b90; }

.package-disabled-action { display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:50%; background:rgba(16,35,59,.08); color:#6b7b90; border:1px solid rgba(16,35,59,.1); }
.package-pagination-wrap { display:flex; justify-content:flex-end; padding:1rem 1.4rem 1.4rem; border-top:1px solid rgba(16,35,59,.08); background:rgba(255,255,255,.58); }
.package-pagination { display:flex; align-items:center; gap:.45rem; margin:0; padding:0; list-style:none; }
.package-page-item { display:flex; }
.package-page-link { display:inline-flex; align-items:center; justify-content:center; min-width:40px; height:40px; padding:0 .85rem; border-radius:12px; border:1px solid rgba(16,35,59,.12); background:rgba(255,255,255,.92); color:#173761; font-weight:700; text-decoration:none; transition:all .18s ease; }
.package-page-link:hover { background:rgba(23,55,97,.08); color:#173761; text-decoration:none; }
.package-page-item.active .package-page-link { background:linear-gradient(135deg,#173761 0%,#1e4b80 55%,#b38a51 150%); color:#fff; border-color:transparent; box-shadow:0 10px 22px rgba(23,55,97,.16); }
.package-page-item.disabled .package-page-link { opacity:.45; pointer-events:none; }
@media (max-width:767.98px){ .package-shell-header, .package-grid-toolbar { flex-direction:column; align-items:flex-start; } .package-grid-toolbar { min-height:0; padding-right:1.15rem; padding-bottom:1rem; } .package-grid-total { position:static; width:auto !important; pointer-events:auto; margin-top:.75rem; } .package-grid-add-row { position:static; margin-top:.55rem; } .package-grid-total-value { font-size:1.7rem; } .package-actions { align-items:flex-start; } }
</style>

<div class="container-fluid package-page">
    @if(session('success'))<div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error mb-4">{{ session('error') }}</div>@endif

    <section class="package-hero">
        <div class="package-kicker"><span>Quantum Hotel</span><span>Package Transactions</span></div>
        <h1>Package Transactions</h1>
        <p>Create package transactions manually by selecting items from Package Items. This flexible line-item grid behaves like a lightweight spreadsheet so users can keep adding rows as needed.</p>
    </section>

    <section class="package-shell">
        <div class="package-shell-header"><div><h2 class="package-shell-title">Manual Package Transaction Input</h2><p class="package-shell-subtitle">Build package transactions with a flexible grid instead of a fixed three-line form. The transaction number is generated automatically, and the final number is reserved safely on save.</p></div></div>
        <div class="package-shell-body">
            <form method="POST" action="/menu-package-transaction" id="formPackageTransaction" autocomplete="off">
                @csrf
                <input type="hidden" id="CurrentNofak" value="">
                <input type="hidden" name="GeneratedNofak" id="GeneratedNofak" value="{{ old('GeneratedNofak', $nextNofak) }}">
                <div class="form-row">
                    <div class="form-group col-md-4"><label class="package-label">Transaction Number</label><input type="text" id="DisplayNofak" class="form-control package-input" value="{{ old('GeneratedNofak', $nextNofak) }}" readonly></div>
                    <div class="form-group col-md-4"><label class="package-label" for="Meja">Package Code</label><input type="text" name="Meja" id="Meja" class="form-control package-input" value="{{ old('Meja') }}" required></div>
                    <div class="form-group col-md-4"><label class="package-label" for="ExpiredDisplay">Expired</label><input type="hidden" name="Expired" id="Expired" value="{{ old('Expired', now()->format('Y-m-d')) }}"><input type="text" id="ExpiredDisplay" class="form-control package-input" value="{{ \Carbon\Carbon::parse(old('Expired', now()->format('Y-m-d')))->format('d-m-Y') }}" placeholder="dd-MM-yyyy" inputmode="numeric" required></div>
                </div>

                <div class="package-grid-wrap">
                    <div class="package-grid-toolbar">
                        <div>
                            <h3 class="package-grid-title">Package Line Grid</h3>
                            <p class="package-grid-note">Default starts with 2 rows, and additional lines can be added from the Add Row button.</p>
                        </div>
                        <button type="button" class="btn package-btn-add package-grid-add-row" id="addRowButton"><i class="fa-solid fa-plus mr-1"></i>Add Row</button>
                        <div class="package-grid-total" id="totalNominalPanel">
                            <span class="package-grid-total-label">Total Amount</span>
                            <strong class="package-grid-total-value"><span class="package-grid-total-currency">Rp.</span><span id="TotalNominal">0</span></strong>
                        </div>
                    </div>
                    <div class="package-grid-table-wrap">
                        <table class="package-grid-table">
                            <thead>
                                <tr>
                                    <th width="70">Line</th>
                                    <th width="300">Item Code</th>
                                    <th width="260">Item Name</th>
                                    <th width="120" class="text-right">Qty</th>
                                    <th width="120" class="text-right">Price</th>
                                    <th width="150" class="text-right" data-amount-header>Amount</th>
                                    <th width="90" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="detailGridBody"></tbody>
                        </table>
                    </div>
                </div>

                <p class="package-grid-hint">Use Add Row whenever you need another line, and totals update automatically while you type.</p>

                <div class="package-actions">
                    <div class="package-actions-main"><button class="btn package-btn-primary" id="saveButton">Save Package Transaction</button><button type="button" class="btn package-btn-secondary" id="newTransactionButton">New Transaction</button><button type="button" class="btn package-btn-secondary" id="resetButton">Reset Form</button></div>
                </div>
            </form>
        </div>
    </section>

    @include('package.partials.transaction-directory-section', ['packages' => $packages, 'summary' => $summary, 'searchType' => $searchType, 'searchValue' => $searchValue, 'sortBy' => $sortBy, 'sortDir' => $sortDir])
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
        <td><input type="text" name="Price[]" class="form-control package-input text-right item-price" inputmode="numeric" readonly tabindex="-1"></td>
        <td class="text-right"><span class="line-total">Rp. 0</span></td>
        <td class="text-center"><button type="button" class="package-row-remove" title="Remove line" aria-label="Remove line"><i class="fa-solid fa-trash"></i></button></td>
    </tr>
</template>

<script>
function normalizeNumber(value){if(value===null||value===undefined){return '';}const raw=value.toString().trim();if(raw===''){return '';}if(raw.includes('.')){const [integerPart,decimalPart='']=raw.split('.');const cleanInteger=integerPart.replace(/\D/g,'');if(/^0+$/.test(decimalPart)){return cleanInteger;}}return raw.replace(/\D/g,'');}
function formatRibuan(value){const normalized=normalizeNumber(value);if(!normalized){return '';}return normalized.replace(/\B(?=(\d{3})+(?!\d))/g,'.');}
function unformat(value){return (value||'').toString().replace(/\./g,'');}
function formatDisplayDate(value){if(!value){return '';} const normalized=value.toString().trim().replace(/\//g,'-'); const parts=normalized.split('-'); if(parts.length===3 && parts[0].length===4){return [parts[2],parts[1],parts[0]].join('-');} if(parts.length===3 && parts[2].length===4){return [parts[0].padStart(2,'0'),parts[1].padStart(2,'0'),parts[2]].join('-');} return value;}
function normalizeDisplayDate(value){const normalized=(value||'').toString().trim().replace(/\//g,'-'); const parts=normalized.split('-'); if(parts.length!==3){return '';} const day=parts[0].padStart(2,'0'); const month=parts[1].padStart(2,'0'); const year=parts[2]; if(year.length!==4){return '';} const iso=year + '-' + month + '-' + day; const testDate=new Date(iso + 'T00:00:00'); if(Number.isNaN(testDate.getTime())){return '';} return testDate.getFullYear().toString()===year && (testDate.getMonth()+1).toString().padStart(2,'0')===month && testDate.getDate().toString().padStart(2,'0')===day ? iso : '';}
function qtyValue(value){const normalized=(value||'').toString().replace(',', '.').replace(/[^\d.]/g,'');const parsed=parseFloat(normalized);return parsed>0?parsed:0;}
function escapeHtml(value){return (value||'').toString().replace(/[&<>"']/g, function(char){return({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[char];});}

const form=document.getElementById('formPackageTransaction');
const mejaField=document.getElementById('Meja');
const expiredField=document.getElementById('Expired');
const expiredDisplayField=document.getElementById('ExpiredDisplay');
const totalNominalField=document.getElementById('TotalNominal');
  const currentNofakField=document.getElementById('CurrentNofak');
  const generatedNofakField=document.getElementById('GeneratedNofak');
  const displayNofakField=document.getElementById('DisplayNofak');
  const saveButton=document.getElementById('saveButton');
  const newTransactionButton=document.getElementById('newTransactionButton');
  const resetButton=document.getElementById('resetButton');
const addRowButton=document.getElementById('addRowButton');
const detailGridBody=document.getElementById('detailGridBody');
const rowTemplate=document.getElementById('detailRowTemplate');
  const packageDirectoryShell=document.getElementById('packageDirectoryShell');
const totalNominalPanel=document.getElementById('totalNominalPanel');
  const initialRows=@json($initialRows);
  const minimumRows=2;
  const defaultGeneratedNofak=@json(old('GeneratedNofak', $nextNofak));
  const resetAfterSuccess=@json(session()->has('success'));

function getRows(){return Array.from(detailGridBody.querySelectorAll('[data-row]'));}
function rowHasMeaningfulData(row){if(!row){return false;}const code=row.querySelector('.item-code')?.value?.trim()||'';const qty=row.querySelector('.item-qty')?.value?.trim()||'';const price=row.querySelector('.item-price')?.value?.trim()||'';return code!==''||qty!==''&&qty!=='1'||price!=='';}
function renameRows(){getRows().forEach((row,index)=>{row.querySelector('[data-line-number]').textContent=index+1;});}
function syncTotalAmountAlignment(){const amountHeader=document.querySelector('[data-amount-header]'); if(!amountHeader||!totalNominalPanel){return;} if(window.matchMedia('(max-width: 767.98px)').matches){totalNominalPanel.style.left=''; totalNominalPanel.style.width=''; totalNominalPanel.classList.add('is-ready'); return;} const toolbar=amountHeader.closest('.package-grid-wrap')?.querySelector('.package-grid-toolbar'); if(!toolbar){return;} const toolbarRect=toolbar.getBoundingClientRect(); const headerRect=amountHeader.getBoundingClientRect(); totalNominalPanel.style.left=(headerRect.left-toolbarRect.left)+'px'; totalNominalPanel.style.width=headerRect.width+'px'; totalNominalPanel.classList.add('is-ready');}
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
  function findDuplicateRow(sourceRow, code){if(!code){return null;} return getRows().find((row)=>row!==sourceRow && (row.querySelector('.item-code')?.value||'').trim()===code.trim())||null;}
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
    lineTotal.textContent='Rp. '+formatRibuan(Math.round(qty*price).toString()||'0');
    return qty*price;
  }
  function updateTotals(){let total=0; getRows().forEach((row)=>{total+=updateRow(row);}); totalNominalField.textContent=formatRibuan(Math.round(total).toString()); renameRows();}
  function resetGrid(details=[]){detailGridBody.innerHTML=''; const rows=(details&&details.length)?details:(initialRows&&initialRows.length?initialRows.slice(0, minimumRows):[{qty:'1'},{qty:'1'}]); rows.forEach((detail)=>createRow(detail)); while(getRows().length<minimumRows){createRow({qty:'1'});} updateTotals();}
  function setGeneratedNofak(value){const normalized=(value||'').toString().trim(); generatedNofakField.value=normalized; displayNofakField.value=normalized;}
  function activateCreateMode(){form.reset(); form.action='/menu-package-transaction'; currentNofakField.value=''; saveButton.textContent='Save Package Transaction'; resetButton.textContent='Reset Form'; expiredField.value='{{ now()->format('Y-m-d') }}'; expiredDisplayField.value=formatDisplayDate(expiredField.value); resetGrid([{kode:'',qty:'1',price:''},{kode:'',qty:'1',price:''}]); setGeneratedNofak(defaultGeneratedNofak); mejaField.focus();}
  function getEnterFlowFields(){return [mejaField, expiredDisplayField, ...getRows().flatMap((row)=>[row.querySelector('.item-code'), row.querySelector('.item-qty')]).filter((field)=>field && !field.disabled)];}
  function focusNextEnterField(currentField){const fields=getEnterFlowFields(); const index=fields.indexOf(currentField); if(index!==-1 && index < fields.length-1){const nextField=fields[index+1]; nextField.focus(); if(typeof nextField.select==='function' && nextField.tagName!=='SELECT'){nextField.select();} return true;} return false;}
  function submitFromEnter(){if(typeof form.requestSubmit==='function'){form.requestSubmit(saveButton);} else {saveButton.click();}}
  function syncPackageDirectory(url){if(!packageDirectoryShell||!url){return;} packageDirectoryShell.classList.add('package-directory-loading'); fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest'}}).then((response)=>response.text()).then((html)=>{const parser=new DOMParser(); const doc=parser.parseFromString(html,'text/html'); const nextShell=doc.getElementById('packageDirectoryShell'); if(!nextShell){window.location.href=url; return;} packageDirectoryShell.innerHTML=nextShell.innerHTML; window.history.replaceState({},'',url);}).catch(()=>{window.location.href=url;}).finally(()=>{packageDirectoryShell.classList.remove('package-directory-loading');});}

addRowButton.addEventListener('click', function(){const row=createRow({qty:'1'}); const select=row.querySelector('.item-code'); if(select){select.focus();}});

detailGridBody.addEventListener('change', function(event){const row=event.target.closest('[data-row]'); if(!row){return;} if(event.target.classList.contains('item-code')){const codeSelect=event.target; const selected=codeSelect.options[codeSelect.selectedIndex]; const selectedCode=(codeSelect.value||'').trim(); const duplicateRow=findDuplicateRow(row, selectedCode); if(duplicateRow){window.showCrudNotice('The same item code cannot be selected more than once in one package transaction.', 'Duplicate Item Code'); codeSelect.value=''; codeSelect.classList.remove('is-invalid'); row.querySelector('.item-name').value=''; const priceField=row.querySelector('.item-price'); priceField.value=''; priceField.dataset.autofill='1'; updateTotals(); codeSelect.focus(); return;} codeSelect.classList.remove('is-invalid'); row.querySelector('.item-name').value=selected?.dataset.name||''; const priceField=row.querySelector('.item-price'); priceField.dataset.autofill='1';} updateTotals();});

detailGridBody.addEventListener('input', function(event){const row=event.target.closest('[data-row]'); if(!row){return;} if(event.target.classList.contains('item-qty')){event.target.value=event.target.value.replace(/[^\d.,]/g,'');} updateTotals();});

  detailGridBody.addEventListener('click', function(event){const removeButton=event.target.closest('.package-row-remove'); if(!removeButton){return;} const row=removeButton.closest('[data-row]'); if(!row){return;} const rows=getRows(); if(rows.length<=1){row.querySelector('.item-code').value=''; row.querySelector('.item-name').value=''; row.querySelector('.item-qty').value='1'; row.querySelector('.item-price').value=''; row.querySelector('.item-price').dataset.autofill='1'; updateTotals(); return;} row.remove(); while(getRows().length<minimumRows){createRow({qty:'1'});} updateTotals();});

packageDirectoryShell.addEventListener('click', function(event){
    const pageLink = event.target.closest('.package-pagination .package-page-link');
    const sortLink = event.target.closest('.package-sort-link');
    const directoryLink = pageLink || sortLink;

    if(directoryLink){
        const href = directoryLink.getAttribute('href') || '';
        if(href !== '' && href !== '#'){
            event.preventDefault();
            syncPackageDirectory(href);
        }
        return;
    }

    if(event.target.closest('a')){return;}
    const row=event.target.closest('#tablePackageTransaction tbody tr');
    if(!row||!row.dataset.nofak){return;}
    if(row.dataset.used==='1'){return;}
    const details=JSON.parse(row.dataset.details||'[]');
    currentNofakField.value=row.dataset.nofak;
    setGeneratedNofak(row.dataset.nofak);
    mejaField.value=row.dataset.meja||'';
    expiredField.value=row.dataset.expired||'';
    expiredDisplayField.value=formatDisplayDate(row.dataset.expired||'');
    form.action='/menu-package-transaction/'+row.dataset.nofak+'/update';
    saveButton.textContent='Update Package Transaction';
    resetButton.textContent='Cancel Edit';
    resetGrid(details);
    mejaField.focus();
});

packageDirectoryShell.addEventListener('submit', function(event){
    const searchForm = event.target.closest('.package-search-form');
    if(!searchForm){return;}
    event.preventDefault();
    const query = new URLSearchParams(new FormData(searchForm)).toString();
    syncPackageDirectory(searchForm.action + (query ? '?' + query : ''));
});

  newTransactionButton.addEventListener('click', function(){activateCreateMode();});
  resetButton.addEventListener('click', function(){activateCreateMode();});

form.addEventListener('keydown', function(event){if(event.key!=='Enter'){return;} const target=event.target; const managedField=target===mejaField || target===expiredDisplayField || target.classList.contains('item-code') || target.classList.contains('item-qty'); if(!managedField){return;} event.preventDefault(); if(target===expiredDisplayField){const normalizedExpired=normalizeDisplayDate(expiredDisplayField.value); if(!normalizedExpired){window.showCrudNotice('Expired date must use format dd-MM-yyyy.', 'Invalid Date'); expiredDisplayField.focus(); return;} expiredField.value=normalizedExpired; expiredDisplayField.value=formatDisplayDate(normalizedExpired);} if(focusNextEnterField(target)){return;} submitFromEnter();});

form.addEventListener('submit', function(event){mejaField.value=(mejaField.value||'').toString().trim().toUpperCase(); const normalizedExpired=normalizeDisplayDate(expiredDisplayField.value); if(!normalizedExpired){event.preventDefault(); window.showCrudNotice('Expired date must use format dd-MM-yyyy.', 'Invalid Date'); expiredDisplayField.focus(); return;} expiredField.value=normalizedExpired; getRows().forEach((row)=>{const priceField=row.querySelector('.item-price'); if(priceField){priceField.value=unformat(priceField.value);} const qtyField=row.querySelector('.item-qty'); if(qtyField&&qtyField.value.trim()===''){qtyField.value='1';}});});

expiredDisplayField.addEventListener('blur', function(){const normalizedExpired=normalizeDisplayDate(this.value); if(normalizedExpired){expiredField.value=normalizedExpired; this.value=formatDisplayDate(normalizedExpired);}});

const successAlert=document.getElementById('successAlert');
if(successAlert){setTimeout(()=>{successAlert.style.transition='opacity .3s ease, transform .3s ease'; successAlert.style.opacity='0'; successAlert.style.transform='translateY(-8px)'; setTimeout(()=>successAlert.remove(),300);},3000);}

resetGrid();
setGeneratedNofak(defaultGeneratedNofak);
expiredDisplayField.value=formatDisplayDate(expiredField.value);
syncTotalAmountAlignment();
window.addEventListener('resize', syncTotalAmountAlignment);
if(resetAfterSuccess){activateCreateMode();} else {mejaField.focus();}
</script>

@endsection








