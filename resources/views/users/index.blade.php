@extends('admin.sidebar')

@section('title', 'User Management')

@section('styles')
<style>
    .stats-card {
        transition: transform 0.2s ease;
    }
    .stats-card:hover {
        transform: translateY(-4px);
    }
    .search-box {
        max-width: 280px;
    }
</style>
@endsection

@section('content')
    <div class="py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="mb-1">Manajemen User</h2>
                <p class="text-muted mb-0">Kelola akun dan role pengguna aplikasi</p>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('users.index') }}" method="GET" class="d-flex search-box">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau username..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary ms-2" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Tambah User
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Total Users</p>
                        <h3 class="mb-0">{{ $users->total() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Role Terpopuler</p>
                        <h3 class="mb-0 text-capitalize">{{ $topRole ? str_replace('_', ' ', $topRole) : '-' }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card stats-card shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted small mb-1">User Aktif Terakhir</p>
                        <h3 class="fs-5 mb-0">{{ $latestUser?->full_name ?? '-' }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Daftar User</span>
                <span class="badge bg-secondary">{{ $users->total() }} pengguna</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $user->full_name }}</strong>
                                </td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-primary text-capitalize">{{ str_replace('_', ' ', $user->role) }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->user_id) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-person-x"></i> Tidak ada user yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="card-footer">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
