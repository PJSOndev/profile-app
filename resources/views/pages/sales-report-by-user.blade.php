<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sales Report - Sales by User</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-chart-line"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-users"></i><span>User Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="{{ route('sales.report') }}"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Sales Report</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('inventory.index') }}"><i class="fas fa-fw fa-warehouse"></i><span>Inventory Management</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">Sales Report - Sales by User</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('sales.report') }}" class="btn btn-outline-primary btn-sm">Overview</a>
                    <a href="{{ route('sales.report.by-user') }}" class="btn btn-primary btn-sm">Sales by User</a>
                    <a href="{{ route('sales.report.by-shift') }}" class="btn btn-outline-primary btn-sm">Sales per Shift</a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Filters</h6></div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('sales.report.by-user') }}" class="form-row">
                            <div class="col-md-3 mb-2">
                                <label>Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="">All</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method }}" {{ $filters['payment_method'] === $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Apply</button>
                                <a href="{{ route('sales.report.by-user') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Sales by User</h6></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="salesByUserTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>User Name</th>
                                    <th>Transactions</th>
                                    <th>Qty</th>
                                    <th>Discounts</th>
                                    <th>Total Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($salesByUser as $row)
                                    <tr>
                                        <td>{{ $row->user_id }}</td>
                                        <td>{{ $row->user_name }}</td>
                                        <td>{{ $row->transaction_count }}</td>
                                        <td>{{ $row->total_qty }}</td>
                                        <td>{{ number_format((float) $row->total_discounts, 2) }}</td>
                                        <td>{{ number_format((float) $row->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center">No data</td></tr>
                                @endforelse
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
<script>$(function () { $('#salesByUserTable').DataTable({ order: [[5, 'desc']] }); });</script>
</body>
</html>
