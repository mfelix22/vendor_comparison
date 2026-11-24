@extends('layouts.app')

@section('title', 'Database Backups')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="bi bi-database-down text-primary me-2"></i>Database Backups
            </h4>
            <div class="text-muted small">
                Kelola pencadangan database MySQL otomatis dan unduh file cadangan (.sql.gz) sewaktu-waktu.
            </div>
        </div>
        <div>
            <span class="badge bg-secondary rounded-pill px-3 py-2">
                <i class="bi bi-archive me-1"></i>{{ $backups->count() }} file backup
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Card 1: Manual Backup --}}
        <div class="col-lg-5">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-cloud-arrow-up-fill text-success me-2"></i>Buat Backup Manual (Instan)
                    </h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-between">
                    <p class="text-muted small mb-3">
                        Ekspor seluruh database MySQL saat ini ke dalam arsip terkompresi <code>.sql.gz</code>. Proses ini aman dan tidak mengunci tabel.
                    </p>

                    <form method="POST" action="{{ route('admin.backups.create') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label form-label-sm fw-semibold text-secondary">Catatan / Remark (Opsional)</label>
                            <input type="text" name="remark" class="form-control form-control-sm" placeholder="Contoh: Sebelum update sistem / Tutup bulan">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold py-2">
                            <i class="bi bi-play-circle-fill me-1"></i>Create Backup Now
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Card 2: Automatic Schedule --}}
        <div class="col-lg-7">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-clock-history text-primary me-2"></i>Jadwal Backup Otomatis (Scheduler)
                    </h6>
                    <span class="badge {{ $schedule->enabled ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-1">
                        {{ $schedule->enabled ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.backups.schedule') }}">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label form-label-sm fw-semibold text-secondary">Status Scheduler</label>
                                <select name="enabled" class="form-select form-select-sm">
                                    <option value="1" {{ $schedule->enabled ? 'selected' : '' }}>Yes, enable auto-backup</option>
                                    <option value="0" {{ !$schedule->enabled ? 'selected' : '' }}>No, disable auto-backup</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm fw-semibold text-secondary">Frekuensi</label>
                                <select name="frequency" class="form-select form-select-sm">
                                    <option value="daily" {{ $schedule->frequency === 'daily' ? 'selected' : '' }}>Daily (Harian)</option>
                                    <option value="weekly" {{ $schedule->frequency === 'weekly' ? 'selected' : '' }}>Weekly (Mingguan)</option>
                                    <option value="monthly" {{ $schedule->frequency === 'monthly' ? 'selected' : '' }}>Monthly (Bulanan)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-sm fw-semibold text-secondary">Waktu Eksekusi (Jam)</label>
                                <input type="time" name="time" value="{{ $schedule->time ?? '02:00' }}" class="form-control form-control-sm" required>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="prune_enabled" id="prune_enabled" value="1" {{ $schedule->prune_enabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small text-secondary" for="prune_enabled">
                                    Auto-delete backup lama (Retensi agar disk tidak penuh)
                                </label>
                            </div>

                            <div class="row g-2 align-items-center ps-4 text-muted small">
                                <div class="col-auto">
                                    Simpan <input type="number" name="keep_daily" value="{{ $schedule->keep_daily ?? 7 }}" min="1" max="365" class="form-control form-control-sm d-inline-block text-center" style="width: 65px;"> hari terakhir
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2 border-top">
                            <span class="text-muted small">
                                <i class="bi bi-info-circle me-1"></i>Dijalankan otomatis oleh scheduler di Portainer.
                            </span>
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i>Save Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: Backups Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-folder2-open text-warning me-2"></i>Riwayat &amp; File Backup
            </h6>
            @if ($schedule->prune_enabled && $backups->count() > 0)
                <form method="POST" action="{{ route('admin.backups.prune') }}" class="d-inline" onsubmit="return confirm('Jalankan pembersihan file backup lama sesuai aturan retensi?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Bersihkan file lama sekarang">
                        <i class="bi bi-trash3 me-1"></i>Prune Old Backups
                    </button>
                </form>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-3 py-3" style="width: 32%;">Nama File</th>
                            <th class="py-3" style="width: 12%;">Ukuran</th>
                            <th class="py-3" style="width: 18%;">Dibuat Oleh</th>
                            <th class="py-3" style="width: 18%;">Tanggal &amp; Waktu</th>
                            <th class="py-3">Catatan</th>
                            <th class="pe-3 py-3 text-end" style="width: 15%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($backups as $backup)
                            <tr>
                                <td class="ps-3 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark-zip-fill text-danger fs-5"></i>
                                        <div>
                                            <span class="fw-semibold font-monospace">{{ $backup->filename }}</span>
                                            <span class="badge bg-light text-dark border ms-1 font-monospace" style="font-size: 0.65rem;">GZIP</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-secondary">
                                    @if ($backup->size >= 1048576)
                                        <span class="fw-semibold text-dark">{{ number_format($backup->size / 1048576, 2) }}</span> MB
                                    @else
                                        <span class="fw-semibold text-dark">{{ number_format($backup->size / 1024, 1) }}</span> KB
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="badge {{ str_contains($backup->created_by, 'Scheduler') ? 'bg-info text-dark' : 'bg-primary' }} rounded-pill">
                                        {{ $backup->created_by }}
                                    </span>
                                </td>
                                <td class="py-3 text-secondary">
                                    {{ $backup->created_at ? $backup->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') : '-' }} WIB
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $backup->remark ?: '-' }}
                                </td>
                                <td class="pe-3 py-3 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.backups.download', $backup->filename) }}" class="btn btn-outline-success btn-sm" title="Download backup ke komputer">
                                            <i class="bi bi-download me-1"></i>Download
                                        </a>
                                        <form method="POST" action="{{ route('admin.backups.destroy', $backup->filename) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus file backup {{ $backup->filename }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus file ini">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada file cadangan (backup). Klik tombol <strong>"Create Backup Now"</strong> di atas untuk membuat cadangan pertama Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
