@extends(Auth::user()->role . '.sidebar')

@section('title', 'help')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Request Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-question-circle"></i> Help Request Details</h2>
                        <a href="{{ route('help-requests.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Help Requests
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Request Information</h5>
                                    <span class="badge 
                                        @if($helpRequest->status === 'pending') bg-warning
                                        @elseif($helpRequest->status === 'responded') bg-info
                                        @elseif($helpRequest->status === 'rejected') bg-danger
                                        @else bg-success @endif">
                                        @if($helpRequest->status === 'pending') Menunggu
                                        @elseif($helpRequest->status === 'responded') Direspons
                                        @elseif($helpRequest->status === 'rejected') Ditolak
                                        @else Selesai
                                        @endif
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Subtask:</strong>
                                            <p class="mb-0">{{ $helpRequest->subtask->subtask_title }}</p>
                                            <small class="text-muted">{{ $helpRequest->subtask->card->card_title }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Project:</strong>
                                            <p class="mb-0">{{ $helpRequest->subtask->card->board->project->project_name ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>Requester:</strong>
                                            <p class="mb-0">{{ $helpRequest->requester->full_name }}</p>
                                            <small class="text-muted">{{ $helpRequest->requester->username }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Team Lead:</strong>
                                            <p class="mb-0">{{ $helpRequest->teamLead->full_name }}</p>
                                            <small class="text-muted">{{ $helpRequest->teamLead->username }}</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Message:</strong>
                                        <div class="border rounded p-3 bg-light">
                                            {{ $helpRequest->message }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <strong>Created:</strong>
                                        <span class="text-muted">{{ $helpRequest->created_at->format('M d, Y H:i:s') }}</span>
                                    </div>

                                    @if($helpRequest->response)
                                        <div class="mb-3">
                                            <strong>Response:</strong>
                                            <div class="border rounded p-3 bg-info bg-opacity-10">
                                                {{ $helpRequest->response }}
                                            </div>
                                            <small class="text-muted">Responded on {{ $helpRequest->updated_at->format('M d, Y H:i:s') }}</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    @if(Auth::user()->role === 'team_lead' && $helpRequest->status === 'pending')
                                        <form method="POST" action="{{ route('help-requests.respond', $helpRequest->help_request_id) }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="response" class="form-label">Response (Opsional)</label>
                                                <textarea class="form-control" id="response" name="response" rows="4" 
                                                          placeholder="Provide your response here..."></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="responded">Responded</option>
                                                    <option value="resolved">Resolved</option>
                                                    <option value="rejected">Rejected</option>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-send"></i> Send Response
                                            </button>
                                        </form>
                                    @elseif(Auth::user()->user_id === $helpRequest->requester_id && $helpRequest->status === 'responded')
                                        <form method="POST" action="{{ route('help-requests.mark-resolved', $helpRequest->help_request_id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success w-100">
                                                <i class="bi bi-check-circle"></i> Mark as Resolved
                                            </button>
                                        </form>
                                    @else
                                        <div class="text-center">
                                            <i class="bi bi-info-circle display-4 text-muted"></i>
                                            <p class="text-muted mt-2">
                                                @if($helpRequest->status === 'pending')
                                                    Menunggu respons dari team lead
                                                @elseif($helpRequest->status === 'responded')
                                                    Menunggu konfirmasi Anda
                                                @elseif($helpRequest->status === 'rejected')
                                                    Permintaan ini telah ditolak
                                                @else
                                                    Permintaan ini telah diselesaikan
                                                @endif
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Subtask Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <strong>Status:</strong>
                                        @if($helpRequest->subtask->status === 'todo')
                                            <span class="badge bg-secondary">To Do</span>
                                        @elseif($helpRequest->subtask->status === 'in_progress')
                                            <span class="badge bg-primary">In Progress</span>
                                        @elseif($helpRequest->subtask->status === 'review')
                                            <span class="badge bg-info">Review</span>
                                        @else
                                            <span class="badge bg-success">Done</span>
                                        @endif
                                    </div>

                                    @if($helpRequest->subtask->description)
                                        <div class="mb-2">
                                            <strong>Description:</strong>
                                            <p class="text-muted small">{{ $helpRequest->subtask->description }}</p>
                                        </div>
                                    @endif

                                    <div class="mb-2">
                                        <strong>Estimated Hours:</strong>
                                        <span class="text-muted">{{ $helpRequest->subtask->estimated_hours ?? 'Not set' }}</span>
                                    </div>

                                    <div class="mb-2">
                                        <strong>Actual Hours:</strong>
                                        <span class="text-muted">{{ $helpRequest->subtask->actual_hours ?? 'Not tracked' }}</span>
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