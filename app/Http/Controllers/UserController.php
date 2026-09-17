<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('wa_instance_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role') && in_array($request->role, ['superadmin', 'admin', 'pengguna'], true)) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => User::count(),
            'superadmin' => User::where('role', 'superadmin')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'pengguna' => User::where('role', 'pengguna')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        $allowedRoles = ['admin', 'pengguna'];
        if ($currentUser->isSuperAdmin()) {
            $allowedRoles[] = 'superadmin';
        }

        $waInstanceRules = ['nullable', 'string', 'alpha_dash', 'max:50'];
        if (! $currentUser->isSuperAdmin()) {
            $waInstanceRules[] = 'unique:users,wa_instance_name';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'wa_instance_name' => $waInstanceRules,
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'wa_instance_name' => ! empty($validated['wa_instance_name']) ? strtolower(trim($validated['wa_instance_name'])) : null,
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record(
            action: 'user_create',
            description: "Membuat akun user baru: {$user->name} (@{$user->username}) dengan role {$user->role}",
            subject: $user,
            properties: [
                'created_user_id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'wa_instance_name' => $user->wa_instance_name,
            ]
        );

        return redirect()->route('users.index')->with('status', "User {$user->name} berhasil ditambahkan.");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // An admin cannot edit a superadmin
        if ($user->isSuperAdmin() && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah data Superadmin.');
        }

        $allowedRoles = ['admin', 'pengguna'];
        if ($currentUser->isSuperAdmin()) {
            $allowedRoles[] = 'superadmin';
        }

        $waInstanceRules = ['nullable', 'string', 'alpha_dash', 'max:50'];
        if (! $currentUser->isSuperAdmin()) {
            $waInstanceRules[] = Rule::unique('users', 'wa_instance_name')->ignore($user->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'wa_instance_name' => $waInstanceRules,
            'password' => ['nullable', 'string', Password::defaults()],
        ]);

        // Prevent demoting the last superadmin
        if ($user->isSuperAdmin() && $validated['role'] !== 'superadmin') {
            $superAdminCount = User::where('role', 'superadmin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Tidak dapat mengubah role Superadmin terakhir.');
            }
        }

        $userData = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'role' => $validated['role'],
            'wa_instance_name' => ! empty($validated['wa_instance_name']) ? strtolower(trim($validated['wa_instance_name'])) : null,
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $user->update($userData);

        ActivityLog::record(
            action: 'user_update',
            description: "Memperbarui data user: {$user->name} (@{$user->username})",
            subject: $user,
            properties: [
                'updated_user_id' => $user->id,
                'role' => $user->role,
                'password_changed' => ! empty($validated['password']),
            ]
        );

        return redirect()->route('users.index')->with('status', "Data user {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Cannot delete self
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // An admin cannot delete a superadmin
        if ($user->isSuperAdmin() && ! $currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Superadmin yang dapat menghapus akun Superadmin.');
        }

        // Prevent deleting the last superadmin
        if ($user->isSuperAdmin()) {
            $superAdminCount = User::where('role', 'superadmin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus Superadmin terakhir dalam sistem.');
            }
        }

        $userName = $user->name;
        $userUsername = $user->username;
        $userId = $user->id;

        $user->delete();

        ActivityLog::record(
            action: 'user_delete',
            description: "Menghapus akun user: {$userName} (@{$userUsername})",
            subject: null,
            properties: [
                'deleted_user_id' => $userId,
                'deleted_username' => $userUsername,
            ]
        );

        return redirect()->route('users.index')->with('status', "User {$userName} berhasil dihapus.");
    }
}
