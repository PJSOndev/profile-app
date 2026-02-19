<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryManagementController extends Controller
{
    public function index(Request $request): View
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';
        $actorBranch = $authUser?->branch_assigned;

        $selectedDateInput = $request->input('date');
        $selectedDate = $selectedDateInput
            ? Carbon::parse($selectedDateInput)->startOfDay()
            : Carbon::today();

        $selectedDateStr = $selectedDate->toDateString();
        $yesterdayEnd = $selectedDate->copy()->subDay()->endOfDay();
        $selectedDayStart = $selectedDate->copy()->startOfDay();
        $selectedDayEnd = $selectedDate->copy()->endOfDay();

        $inBeforeRows = DB::table('inventory')
            ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as qty')
            ->where('type', 'IN')
            ->whereNotNull('product_id')
            ->where('date', '<=', $yesterdayEnd)
            ->when($isAdmin, fn ($q) => $q->where('branch_assigned', $actorBranch))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $outBeforeRows = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->selectRaw('ti.product_id, COALESCE(SUM(ti.quantity), 0) as qty')
            ->whereNotNull('ti.product_id')
            ->where('t.date', '<=', $yesterdayEnd)
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->groupBy('ti.product_id')
            ->get()
            ->keyBy('product_id');

        $inTodayRows = DB::table('inventory')
            ->selectRaw('product_id, COALESCE(SUM(quantity), 0) as qty')
            ->where('type', 'IN')
            ->whereNotNull('product_id')
            ->whereBetween('date', [$selectedDayStart, $selectedDayEnd])
            ->when($isAdmin, fn ($q) => $q->where('branch_assigned', $actorBranch))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $outTodayRows = DB::table('transaction_items as ti')
            ->join('transactions as t', 't.id', '=', 'ti.transaction_id')
            ->leftJoin('users as tu', 'tu.id', '=', 't.user_id')
            ->selectRaw('ti.product_id, COALESCE(SUM(ti.quantity), 0) as qty')
            ->whereNotNull('ti.product_id')
            ->whereBetween('t.date', [$selectedDayStart, $selectedDayEnd])
            ->when($isAdmin, fn ($q) => $q->where('tu.branch_assigned', $actorBranch))
            ->groupBy('ti.product_id')
            ->get()
            ->keyBy('product_id');

        $rows = DB::table('products as p')
            ->whereNull('p.deleted_at')
            ->selectRaw('p.id as product_id, p.name as item_name')
            ->orderBy('p.name')
            ->get()
            ->map(function ($row) use ($inBeforeRows, $outBeforeRows, $inTodayRows, $outTodayRows) {
                $productId = (int) $row->product_id;

                $inBefore = (int) ($inBeforeRows[$productId]->qty ?? 0);
                $outBefore = (int) ($outBeforeRows[$productId]->qty ?? 0);
                $inToday = (int) ($inTodayRows[$productId]->qty ?? 0);
                $outToday = (int) ($outTodayRows[$productId]->qty ?? 0);

                $beginning = $inBefore - $outBefore;
                $ending = $beginning + $inToday - $outToday;

                return (object) [
                    'product_id' => $productId,
                    'item_name' => $row->item_name,
                    'beginning' => $beginning,
                    'in' => $inToday,
                    'out' => $outToday,
                    'ending' => $ending,
                ];
            });

        return view('pages.inventory', [
            'rows' => $rows,
            'selectedDate' => $selectedDateStr,
        ]);
    }

    public function storeIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'date' => ['nullable', 'date'],
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);
        $entryDate = ! empty($validated['date'])
            ? Carbon::parse($validated['date'])->setTimeFromTimeString(now()->format('H:i:s'))
            : now();

        Inventory::create([
            'product_id' => $product->id,
            'type' => 'IN',
            'quantity' => $validated['quantity'],
            'date' => $entryDate,
            'branch_assigned' => Auth::user()?->branch_assigned,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('inventory.index', ['date' => $entryDate->toDateString()])
            ->with('success', 'Quantity added to inventory successfully.');
    }

    public function logs(Request $request): View
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';

        $selectedDateInput = $request->input('date');

        $logsQuery = DB::table('inventory as i')
            ->leftJoin('products as p', 'p.id', '=', 'i.product_id')
            ->leftJoin('users as u', 'u.id', '=', 'i.created_by')
            ->selectRaw('
                i.id,
                i.date,
                i.type,
                i.quantity,
                i.branch_assigned,
                i.notes,
                p.name as item_name,
                u.name as added_by_name
            ')
            ->when($isAdmin, fn ($q) => $q->where('i.branch_assigned', $authUser?->branch_assigned))
            ->orderByDesc('i.date')
            ->orderByDesc('i.id');

        if (! empty($selectedDateInput)) {
            $logsQuery->whereDate('i.date', $selectedDateInput);
        }

        $logs = $logsQuery->get();

        return view('pages.inventory-logs', [
            'logs' => $logs,
            'selectedDate' => $selectedDateInput,
        ]);
    }
}
