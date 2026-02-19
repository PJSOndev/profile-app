<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShiftManagementController extends Controller
{
    public function index(): View
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';

        $shifts = Shift::query()
            ->when($isAdmin, fn ($q) => $q->where('branch_assigned', $authUser->branch_assigned))
            ->orderBy('name')
            ->orderBy('time_in')
            ->get();

        $branches = Branch::query()
            ->where('is_active', true)
            ->when($isAdmin, fn ($q) => $q->where('name', $authUser->branch_assigned))
            ->orderBy('name')
            ->get();

        return view('pages.user-shifts', compact('shifts', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'role_scope' => ['required', 'in:super admin,owner,admin,user'],
            'description' => ['nullable', 'string'],
            'branch_assigned' => ['nullable', 'string', 'max:255', 'exists:branches,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($isAdmin) {
            $validated['branch_assigned'] = $authUser->branch_assigned;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        Shift::create($validated);

        return redirect()
            ->route('users.shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';
        $this->guardAdminBranchAccess(
            $isAdmin,
            $authUser?->branch_assigned,
            $shift->branch_assigned
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i'],
            'role_scope' => ['required', 'in:super admin,owner,admin,user'],
            'description' => ['nullable', 'string'],
            'branch_assigned' => ['nullable', 'string', 'max:255', 'exists:branches,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($isAdmin) {
            $validated['branch_assigned'] = $authUser->branch_assigned;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['updated_by'] = Auth::id();

        $shift->update($validated);

        return redirect()
            ->route('users.shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $authUser = Auth::user();
        $isAdmin = $authUser && strtolower((string) $authUser->role) === 'admin';
        $this->guardAdminBranchAccess(
            $isAdmin,
            $authUser?->branch_assigned,
            $shift->branch_assigned
        );

        $shift->deleted_by = Auth::id();
        $shift->save();
        $shift->delete();

        return redirect()
            ->route('users.shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }

    private function guardAdminBranchAccess(bool $isAdmin, ?string $actorBranch, ?string $targetBranch): void
    {
        if ($isAdmin && (string) $actorBranch !== (string) $targetBranch) {
            abort(403, 'You can only access shifts from your assigned branch.');
        }
    }
}
