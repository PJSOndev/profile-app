<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inventory Management</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
@php
    $isViewOnly = strtolower((string) auth()->user()->role) === 'owner';
@endphp
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
                <span class="h5 mb-0 text-gray-800">Inventory Management</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('inventory.index') }}" class="btn btn-primary btn-sm">Stocks Summary</a>
                    <a href="{{ route('inventory.logs') }}" class="btn btn-outline-primary btn-sm">History Log</a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Date Filter</h6></div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('inventory.index') }}" class="form-row">
                            <div class="col-md-4 mb-2">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
                            </div>
                            <div class="col-md-4 mb-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mr-2">Apply</button>
                                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Stocks Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="inventoryTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Beginning</th>
                                    <th>In</th>
                                    <th>Out</th>
                                    <th>Ending</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($rows as $row)
                                    <tr>
                                        <td>{{ $row->item_name }}</td>
                                        <td>{{ $row->beginning }}</td>
                                        <td>{{ $row->in }}</td>
                                        <td>{{ $row->out }}</td>
                                        <td>{{ $row->ending }}</td>
                                        <td>
                                            @if ($isViewOnly)
                                                <span class="text-muted">View only</span>
                                            @else
                                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addQtyModal{{ $row->product_id }}">
                                                    <i class="fas fa-plus mr-1"></i> Add Qty
                                                </button>
                                            @endif
                                        </td>
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

@if (! $isViewOnly)
@foreach ($rows as $row)
    <div class="modal fade" id="addQtyModal{{ $row->product_id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('inventory.store-in') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $row->product_id }}">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Quantity - {{ $row->item_name }}</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label>Quantity to Add (IN)</label>
                            <input type="number" name="quantity" min="1" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function () { $('#inventoryTable').DataTable(); });</script>
</body>
</html>
