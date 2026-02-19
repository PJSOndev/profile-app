<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $authUser = Auth::user();

        $usersQuery = User::query();
        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $usersQuery->where('branch_assigned', $authUser->branch_assigned);
        }

        $registeredUsers = (clone $usersQuery)->count();
        $pendingUsers = (clone $usersQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();
        $approvedUsers = (clone $usersQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();

        return view('pages.index', [
            'registeredUsers' => $registeredUsers,
            'pendingUsers' => $pendingUsers,
            'approvedUsers' => $approvedUsers,
        ]);
    }
}
