<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Category Management</title>

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
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('products.index') }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-boxes"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>
        <hr class="sidebar-divider my-0">
        <li class="nav-item"><a class="nav-link" href="{{ route('index') }}"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}"><i class="fas fa-fw fa-box"></i><span>Product Management</span></a></li>
        <li class="nav-item active"><a class="nav-link" href="{{ route('products.categories.index') }}"><i class="fas fa-fw fa-tags"></i><span>Category Management</span></a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('products.logs.index') }}"><i class="fas fa-fw fa-history"></i><span>History Log</span></a></li>
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <span class="h5 mb-0 text-gray-800">Category Management</span>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger btn-sm">Logout</button></form>
                </div>
            </nav>

            <div class="container-fluid">
                <div class="mb-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">Products</a>
                    <a href="{{ route('products.categories.index') }}" class="btn btn-primary btn-sm">Category Management</a>
                    <a href="{{ route('products.logs.index') }}" class="btn btn-outline-primary btn-sm">History Log</a>
                </div>

                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if ($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
                        @if ($isViewOnly)
                            <span class="text-muted">Owner role is view-only.</span>
                        @else
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createCategoryModal">
                                <i class="fas fa-plus mr-1"></i> Add Category
                            </button>
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
                                    <th>Products</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td>{{ $category->id }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description ?: '-' }}</td>
                                        <td><span class="badge {{ $category->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $category->is_active ? 'Yes' : 'No' }}</span></td>
                                        <td>{{ $category->products_count }}</td>
                                        <td class="text-nowrap">
                                            @if ($isViewOnly)
                                                <span class="text-muted">View only</span>
                                            @else
                                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editCategoryModal{{ $category->id }}"><i class="fas fa-edit"></i></button>
                                                <form action="{{ route('products.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
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
<div class="modal fade" id="createCategoryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <form action="{{ route('products.categories.store') }}" method="POST">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add Category</h5><button class="close" type="button" data-dismiss="modal"><span>x</span></button></div>
            <div class="modal-body">
                <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="categoryCreateActive" name="is_active" value="1" checked><label class="custom-control-label" for="categoryCreateActive">Active</label></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save</button></div>
        </form>
    </div></div>
</div>

@foreach ($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document"><div class="modal-content">
            <form action="{{ route('products.categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button class="close" type="button" data-dismiss="modal"><span>x</span></button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ $category->name }}" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea></div>
                    <div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="categoryEditActive{{ $category->id }}" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}><label class="custom-control-label" for="categoryEditActive{{ $category->id }}">Active</label></div>
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
