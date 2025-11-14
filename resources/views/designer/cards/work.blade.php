<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kerjakan Card</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">

  <div class="container">
    <h1>{{ $card->card_title }}</h1>
    <p>{{ $card->description }}</p>
    <p><strong>Status:</strong> {{ ucfirst($card->status) }}</p>

    @if($card->status === 'todo')
      <form method="POST" action="{{ route('cards.start', $card->card_id) }}">
        @csrf
        <button type="submit" class="btn btn-primary">🚀 Mulai Kerja</button>
      </form>
    @elseif($card->status === 'in_progress')
      <form method="POST" action="{{ route('cards.complete', $card->card_id) }}">
        @csrf
        <button type="submit" class="btn btn-success">✅ Selesaikan</button>
      </form>
    @else
      <span class="badge bg-success">Sudah Selesai</span>
    @endif
  </div>

</body>
</html>
