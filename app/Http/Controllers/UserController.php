<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Menampilkan halaman manajemen pengguna.
     */
    public function index()
    {
        // Pastikan hanya 'pengelola' yang bisa mengakses halaman ini.
        if (! Gate::allows('manage-users')) {
            abort(403);
        }

        // Ambil semua user kecuali user yang sedang login, urutkan berdasarkan nama.
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();
        return view('manage-user', compact('users'));
    }

    /**
     * Memperbarui role pengguna.
     */
    public function updateRole(Request $request, User $user)
    {
        // Pastikan hanya 'pengelola' yang bisa mengubah role.
        if (! Gate::allows('manage-users')) {
            abort(403);
        }
        
        // Validasi input role
        $request->validate([
            'role' => 'required|in:anggota,kasir,pengelola',
        ]);

        // Update role user
        $user->role = $request->role;
        $user->save();

        return back()->with('success', "Role untuk pengguna {$user->name} berhasil diperbarui.");
    }
}