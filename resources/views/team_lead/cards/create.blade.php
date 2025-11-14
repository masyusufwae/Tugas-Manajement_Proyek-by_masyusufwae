<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Buat Card Baru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body class="bg-light p-4">
  <div class="container">
    <h1>Buat Card Baru di Board: {{ $board->board_name }}</h1>

    <form method="POST" action="{{ route('team_lead.cards.store', $board->board_id) }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Judul Card</label>
        <input type="text" name="card_title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Prioritas</label>
        <select name="priority" class="form-control" required>
          <option value="low">Low</option>
          <option value="medium" selected>Medium</option>
          <option value="high">High</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Estimasi Jam</label>
        <input type="number" name="estimated_hours" class="form-control" step="0.5" min="0">
      </div>

      <div class="mb-3">
        <label class="form-label">Deadline</label>
        <input type="date" name="due_date" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Assign ke Developer/Designer</label>
        <select name="user_ids[]" id="userSelect" class="form-control" multiple="multiple" required>
          @foreach($availableUsers as $user)
            <option value="{{ $user->user_id }}">{{ $user->username }} ({{ $user->full_name }}) - {{ $user->role }}</option>
          @endforeach
        </select>
        <small class="text-muted">Pilih satu atau lebih developer/designer. Gunakan Ctrl+klik untuk memilih multiple.</small>
      </div>

      <button type="submit" class="btn btn-success">Simpan</button>
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#userSelect').select2({
        placeholder: "Pilih developer/designer",
        allowClear: true
      });
    });
  </script>
</body>
</html>