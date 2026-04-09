@extends('layouts.app')

@section('title', '')

@section('content')

@include('partials.crud-package-theme')

<style>
.kind-pill {
    display: inline-flex;
    padding: 0.42rem 0.7rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
}

.kind-room {
    background: rgba(30, 140, 88, 0.12);
    color: #126b45;
}

.kind-restaurant {
    background: rgba(196, 134, 27, 0.14);
    color: #9b6616;
}

.package-code {
    display: inline-flex;
    align-items: center;
    min-width: 88px;
    justify-content: center;
    padding: 0.45rem 0.7rem;
    border-radius: 999px;
    background: rgba(23, 55, 97, 0.08);
    color: #173761;
    font-weight: 700;
    letter-spacing: 0.06em;
}

.package-delete {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(178, 34, 34, 0.08);
    color: #aa2f2f;
    border: 1px solid rgba(178, 34, 34, 0.12);
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.18s ease;
}

.package-delete:hover {
    background: #aa2f2f;
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
}

.package-empty {
    text-align: center;
    padding: 2.2rem 1rem;
    color: #6b7b90;
}
</style>

<div class="container-fluid package-page">
    @if(session('success'))<div class="alert package-alert mb-4" id="successAlert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert package-error mb-4">{{ session('error') }}</div>@endif

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
        @if($items->hasPages())
        <div class="package-pagination-wrap">
            {{ $items->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
        @endif
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



