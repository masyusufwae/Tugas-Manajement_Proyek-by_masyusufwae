<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Team Lead</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #533710;
        }
        .sidebar .nav-link {
            color: white;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s;
        }
        @media (min-width: 768px) {
            .main-content {
                margin-left: 250px;
            }
        }
        .btn-primary { background-color: #6E4B1F; border-color: #6E4B1F; }
        .btn-primary:hover { background-color: #98651E; border-color: #98651E; }
        .btn-success { background-color: #F0D394; border-color: #F0D394; color: #533710; }
        .btn-success:hover { background-color: #FCC0C0; border-color: #FCC0C0; color: #533710; }
        .btn-warning { background-color: #98651E; border-color: #98651E; color: white; }
        .btn-warning:hover { background-color: #6E4B1F; border-color: #6E4B1F; }
        .btn-info { background-color: #F0D394; border-color: #F0D394; color: #533710; }
        .btn-info:hover { background-color: #FCC0C0; border-color: #FCC0C0; color: #533710; }
        .table { background-color: #F0D394; }
        .table thead th { background-color: #6E4B1F; color: white; }
        .card { background-color: #FCC0C0; }
        .card-header { background-color: #98651E; color: white; }
    </style>
    @yield('styles')
</head>
<body>
    <header class="navbar navbar-dark bg-dark d-md-none">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <span class="navbar-brand ms-2">Menu</span>
        </div>
    </header>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="bi bi-people-fill"></i> Team Lead
                        </h4>
                        <small class="text-white-50">Welcome, {{ Auth::user()->full_name }}</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('team_lead.dashboard') ? 'active' : '' }}" 
                               href="{{ route('team_lead.dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('help-requests*') ? 'active' : '' }}" 
                               href="{{ route('help-requests.index') }}">
                                <i class="bi bi-tools"></i> Solver
                            </a>
                        </li>
                        
                        <hr class="text-white-50">
                        
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-start w-100 text-danger">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
