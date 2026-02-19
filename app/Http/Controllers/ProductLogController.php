<?php

namespace App\Http\Controllers;

use App\Models\ProductLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductLogController extends Controller
{
    public function index(): View
    {
        $authUser = Auth::user();

        $logs = ProductLog::query()
            ->with(['actor', 'product'])
            ->when($authUser && strtolower((string) $authUser->role) === 'admin', function ($q) use ($authUser): void {
                $q->whereHas('actor', function ($actorQuery) use ($authUser): void {
                    $actorQuery->where('branch_assigned', $authUser->branch_assigned);
                });
            })
            ->orderByDesc('performed_at')
            ->orderByDesc('id')
            ->get();

        return view('pages.product-logs', compact('logs'));
    }
}
