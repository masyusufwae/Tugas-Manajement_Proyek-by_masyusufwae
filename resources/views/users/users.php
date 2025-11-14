<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Users - Manajemen Proyek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            background-color: #343a40;
            color: white;
            width: 220px;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .sidebar .nav-link.logout {
            color: #dc3545;
        }

        .sidebar .nav-link.logout:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .main-content {
            flex: 1;
            background-color: #f8f9fa;
        }

        .topbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .table-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            overflow: hidden;
        }

        .badge-admin {
            background-color: #dc3545;
            color: white;
        }

        .badge-manager {
            background-color: #fd7e14;
            color: white;
        }

        .badge-user {
            background-color: #198754;
            color: white;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <h4 class="text-center">Project Management</h4>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('projects.create') }}" class="nav-link">
                    <i class="bi bi-folder-plus"></i> Projects
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link active">
                    <i class="bi bi-kanban"></i> Daftar Users
                </a>
            </li>

            @if(isset($project) && $project->boards->count())
            <li class="nav-item">
                <span class="text-white small fw-bold">Cards</span>
            </li>
            @foreach($project->boards as $board)
                <li class="nav-item">
                    <a href="{{ route('cards.index', $board->board_id) }}" class="nav-link ps-4">
                        <i class="bi bi-card-checklist"></i> {{ $board->board_name }}
                    </a>
                </li>
            @endforeach
            @endif

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-graph-up"></i> Monitoring
                </a>
            </li>

            <!-- Logout -->
            <div class="mt-auto">
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="nav-link logout btn btn-link w-100 text-start">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="topbar navbar navbar-light shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h4">Daftar Users</span>
            </div>
        </nav>

        <!-- Content -->
        <div class="p-4">
            <!-- Alert Success -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manajemen Users</h2>
                <a href="{{ route('users.create') }}" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Tambah User Baru
                </a>
            </div>

            <div class="table-container">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge badge-admin">Admin</span>
                                @elseif($user->role === 'manager')
                                    <span class="badge badge-manager">Manager</span>
                                @else
                                    <span class="badge badge-user">User</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-people display-4 d-block mb-2"></i>
                                Belum ada user yang terdaftar.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- User Statistics -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $users->where('role', 'admin')->count() }}</h4>
                                    <p class="mb-0">Admin</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person-gear display-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $users->where('role', 'manager')->count() }}</h4>
                                    <p class="mb-0">Manager</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person-badge display-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4>{{ $users->where('role', 'user')->count() }}</h4>
                                    <p class="mb-0">User</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-person display-6"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>