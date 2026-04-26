<section class="kelas-shell" id="kelasDirectoryShell">
    <div class="kelas-table-wrap">
        <div class="table-responsive">
            <table class="table kelas-table" id="tableKelas">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Room Facilities</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Deposit</th>
                        <th class="text-center" width="90">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $k)
                        <tr data-id="{{ $k->id }}" data-kode="{{ $k->Kode }}"
                            data-nama="{{ $k->Nama }}" data-rate="{{ $k->Rate1 }}"
                            data-depo="{{ $k->Depo1 }}">
                            <td><span class="kelas-code">{{ $k->Kode }}</span></td>
                            <td class="kelas-name">{{ $k->Nama }}</td>
                            <td class="text-right kelas-money">{{ number_format($k->Rate1 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-right kelas-money">{{ number_format($k->Depo1 ?? 0, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="/kelas/{{ $k->Kode }}/delete" class="kelas-delete" title="Delete"
                                    aria-label="Delete">&#128465;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="kelas-empty">No room type records yet. Create the first one
                                to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($kelas->hasPages())
        <div class="kelas-pagination-wrap">
            {{ $kelas->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif
</section>
