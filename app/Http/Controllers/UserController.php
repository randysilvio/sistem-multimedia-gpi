<?php

namespace App\Http\Controllers;

use App\Services\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Akun operator multimedia. Pendaftaran umum ditutup (aplikasi online bisa
 * diakses siapa saja), jadi akun baru hanya dibuat oleh pengguna yang sudah masuk.
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', ['users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('create', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], ['name' => 'nama', 'password' => 'kata sandi']);

        $user = new User(['name' => $data['name'], 'email' => mb_strtolower($data['email']), 'password' => Hash::make($data['password'])]);
        $user->email_verified_at = now();
        $user->save();

        ActivityLog::record('pengguna.tambah', $user, "Membuat akun {$user->name} ({$user->email})");
        return redirect()->route('users.index')->with('success', "Akun {$user->name} berhasil dibuat. Berikan email & kata sandinya kepada yang bersangkutan.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [], ['name' => 'nama', 'password' => 'kata sandi baru']);

        $user->name = $data['name'];
        $user->email = mb_strtolower($data['email']);
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        ActivityLog::record('pengguna.ubah', $user, "Mengubah akun {$user->name}" . ($request->filled('password') ? ' (kata sandi diganti)' : ''));
        return redirect()->route('users.index')->with('success', "Akun {$user->name} diperbarui.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }
        if (User::count() <= 1) {
            return back()->with('error', 'Minimal harus ada satu akun.');
        }
        $user->delete();

        ActivityLog::record('pengguna.hapus', null, "Menghapus akun {$user->name} ({$user->email})");
        return redirect()->route('users.index')->with('success', "Akun {$user->name} dihapus.");
    }
}
