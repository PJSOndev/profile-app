<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Sales Report</title>

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
                <span class="h5 mb-0 text-gray-800">Sales Report</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('sales.report') }}" class="btn btn-primary btn-sm">Overview</a>
                    <a href="{{ route('sales.report.by-user') }}" class="btn btn-outline-primary btn-sm">Sales by User</a>
                    <a href="{{ route('sales.report.by-shift') }}" class="btn btn-outline-primary btn-sm">Sales per Shift</a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Filters</h6></div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('sales.report') }}" class="form-row">
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
                                <a href="{{ route('sales.report') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Sales</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary->gross_sales, 2) }}</div></div></div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Transactions</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary->total_transactions }}</div></div></div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Items Sold</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $summary->total_items_sold }}</div></div></div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2"><div class="card-body"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Discounts</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($summary->total_discounts, 2) }}</div></div></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Transactions</h6></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="transactionsTable" width="100%" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                            <th>Product Name</th>
                                            <th>User ID</th>
                                            <th>Payment</th>
                                            <th>Discount</th>
                                            <th>Total</th>
                                            <th>Qty</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->id }}</td>
                                                <td>{{ $transaction->date ? \Illuminate\Support\Carbon::parse($transaction->date)->format('Y-m-d H:i') : '-' }}</td>
                                                <td>{{ $transaction->product_names ?: '-' }}</td>
                                                <td>{{ $transaction->user_id }}</td>
                                                <td>{{ $transaction->payment_method ?: '-' }}</td>
                                                <td>{{ number_format((float) $transaction->discount, 2) }}</td>
                                                <td>{{ number_format((float) $transaction->total, 2) }}</td>
                                                <td>{{ $transaction->total_items }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Top Products</h6></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Amount</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($topProducts as $product)
                                            <tr>
                                                <td>{{ $product->product_name ?: '-' }}</td>
                                                <td>{{ $product->total_qty }}</td>
                                                <td>{{ number_format((float) $product->total_amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center">No data</td></tr>
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
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>
    $(function () {
        $('#transactionsTable').DataTable({ order: [[1, 'desc']] });
    });
</script>
</body>
</html>
