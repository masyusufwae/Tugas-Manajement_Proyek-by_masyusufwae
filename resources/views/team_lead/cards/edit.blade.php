<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Card</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
  <div class="container">
    <h1>Edit Card di Board: {{ $board->board_name }}</h1>

    <form method="POST" action="{{ route('team_lead.cards.update', [$board->board_id, $card->card_id]) }}">
      @csrf
      @method('PUT')

      <div class="mb-3">
        <label class="form-label">Judul Card</label>
        <input type="text" name="card_title" class="form-control" value="{{ $card->card_title }}" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control">{{ $card->description }}</textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Prioritas</label>
        <select name="priority" class="form-control" required>
          <option value="low" @if($card->priority=='low') selected @endif>Low</option>
          <option value="medium" @if($card->priority=='medium') selected @endif>Medium</option>
          <option value="high" @if($card->priority=='high') selected @endif>High</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Estimasi Jam</label>
        <input type="number" name="estimated_hours" class="form-control" value="{{ $card->estimated_hours }}">
      </div>

      <div class="mb-3">
        <label class="form-label">Deadline</label>
        <input type="date" name="due_date" class="form-control" value="{{ $card->due_date }}">
      </div>

      <div class="mb-3">
        <label class="form-label">Assign ke Username (pisahkan dengan koma)</label>
        <input type="text" name="usernames" class="form-control" 
               value="{{ $card->assignments->pluck('user.username')->implode(', ') }}">
      </div>

      <button type="submit" class="btn btn-primary">Update</button>
    </form>
  </div>
</body>
</html>
