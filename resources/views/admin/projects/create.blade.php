@extends('admin.sidebar')

@section('title', 'Admin Dashboard')

@section('content')
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Buat Proyek Baru</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow-lg rounded-4 w-100" style="max-width: 700px;">
    <div class="card-body p-4">
      <h1 class="h4 mb-4 text-center fw-bold text-primary">Buat Proyek Baru</h1>

      <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        <div class="mb-3">
          <label for="project_name" class="form-label">Nama Proyek</label>
          <input type="text" class="form-control rounded-3" id="project_name" name="project_name" placeholder="Masukkan nama proyek">
        </div>

        <div class="mb-3">
          <label for="description" class="form-label">Deskripsi</label>
          <textarea class="form-control rounded-3" id="description" name="description" rows="3" placeholder="Tulis deskripsi proyek..."></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Anggota Tim</label>
          
          <div class="mb-3">
            <label for="team_lead_id" class="form-label">Team Lead <span class="text-danger">*</span></label>
            <select class="form-select rounded-3" id="team_lead_id" name="team_lead_id" required>
              <option value="">-- Pilih Team Lead --</option>
              @foreach($teamLeads as $teamLead)
                <option value="{{ $teamLead->user_id }}">{{ $teamLead->full_name ?? $teamLead->username }} ({{ $teamLead->username }})</option>
              @endforeach
            </select>
            <small class="text-muted">Team Lead wajib dipilih</small>
          </div>

          <div class="mb-3">
            <label for="developer_ids" class="form-label">Developer</label>
            <select class="form-select rounded-3" id="developer_ids" name="developer_ids[]" multiple>
              @foreach($developers as $developer)
                <option value="{{ $developer->user_id }}">{{ $developer->full_name ?? $developer->username }} ({{ $developer->username }})</option>
              @endforeach
            </select>
            <small class="text-muted">Tekan Ctrl (atau Cmd di Mac) untuk memilih multiple</small>
          </div>

          <div class="mb-3">
            <label for="designer_ids" class="form-label">Designer</label>
            <select class="form-select rounded-3" id="designer_ids" name="designer_ids[]" multiple>
              @foreach($designers as $designer)
                <option value="{{ $designer->user_id }}">{{ $designer->full_name ?? $designer->username }} ({{ $designer->username }})</option>
              @endforeach
            </select>
            <small class="text-muted">Tekan Ctrl (atau Cmd di Mac) untuk memilih multiple</small>
          </div>
        </div>

        <div class="mb-3">
          <label for="deadline" class="form-label">Deadline</label>
          <input type="date" class="form-control rounded-3" id="deadline" name="deadline">
        </div>

        <button type="submit" class="btn btn-primary w-100 rounded-3">Simpan</button>
      </form>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection