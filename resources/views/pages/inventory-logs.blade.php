<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inventory History Log</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('inventory.index') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-warehouse"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('index') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-users"></i><span>User Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('sales.report') }}"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Sales Report</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="{{ route('inventory.index') }}"><i class="fas fa-fw fa-warehouse"></i><span>Inventory Management</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">Inventory History Log</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-primary btn-sm">Stocks Summary</a>
                    <a href="{{ route('inventory.logs') }}" class="btn btn-primary btn-sm">History Log</a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Date Filter</h6></div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('inventory.logs') }}" class="form-row">
                            <div class="col-md-4 mb-2">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                            </div>
                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Apply</button>
                                <a href="{{ route('inventory.logs') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Inventory Logs</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="inventoryLogsTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Quantity</th>
                                    <th>Branch</th>
                                    <th>Added By</th>
                                    <th>Notes</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->id }}</td>
                                        <td>{{ $log->date ? \Illuminate\Support\Carbon::parse($log->date)->format('Y-m-d H:i') : '-' }}</td>
                                        <td>{{ $log->item_name ?? '-' }}</td>
                                        <td>{{ $log->type ?? '-' }}</td>
                                        <td>{{ $log->quantity ?? 0 }}</td>
                                        <td>{{ $log->branch_assigned ?? '-' }}</td>
                                        <td>{{ $log->added_by_name ?? '-' }}</td>
                                        <td>{{ $log->notes ?? '-' }}</td>
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
<script>$(function () { $('#inventoryLogsTable').DataTable({ order: [[0, 'desc']] }); });</script>
</body>
</html>
