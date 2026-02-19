<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Shift Management</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
@php
    $currentRole = strtolower((string) auth()->user()->role);
    $isAdmin = $currentRole === 'admin';
@endphp
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('users.index') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-user-clock"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('index') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('profile.show') }}"><i class="fas fa-fw fa-user"></i><span>My Profile</span></a></li>
        <hr class="sidebar-divider">
        <li class="nav-item active"><a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-table"></i><span>User Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('sales.report') }}"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Sales Report</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('inventory.index') }}"><i class="fas fa-fw fa-warehouse"></i><span>Inventory Management</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">Shift Management</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">Users</a>
                    <a href="{{ route('users.branches.index') }}" class="btn btn-outline-primary btn-sm">Branch Management</a>
                    <a href="{{ route('users.shifts.index') }}" class="btn btn-primary btn-sm">Shift Management</a>
                </div>

                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Shifts</h6>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createShiftModal">
                            <i class="fas fa-plus mr-1"></i> Add Shift
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>Shift ID</th>
                                    <th>Shift Name</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Role</th>
                                    <th>Branch</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($shifts as $shift)
                                    <tr>
                                        <td>{{ $shift->shift_id }}</td>
                                        <td>{{ $shift->name }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', (string) $shift->time_in)->format('h:i A') }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::createFromFormat('H:i:s', (string) $shift->time_out)->format('h:i A') }}</td>
                                        <td>{{ ucwords((string) $shift->role_scope) }}</td>
                                        <td>{{ $shift->branch_assigned ?: '-' }}</td>
                                        <td><span class="badge {{ $shift->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $shift->is_active ? 'Yes' : 'No' }}</span></td>
                                        <td class="text-nowrap">
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editShiftModal{{ $shift->shift_id }}"><i class="fas fa-edit"></i></button>
                                            <form action="{{ route('users.shifts.destroy', $shift) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this shift?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
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

<div class="modal fade" id="createShiftModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <form action="{{ route('users.shifts.store') }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Shift</h5><button class="close" type="button" data-dismiss="modal"><span>x</span></button></div>
            <div class="modal-body">
                <div class="form-group"><label>Shift Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Time In</label><input type="time" name="time_in" class="form-control" required></div>
                <div class="form-group"><label>Time Out</label><input type="time" name="time_out" class="form-control" required></div>
                <div class="form-group">
                    <label>Role Scope</label>
                    <select name="role_scope" class="form-control" required>
                        <option value="user" selected>User</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                        <option value="super admin">Super Admin</option>
                    </select>
                </div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                @if ($isAdmin)
                    <div class="form-group mb-0">
                        <label>Branch Assigned</label>
                        <input type="text" class="form-control" value="{{ auth()->user()->branch_assigned ?: '-' }}" readonly>
                        <input type="hidden" name="branch_assigned" value="{{ auth()->user()->branch_assigned }}">
                    </div>
                @else
                    <div class="form-group">
                        <label>Branch Assigned</label>
                        <select name="branch_assigned" class="form-control">
                            <option value="">No branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="shiftCreateActive" name="is_active" value="1" checked><label class="custom-control-label" for="shiftCreateActive">Active</label></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
        </form>
    </div></div>
</div>

@foreach ($shifts as $shift)
    <div class="modal fade" id="editShiftModal{{ $shift->shift_id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <form action="{{ route('users.shifts.update', $shift) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Edit Shift</h5><button class="close" type="button" data-dismiss="modal"><span>x</span></button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Shift Name</label><input type="text" name="name" class="form-control" value="{{ $shift->name }}" required></div>
                    <div class="form-group"><label>Time In</label><input type="time" name="time_in" class="form-control" value="{{ substr((string) $shift->time_in, 0, 5) }}" required></div>
                    <div class="form-group"><label>Time Out</label><input type="time" name="time_out" class="form-control" value="{{ substr((string) $shift->time_out, 0, 5) }}" required></div>
                    <div class="form-group">
                        <label>Role Scope</label>
                        <select name="role_scope" class="form-control" required>
                            <option value="user" {{ (string) $shift->role_scope === 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ (string) $shift->role_scope === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="owner" {{ (string) $shift->role_scope === 'owner' ? 'selected' : '' }}>Owner</option>
                            <option value="super admin" {{ (string) $shift->role_scope === 'super admin' ? 'selected' : '' }}>Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2">{{ $shift->description }}</textarea></div>
                    @if ($isAdmin)
                        <div class="form-group mb-0">
                            <label>Branch Assigned</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->branch_assigned ?: '-' }}" readonly>
                            <input type="hidden" name="branch_assigned" value="{{ auth()->user()->branch_assigned }}">
                        </div>
                    @else
                        <div class="form-group">
                            <label>Branch Assigned</label>
                            <select name="branch_assigned" class="form-control">
                                <option value="">No branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->name }}" {{ (string) $shift->branch_assigned === (string) $branch->name ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="shiftEditActive{{ $shift->shift_id }}" name="is_active" value="1" {{ $shift->is_active ? 'checked' : '' }}><label class="custom-control-label" for="shiftEditActive{{ $shift->shift_id }}">Active</label></div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Update</button></div>
            </form>
        </div></div>
    </div>
@endforeach

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function () { $('#dataTable').DataTable(); });</script>
</body>
</html>
