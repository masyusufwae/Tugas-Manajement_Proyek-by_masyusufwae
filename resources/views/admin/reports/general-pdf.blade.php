<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0d6efd;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            color: #666;
            margin: 4px 0;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .summary h3 {
            color: #0d6efd;
            margin-top: 0;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }
        .summary-item {
            background: #fff;
            padding: 14px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .summary-item .value {
            font-size: 22px;
            font-weight: bold;
            color: #0d6efd;
        }
        .summary-item .label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        h3.section-title {
            color: #0d6efd;
            margin-top: 35px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f1f3f5;
            font-weight: bold;
            color: #495057;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
        }
        .status-todo { background-color: #6c757d; }
        .status-in_progress { background-color: #ffc107; color: #212529; }
        .status-review { background-color: #17a2b8; }
        .status-done { background-color: #28a745; }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #868e96;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>General Performance Report</h1>
        <p><strong>Generated:</strong> {{ $generated_at->format('Y-m-d H:i:s') }}</p>
        @if($filters['start_date'] || $filters['end_date'])
            <p>
                <strong>Periode:</strong>
                {{ $filters['start_date'] ? \Carbon\Carbon::parse($filters['start_date'])->format('Y-m-d') : 'Semua waktu' }}
                &ndash;
                {{ $filters['end_date'] ? \Carbon\Carbon::parse($filters['end_date'])->format('Y-m-d') : 'Sekarang' }}
            </p>
        @endif
    </div>

    <div class="summary">
        <h3>Ringkasan Utama</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">{{ $summary['total_projects'] }}</div>
                <div class="label">Total Projects</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['total_cards'] }}</div>
                <div class="label">Total Cards</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['status_counts']['todo'] }}</div>
                <div class="label">Cards - To Do</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['status_counts']['in_progress'] }}</div>
                <div class="label">Cards - In Progress</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['status_counts']['review'] }}</div>
                <div class="label">Cards - Review</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['status_counts']['done'] }}</div>
                <div class="label">Cards - Done</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['completion_percentage'] }}%</div>
                <div class="label">Completion</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['total_subtasks'] }}</div>
                <div class="label">Total Subtasks</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['completed_subtasks'] }}</div>
                <div class="label">Completed Subtasks</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['total_estimated_hours'] }}</div>
                <div class="label">Estimated Hours</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['total_actual_hours'] }}</div>
                <div class="label">Actual Hours</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $summary['total_logged_hours'] }}</div>
                <div class="label">Logged Hours</div>
            </div>
        </div>
    </div>

    @if($project_breakdown->isNotEmpty())
        <h3 class="section-title">Ringkasan Per Project</h3>
        <table>
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Total Cards</th>
                    <th>In Progress</th>
                    <th>Review</th>
                    <th>Done</th>
                </tr>
            </thead>
            <tbody>
                @foreach($project_breakdown as $project)
                    <tr>
                        <td>{{ $project['name'] }}</td>
                        <td>{{ $project['total_cards'] }}</td>
                        <td>{{ $project['in_progress_cards'] }}</td>
                        <td>{{ $project['review_cards'] }}</td>
                        <td>{{ $project['completed_cards'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($team_data->isNotEmpty())
        <h3 class="section-title">Performa Tim</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Total Cards</th>
                    <th>Cards Selesai</th>
                    <th>Total Subtasks</th>
                    <th>Subtasks Selesai</th>
                    <th>Jam Kerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($team_data as $member)
                    <tr>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->username }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $member->role)) }}</td>
                        <td>{{ $member->total_cards }}</td>
                        <td>{{ $member->completed_cards }}</td>
                        <td>{{ $member->total_subtasks }}</td>
                        <td>{{ $member->completed_subtasks }}</td>
                        <td>{{ $member->total_hours_logged }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Report generated by Project Management System on {{ $generated_at->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
