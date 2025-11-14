@extends('developer.sidebar')

@section('title', 'Tambah Subtask')

@section('content')
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-node-plus"></i> Tambah Subtask</h2>
                <p class="text-muted mb-0">Card: {{ $card->card_title }}</p>
            </div>
            <a href="{{ route('developer.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">Form Subtask</div>
            <div class="card-body">
                <form action="{{ route('subtasks.store', $card->card_id) }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-12">
                        <label for="subtask_title" class="form-label">Judul Subtask</label>
                        <input type="text" name="subtask_title" id="subtask_title" class="form-control @error('subtask_title') is-invalid @enderror" value="{{ old('subtask_title') }}" required>
                        @error('subtask_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Detail pekerjaan yang perlu dilakukan">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="estimated_hours" class="form-label">Estimasi Jam</label>
                        <input type="number" step="0.1" min="0" name="estimated_hours" id="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" value="{{ old('estimated_hours') }}">
                        @error('estimated_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('developer.dashboard') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
