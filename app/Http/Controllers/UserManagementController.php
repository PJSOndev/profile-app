<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $authUser = Auth::user();

        $usersQuery = User::query();
        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $usersQuery->where('branch_assigned', $authUser->branch_assigned);
        }

        $users = $usersQuery
            ->orderByDesc('id')
            ->get();

        $branchesQuery = Branch::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $branchesQuery->where('name', $authUser->branch_assigned);
        }

        $branches = $branchesQuery->get();

        $shifts = Shift::query()
            ->where('is_active', true)
            ->when($authUser && strtolower((string) $authUser->role) === 'admin', function ($q) use ($authUser): void {
                $q->where('branch_assigned', $authUser->branch_assigned);
            })
            ->orderBy('name')
            ->orderBy('time_in')
            ->get();

        $pendingUsersQuery = User::query()
            ->whereRaw('LOWER(status) = ?', ['pending'])
            ->orderByDesc('id');

        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $pendingUsersQuery->where('branch_assigned', $authUser->branch_assigned);
        }

        $pendingUsersCount = $pendingUsersQuery->count();

        return view('pages.tables', compact('users', 'pendingUsersCount', 'branches', 'shifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $authUser = Auth::user();
        $allowedRoles = $this->allowedAssignableRoles($authUser?->role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'status' => ['required', 'in:pending,approved,suspended'],
            'branch_assigned' => ['nullable', 'string', 'max:255', 'exists:branches,name'],
            'shift_id' => ['nullable', 'integer', 'exists:shift,shift_id'],
        ]);

        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $validated['branch_assigned'] = $authUser->branch_assigned;
            $this->guardAdminShiftAccess($validated['shift_id'] ?? null, $authUser->branch_assigned);
        }

        if ($validated['status'] === 'approved') {
            $validated['approved_at'] = now();
            $validated['approved_by'] = Auth::id();
        } else {
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $authUser = Auth::user();
        $this->guardAdminBranchAccess($authUser?->role, $authUser?->branch_assigned, $user->branch_assigned);
        $allowedRoles = $this->allowedAssignableRoles($authUser?->role);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'status' => ['required', 'in:pending,approved,suspended'],
            'branch_assigned' => ['nullable', 'string', 'max:255', 'exists:branches,name'],
            'shift_id' => ['nullable', 'integer', 'exists:shift,shift_id'],
        ]);

        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $validated['branch_assigned'] = $authUser->branch_assigned;
            $this->guardAdminShiftAccess($validated['shift_id'] ?? null, $authUser->branch_assigned);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if ($validated['status'] === 'approved') {
            if (strtolower((string) $user->status) !== 'approved') {
                $validated['approved_at'] = now();
                $validated['approved_by'] = Auth::id();
            }
        } else {
            $validated['approved_at'] = null;
            $validated['approved_by'] = null;
        }

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $authUser = Auth::user();
        $this->guardAdminBranchAccess($authUser?->role, $authUser?->branch_assigned, $user->branch_assigned);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function approve(User $user): RedirectResponse
    {
        $authUser = Auth::user();
        $this->guardAdminBranchAccess($authUser?->role, $authUser?->branch_assigned, $user->branch_assigned);

        if (strtolower((string) $user->status) !== 'pending') {
            return redirect()
                ->route('users.index')
                ->with('success', 'User is already approved.');
        }

        $user->status = 'approved';
        $user->approved_at = now();
        $user->approved_by = Auth::id();
        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User approved successfully.');
    }

    public function approveAllPending(): RedirectResponse
    {
        $authUser = Auth::user();

        $query = User::query()
            ->whereRaw('LOWER(status) = ?', ['pending']);

        if ($authUser && strtolower((string) $authUser->role) === 'admin') {
            $query->where('branch_assigned', $authUser->branch_assigned);
        }

        $approvedCount = $query->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

        return redirect()
            ->route('users.index')
            ->with('success', $approvedCount . ' pending user(s) approved successfully.');
    }

    private function guardAdminBranchAccess(?string $role, ?string $actorBranch, ?string $targetBranch): void
    {
        if (strtolower((string) $role) === 'admin' && (string) $actorBranch !== (string) $targetBranch) {
            abort(403, 'You can only access users from your assigned branch.');
        }
    }

    private function guardAdminShiftAccess(?int $shiftId, ?string $actorBranch): void
    {
        if (empty($shiftId)) {
            return;
        }

        $shift = Shift::query()->find($shiftId);
        if (! $shift || (string) $shift->branch_assigned !== (string) $actorBranch) {
            abort(403, 'You can only assign shifts from your branch.');
        }
    }

    /**
     * @return list<string>
     */
    private function allowedAssignableRoles(?string $role): array
    {
        return strtolower((string) $role) === 'super admin'
            ? ['super admin', 'owner', 'admin', 'user']
            : ['admin', 'user'];
    }
}
