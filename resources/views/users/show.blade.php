@extends('admin.sidebar')

@section('title', 'Detail User')

@section('content')
    <div class="py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><i class="bi bi-person-badge"></i> {{ $user->full_name }}</h2>
                <p class="text-muted mb-0">Detail akun dan aktivitas pengguna</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('users.edit', $user->user_id) }}" class="btn btn-warning text-white">
                    <i class="bi bi-pencil-square"></i> Edit
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <strong>Informasi Akun</strong>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Username</dt>
                            <dd class="col-sm-8">{{ $user->username }}</dd>

                            <dt class="col-sm-4">Email</dt>
                            <dd class="col-sm-8">{{ $user->email }}</dd>

                            <dt class="col-sm-4">Role</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-primary text-capitalize">{{ str_replace('_', ' ', $user->role) }}</span>
                            </dd>

                            <dt class="col-sm-4">Terdaftar</dt>
                            <dd class="col-sm-8">
                                @if($user->created_at)
                                    {{ $user->created_at->format('d M Y') }}
                                @else
                                    Tidak diketahui
                                @endif
                            </dd>

                            <dt class="col-sm-4">User ID</dt>
                            <dd class="col-sm-8"><code>{{ $user->user_id }}</code></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong>Statistik</strong>
                        <span class="badge bg-secondary">{{ $user->projectMembers->count() }} project</span>
                    </div>
                    <div class="card-body">
                        <p class="mb-2 text-muted">Card aktif</p>
                        @if($user->assignments->count())
                            <ul class="list-group list-group-flush">
                                @foreach($user->assignments as $assignment)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $assignment->card->card_title ?? '-' }}</span>
                                        <span class="badge bg-info text-dark">{{ $assignment->card->status ?? '-' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Belum ada card aktif.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <strong>Keanggotaan Proyek</strong>
            </div>
            <div class="card-body">
                @if($user->projectMembers->count())
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Proyek</th>
                                    <th>Peran</th>
                                    <th>Ditambahkan Pada</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->projectMembers as $member)
                                    <tr>
                                        <td>{{ $member->project->project_name ?? '-' }}</td>
                                        <td class="text-capitalize">{{ str_replace('_', ' ', $member->role ?? '-') }}</td>
                                        <td>{{ $member->joined_at ? $member->joined_at->format('d M Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border mb-0">
                        <i class="bi bi-people"></i> Belum tergabung dalam proyek manapun.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
