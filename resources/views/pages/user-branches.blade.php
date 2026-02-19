<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Branch Management</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
@php
    $currentRole = strtolower((string) auth()->user()->role);
    $isOwner = $currentRole === 'owner';
@endphp
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('users.index') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-code-branch"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('index') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('profile.show') }}"><i class="fas fa-fw fa-user"></i><span>My Profile</span></a></li>
        <hr class="sidebar-divider">
        <li class="nav-item active"><a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-fw fa-table"></i><span>User Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">Branch Management</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary btn-sm">Users</a>
                    <a href="{{ route('users.branches.index') }}" class="btn btn-primary btn-sm">Branch Management</a>
                    @if (in_array($currentRole, ['super admin', 'admin'], true))
                        <a href="{{ route('users.shifts.index') }}" class="btn btn-outline-primary btn-sm">Shift Management</a>
                    @endif
                </div>

                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Branches</h6>
                        @if ($isOwner)
                            <span class="text-muted">Owner role is view-only.</span>
                        @else
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createBranchModal"><i class="fas fa-plus mr-1"></i> Add Branch</button>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td>{{ $branch->id }}</td>
                                        <td>{{ $branch->name }}</td>
                                        <td>{{ $branch->description ?: '-' }}</td>
                                        <td><span class="badge {{ $branch->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $branch->is_active ? 'Yes' : 'No' }}</span></td>
                                        <td class="text-nowrap">
                                            @if ($isOwner)
                                                <span class="text-muted">View only</span>
                                            @else
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editBranchModal{{ $branch->id }}"><i class="fas fa-edit"></i></button>
                                                <form action="{{ route('users.branches.destroy', $branch) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this branch?');">
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

@if (! $isOwner)
<div class="modal fade" id="createBranchModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <form action="{{ route('users.branches.store') }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Branch</h5><button class="close" type="button" data-dismiss="modal"><span>x</span></button></div>
            <div class="modal-body">
                <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="createBranchActive" name="is_active" value="1" checked><label class="custom-control-label" for="createBranchActive">Active</label></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
        </form>
    </div></div>
</div>
@endif

@if (! $isOwner)
@foreach ($branches as $branch)
    <div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <form action="{{ route('users.branches.update', $branch) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Edit Branch</h5><button class="close" type="button" data-dismiss="modal"><span>x</span></button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ $branch->name }}" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ $branch->description }}</textarea></div>
                    <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="editBranchActive{{ $branch->id }}" name="is_active" value="1" {{ $branch->is_active ? 'checked' : '' }}><label class="custom-control-label" for="editBranchActive{{ $branch->id }}">Active</label></div>
                </div>
                <div class="modal-footer"><button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Update</button></div>
            </form>
        </div></div>
    </div>
@endforeach
@endif

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script>$(function () { $('#dataTable').DataTable(); });</script>
</body>
</html>
