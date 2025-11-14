@extends(Auth::user()->role . '.sidebar')

@section('title', 'help')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="bi bi-tools"></i> Solver</h2>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card">
                        <div class="card-body">
                            @if($helpRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Subtask</th>
                                                <th>Project</th>
                                                @if(Auth::user()->role === 'team_lead')
                                                    <th>Requester</th>
                                                @else
                                                    <th>Team Lead</th>
                                                @endif
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($helpRequests as $request)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $request->subtask->subtask_title }}</strong>
                                                        <br><small class="text-muted">{{ $request->subtask->card->card_title }}</small>
                                                    </td>
                                                    <td>{{ $request->subtask->card->board->project->project_name ?? 'N/A' }}</td>
                                                    @if(Auth::user()->role === 'team_lead')
                                                        <td>{{ $request->requester->full_name }}</td>
                                                    @else
                                                        <td>{{ $request->teamLead->full_name }}</td>
                                                    @endif
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;">
                                                            {{ $request->message }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($request->status === 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($request->status === 'responded')
                                                            <span class="badge bg-info">Responded</span>
                                                        @else
                                                            <span class="badge bg-success">Resolved</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $request->created_at->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('help-requests.show', $request->help_request_id) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <h4 class="text-muted mt-3">No Help Requests</h4>
                                    <p class="text-muted">You don't have any help requests yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
@endsection