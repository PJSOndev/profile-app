<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Product History Log</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('products.index') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-history"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('index') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.categories.index') }}"><i class="fas fa-fw fa-tags"></i><span>Category Management</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="{{ route('products.logs.index') }}"><i class="fas fa-fw fa-history"></i><span>History Log</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">Product History Log</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Products</a>
                    <a href="{{ route('products.categories.index') }}" class="btn btn-outline-primary btn-sm">Category Management</a>
                    <a href="{{ route('products.logs.index') }}" class="btn btn-primary btn-sm">History Log</a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Action Logs</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Action</th>
                                    <th>Performed By</th>
                                    <th>Performed At</th>
                                    <th>Details</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>{{ $log->product?->name ?? ('#' . ($log->product_id ?? '-')) }}</td>
                                        <td><span class="badge badge-info">{{ ucfirst($log->action) }}</span></td>
                                        <td>{{ $log->actor?->name ?? 'System' }}</td>
                                        <td>{{ optional($log->performed_at)->format('Y-m-d H:i:s') }}</td>
                                        <td><small>{{ json_encode($log->details, JSON_UNESCAPED_UNICODE) }}</small></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function () { $('#dataTable').DataTable({ order: [[0, 'desc']] }); });</script>
</body>
</html>
