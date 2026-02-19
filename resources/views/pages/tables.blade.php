<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>User Management</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
@php
    $currentRole = strtolower((string) auth()->user()->role);
    $isViewOnly = $currentRole === 'owner';
    $canManageHighRoles = $currentRole === 'super admin';
    $shiftsById = collect($shifts ?? [])->keyBy('shift_id');
@endphp
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('users.index') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-users"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('index') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.show') }}"><i class="fas fa-fw fa-user"></i><span>My Profile</span></a>
        </li>
        <hr class="sidebar-divider">
        <li class="nav-item active">
            <a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-table"></i><span>User Management</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('sales.report') }}"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Sales Report</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('inventory.index') }}"><i class="fas fa-fw fa-warehouse"></i><span>Inventory Management</span></a>
        </li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">User Management</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                    </form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm">Users</a>
                    <a href="{{ route('users.branches.index') }}" class="btn btn-outline-primary btn-sm">Branch Management</a>
                    @if (in_array($currentRole, ['super admin', 'admin'], true))
                        <a href="{{ route('users.shifts.index') }}" class="btn btn-outline-primary btn-sm">Shift Management</a>
                    @endif
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
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Users Table</h6>
                        @if ($isViewOnly)
                            <span class="text-muted">Owner role is view-only.</span>
                        @else
                            <div>
                                <form action="{{ route('users.approve-all') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm mr-2" {{ $pendingUsersCount < 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-check-double mr-1"></i> Approve All Pending ({{ $pendingUsersCount }})
                                    </button>
                                </form>
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createUserModal">
                                    <i class="fas fa-plus mr-1"></i> Add User
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Shift</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->branch_assigned ?? '-' }}</td>
                                        <td>{{ $shiftsById[$user->shift_id]->name ?? '-' }}</td>
                                        <td>
                                            @php $role = strtolower((string) ($user->role ?? 'user')); @endphp
                                            <span class="badge {{ $role === 'super admin' ? 'badge-dark' : ($role === 'owner' ? 'badge-info' : ($role === 'admin' ? 'badge-primary' : 'badge-secondary')) }}">
                                                {{ ucwords($role) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $status = strtolower((string) ($user->status ?? 'pending')); @endphp
                                            <span class="badge {{ $status === 'approved' ? 'badge-success' : ($status === 'suspended' ? 'badge-danger' : 'badge-warning') }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
                                        <td class="text-nowrap">
                                            @if ($isViewOnly)
                                                <span class="text-muted">View only</span>
                                            @else
                                                @if (strtolower((string) ($user->status ?? 'pending')) === 'pending')
                                                    <form action="{{ route('users.approve', $user) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-sm" title="Approve User">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editUserModal{{ $user->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                </form>
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
<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add User</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Branch Assigned</label>
                        <select name="branch_assigned" class="form-control">
                            <option value="">No branch</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->name }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label>Shift Assigned</label>
                        <select name="shift_id" class="form-control">
                            <option value="">No shift</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->shift_id }}">
                                    {{ $shift->name }} ({{ substr((string) $shift->time_in, 0, 5) }} - {{ substr((string) $shift->time_out, 0, 5) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0 mt-3">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="user" selected>User</option>
                            <option value="admin">Admin</option>
                            @if ($canManageHighRoles)
                                <option value="owner">Owner</option>
                                <option value="super admin">Super Admin</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="pending" selected>Pending</option>
                            <option value="approved">Approved</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="form-group mb-0 mt-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
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

@foreach ($users as $user)
    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>
                        <div class="form-group">
                            <label>Branch Assigned</label>
                            <select name="branch_assigned" class="form-control">
                                <option value="">No branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->name }}" {{ (string) $user->branch_assigned === (string) $branch->name ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Shift Assigned</label>
                            <select name="shift_id" class="form-control">
                                <option value="">No shift</option>
                                @foreach ($shifts as $shift)
                                    <option value="{{ $shift->shift_id }}" {{ (int) $user->shift_id === (int) $shift->shift_id ? 'selected' : '' }}>
                                        {{ $shift->name }} ({{ substr((string) $shift->time_in, 0, 5) }} - {{ substr((string) $shift->time_out, 0, 5) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="user" {{ strtolower((string) $user->role) === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ strtolower((string) $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                @if ($canManageHighRoles)
                                    <option value="owner" {{ strtolower((string) $user->role) === 'owner' ? 'selected' : '' }}>Owner</option>
                                    <option value="super admin" {{ strtolower((string) $user->role) === 'super admin' ? 'selected' : '' }}>Super Admin</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            @php $editStatus = strtolower((string) ($user->status ?? 'pending')); @endphp
                            <select name="status" class="form-control" required>
                                <option value="pending" {{ $editStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $editStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="suspended" {{ $editStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label>New Password (optional)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Update</button>
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
<script>
    $(function () {
        $('#dataTable').DataTable();
    });
</script>
</body>
</html>
