<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $role = $request->get('role');
        
        $users = User::when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('username', 'like', "%{$search}%")
                          ->orWhere('full_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query) use ($role) {
                $query->where('role', $role);
            })
            ->orderBy('full_name')
            ->paginate(10);

        $topRole = User::select('role')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('role')
            ->orderByDesc('total')
            ->first()?->role;

        $latestUser = User::latest('created_at')->first();

        return view('users.index', compact('users', 'search', 'role', 'topRole', 'latestUser'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:admin,team_lead,developer,designer',
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'full_name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'role.required' => 'Role wajib dipilih',
        ]);

        User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($userId)
    {
        $user = User::with(['projectMembers.project', 'assignments.card'])->findOrFail($userId);
        
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')
            ],
            'full_name' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')
            ],
            'role' => 'required|in:admin,team_lead,developer,designer',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'full_name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
            'role.required' => 'Role wajib dipilih',
        ]);

        $updateData = [
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($userId)
    {
        $user = User::findOrFail($userId);
        
        // Cek jika user sedang digunakan di project members
        if ($user->projectMembers()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus user karena masih terdaftar dalam proyek.');
        }
        
        // Cek jika user sedang digunakan di card assignments
        if ($user->assignments()->exists()) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak dapat menghapus user karena masih memiliki tugas card.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
