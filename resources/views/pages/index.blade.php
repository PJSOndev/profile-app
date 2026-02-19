<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
@php
    $currentRole = strtolower((string) auth()->user()->role);
@endphp
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
            <div class="sidebar-brand-icon rotate-n-15">
                <i class="fas fa-laugh-wink"></i>
            </div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>

        <hr class="sidebar-divider my-0">

        <li class="nav-item active">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.show') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>My Profile</span>
            </a>
        </li>

        @if (in_array($currentRole, ['super admin', 'owner', 'admin'], true))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.index') }}">
                    <i class="fas fa-fw fa-table"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('products.index') }}">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Product Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sales.report') }}">
                    <i class="fas fa-fw fa-file-invoice-dollar"></i>
                    <span>Sales Report</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('inventory.index') }}">
                    <i class="fas fa-fw fa-warehouse"></i>
                    <span>Inventory Management</span>
                </a>
            </li>
        @endif

        <hr class="sidebar-divider d-none d-md-block">

        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0" id="sidebarToggle"></button>
        </div>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h5 mb-0 text-gray-800">Dashboard</h1>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                    </form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">User Summary</h1>
                </div>

                <div class="row">
                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Registered Users</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $registeredUsers }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Users</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingUsers }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-user-clock fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved Users</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedUsers }}</div>
                                    </div>
                                    <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">User Status Chart</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-bar">
                                    <canvas id="userStatusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <a href="{{ route('profile.show') }}" class="btn btn-primary btn-block mb-2">My Profile</a>
                                @if (in_array($currentRole, ['super admin', 'owner', 'admin'], true))
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-block mb-2">Manage Users</a>
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-block mb-2">Manage Products</a>
                                    <a href="{{ route('sales.report') }}" class="btn btn-outline-primary btn-block mb-2">View Sales Report</a>
                                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary btn-block mb-2">Manage Inventory</a>
                                    <a href="{{ route('tables') }}" class="btn btn-outline-secondary btn-block">Open Users Table</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="sticky-footer bg-white">
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Profile App {{ date('Y') }}</span>
                </div>
            </div>
        </footer>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script>
    const ctx = document.getElementById('userStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Registered', 'Pending', 'Approved'],
            datasets: [{
                label: 'Users',
                data: [{{ $registeredUsers }}, {{ $pendingUsers }}, {{ $approvedUsers }}],
                backgroundColor: ['#4e73df', '#f6c23e', '#1cc88a'],
                borderColor: ['#4e73df', '#f6c23e', '#1cc88a'],
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        precision: 0
                    }
                }]
            },
            legend: {
                display: false
            }
        }
    });
</script>
</body>
</html>
