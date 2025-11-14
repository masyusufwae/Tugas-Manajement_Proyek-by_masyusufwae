@extends(Auth::user()->role . '.sidebar')

@section('title', 'help')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Help</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-question-circle"></i> Request Help</h2>
                        <a href="{{ route('help-requests.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Help Requests
                        </a>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Help Request Details</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('help-requests.store', $subtask->subtask_id) }}">
                                        @csrf
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Subtask</label>
                                            <div class="form-control-plaintext">
                                                <strong>{{ $subtask->subtask_title }}</strong>
                                                <br><small class="text-muted">{{ $subtask->card->card_title }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Team Lead</label>
                                            <div class="form-control-plaintext">
                                                <strong>{{ $teamLead->full_name }}</strong>
                                                <br><small class="text-muted">{{ $teamLead->username }}</small>
                                            </div>
                                            <input type="hidden" name="team_lead_id" value="{{ $teamLead->user_id }}">
                                        </div>

                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> Anda akan mengirimkan permintaan bantuan untuk subtask ini ke Team Lead.
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-send"></i> Konfirmasi & Kirim Permintaan
                                            </button>
                                            <a href="{{ route('help-requests.index') }}" class="btn btn-secondary">Batal</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Subtask Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Status:</strong>
                                        @if($subtask->status === 'todo')
                                            <span class="badge bg-secondary">To Do</span>
                                        @elseif($subtask->status === 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @elseif($subtask->status === 'review')
                                            <span class="badge bg-info">Review</span>
                                        @else
                                            <span class="badge bg-success">Done</span>
                                        @endif
                                    </div>

                                    @if($subtask->description)
                                        <div class="mb-3">
                                            <strong>Description:</strong>
                                            <p class="text-muted">{{ $subtask->description }}</p>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <strong>Estimated Hours:</strong>
                                        <span class="text-muted">{{ $subtask->estimated_hours ?? 'Not set' }}</span>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Actual Hours:</strong>
                                        <span class="text-muted">{{ $subtask->actual_hours ?? 'Not tracked' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection