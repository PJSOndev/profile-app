<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';
        $actorBranch = $authUser?->branch_assigned;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $paymentMethod = $request->input('payment_method');

        $transactionsQuery = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->leftJoin('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->leftJoin('products as p', 'p.id', '=', 'ti.product_id')
            ->selectRaw('
                t.id,
                t.user_id,
                t.date,
                t.payment_method,
                COALESCE(t.discount, 0) as discount,
                COALESCE(t.total, 0) as total,
                GROUP_CONCAT(DISTINCT COALESCE(p.name, ti.product_name) ORDER BY COALESCE(p.name, ti.product_name) SEPARATOR ", ") as product_names,
                COALESCE(SUM(ti.quantity), 0) as total_items
            ')
            ->groupBy('t.id', 't.user_id', 't.date', 't.payment_method', 't.discount', 't.total');

        if ($isAdmin) {
            $transactionsQuery->where('tu.branch_assigned', $actorBranch);
        }

        if (! empty($startDate)) {
            $transactionsQuery->whereDate('t.date', '>=', $startDate);
        }

        if (! empty($endDate)) {
            $transactionsQuery->whereDate('t.date', '<=', $endDate);
        }

        if (! empty($paymentMethod)) {
            $transactionsQuery->where('t.payment_method', $paymentMethod);
        }

        $transactions = $transactionsQuery
            ->orderByDesc('t.date')
            ->get();

        $transactionSummary = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('
                COUNT(*) as total_transactions,
                COALESCE(SUM(t.total), 0) as gross_sales,
                COALESCE(SUM(t.discount), 0) as total_discounts
            ')
            ->first();

        $itemSummary = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('COALESCE(SUM(ti.quantity), 0) as total_items_sold')
            ->first();

        $topProducts = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->leftJoin('products as p', 'p.id', '=', 'ti.product_id')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('
                ti.product_id,
                COALESCE(p.name, "Unknown Product") as product_name,
                COALESCE(SUM(ti.quantity), 0) as total_qty,
                COALESCE(SUM(ti.quantity * ti.price), 0) as total_amount
            ')
            ->whereNotNull('ti.product_id')
            ->groupBy('ti.product_id', 'p.name')
            ->orderByDesc('total_qty')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $paymentMethods = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->select('t.payment_method')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->whereNotNull('t.payment_method')
            ->where('t.payment_method', '!=', '')
            ->distinct()
            ->orderBy('t.payment_method')
            ->pluck('t.payment_method');

        return view('pages.sales-report', [
            'transactions' => $transactions,
            'summary' => (object) [
                'total_transactions' => (int) ($transactionSummary->total_transactions ?? 0),
                'gross_sales' => (float) ($transactionSummary->gross_sales ?? 0),
                'total_discounts' => (float) ($transactionSummary->total_discounts ?? 0),
                'total_items_sold' => (int) ($itemSummary->total_items_sold ?? 0),
            ],
            'topProducts' => $topProducts,
            'paymentMethods' => $paymentMethods,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $paymentMethod,
            ],
        ]);
    }

    public function salesByUser(Request $request): View
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';
        $actorBranch = $authUser?->branch_assigned;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $paymentMethod = $request->input('payment_method');

        $itemsByUser = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->leftJoin('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('
                t.user_id,
                COALESCE(SUM(ti.quantity), 0) as total_qty
            ')
            ->groupBy('t.user_id');

        $salesByUser = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->leftJoinSub($itemsByUser, 'iu', function ($join): void {
                $join->on('iu.user_id', '=', 't.user_id');
            })
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('
                t.user_id,
                COALESCE(u.name, "Unknown User") as user_name,
                COUNT(t.id) as transaction_count,
                COALESCE(SUM(t.total), 0) as total_amount,
                COALESCE(SUM(t.discount), 0) as total_discounts,
                COALESCE(MAX(iu.total_qty), 0) as total_qty
            ')
            ->groupBy('t.user_id', 'u.name')
            ->orderByDesc('total_amount')
            ->get();

        $paymentMethods = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->select('t.payment_method')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->whereNotNull('t.payment_method')
            ->where('t.payment_method', '!=', '')
            ->distinct()
            ->orderBy('t.payment_method')
            ->pluck('t.payment_method');

        return view('pages.sales-report-by-user', [
            'salesByUser' => $salesByUser,
            'paymentMethods' => $paymentMethods,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $paymentMethod,
            ],
        ]);
    }

    public function salesByShift(Request $request): View
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';
        $actorBranch = $authUser?->branch_assigned;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $paymentMethod = $request->input('payment_method');

        $itemsByShift = DB::table('transactions as t')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('transaction_items as ti', 'ti.transaction_id', '=', 't.id')
            ->when($isAdmin, fn ($q) => $q->where('u.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('
                u.shift_id,
                COALESCE(SUM(ti.quantity), 0) as total_qty
            ')
            ->groupBy('u.shift_id');

        $salesByShift = DB::table('transactions as t')
            ->leftJoin('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('shift as s', 's.shift_id', '=', 'u.shift_id')
            ->leftJoinSub($itemsByShift, 'isf', function ($join): void {
                $join->on('isf.shift_id', '=', 'u.shift_id');
            })
            ->when($isAdmin, fn ($q) => $q->where('u.branch_assigned', $actorBranch))
            ->when(! empty($startDate), fn ($q) => $q->whereDate('t.date', '>=', $startDate))
            ->when(! empty($endDate), fn ($q) => $q->whereDate('t.date', '<=', $endDate))
            ->when(! empty($paymentMethod), fn ($q) => $q->where('t.payment_method', $paymentMethod))
            ->selectRaw('
                u.shift_id,
                COALESCE(s.name, "Unassigned Shift") as shift_name,
                s.time_in,
                s.time_out,
                COALESCE(s.role_scope, "user") as role_scope,
                COUNT(t.id) as transaction_count,
                COALESCE(SUM(t.total), 0) as total_amount,
                COALESCE(SUM(t.discount), 0) as total_discounts,
                COALESCE(MAX(isf.total_qty), 0) as total_qty
            ')
            ->groupBy('u.shift_id', 's.name', 's.time_in', 's.time_out', 's.role_scope')
            ->orderByDesc('total_amount')
            ->get();

        $paymentMethods = DB::table('transactions as t')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->select('t.payment_method')
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->whereNotNull('t.payment_method')
            ->where('t.payment_method', '!=', '')
            ->distinct()
            ->orderBy('t.payment_method')
            ->pluck('t.payment_method');

        return view('pages.sales-report-by-shift', [
            'salesByShift' => $salesByShift,
            'paymentMethods' => $paymentMethods,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $paymentMethod,
            ],
        ]);
    }
}
