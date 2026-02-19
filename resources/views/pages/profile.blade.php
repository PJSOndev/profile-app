<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Profile</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<body id="page-top">
@php
    $currentRole = strtolower((string) auth()->user()->role);
    $canAccessManagement = in_array($currentRole, ['super admin', 'owner', 'admin'], true);
    $isViewOnly = $currentRole === 'owner';
    $homeRoute = $canAccessManagement ? route('dashboard') : route('profile.show');
@endphp
<div id="wrapper">
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ $homeRoute }}">
            <div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-user"></i></div>
            <div class="sidebar-brand-text mx-3">Profile App</div>
        </a>

        <hr class="sidebar-divider my-0">

        @if ($canAccessManagement)
            <li class="nav-item">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        @endif

        <li class="nav-item active">
            <a class="nav-link" href="{{ route('profile.show') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>My Profile</span>
            </a>
        </li>

        @if ($canAccessManagement)
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
        @endif
    </ul>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <h1 class="h5 mb-0 text-gray-800">My Profile</h1>
                <div class="ml-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                    </form>
                </div>
            </nav>

            <div class="container-fluid">
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

                <div class="row">
                    <div class="col-xl-6 col-lg-7">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Update Account</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>New Password (optional)</label>
                                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                    </div>

                                    <div class="form-group">
                                        <label>Confirm New Password</label>
                                        <input type="password" name="password_confirmation" class="form-control">
                                    </div>

                                    @if ($isViewOnly)
                                        <div class="alert alert-info mb-0">Owner role is view-only.</div>
                                    @else
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-lg-5">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Account Details</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                                <p class="mb-2"><strong>Branch:</strong> {{ $user->branch_assigned ?: '-' }}</p>
                                <p class="mb-2"><strong>Role:</strong> {{ ucfirst($user->role ?? 'user') }}</p>
                                <p class="mb-2"><strong>Status:</strong> {{ ucfirst($user->status ?? 'pending') }}</p>
                                <p class="mb-0"><strong>Created:</strong> {{ optional($user->created_at)->format('Y-m-d H:i') }}</p>
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
</body>
</html>
