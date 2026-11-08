<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Membuat akun admin atau mengatur ulang kata sandinya dari terminal server.
 * Berguna saat pertama deploy atau bila semua orang lupa kata sandi.
 *
 *   php artisan admin:buat
 *   php artisan admin:buat --email=admin@gereja.org --name="Admin" --password=rahasia123
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:buat {--name= : Nama} {--email= : Email untuk masuk} {--password= : Kata sandi (min. 8)}';

    protected $description = 'Buat akun admin baru atau atur ulang kata sandi akun yang sudah ada';

    public function handle(): int
    {
        $email = mb_strtolower($this->option('email') ?: $this->ask('Email'));
        $existing = User::where('email', $email)->first();
        $name = $this->option('name') ?: ($existing?->name ?? $this->ask('Nama', 'Admin Multimedia'));
        $password = $this->option('password') ?: $this->secret('Kata sandi (min. 8 karakter)');

        $v = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', Password::min(8)],
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = $existing ?? new User(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->email_verified_at ??= now();
        $user->save();

        $this->info(($existing ? 'Kata sandi diperbarui untuk ' : 'Akun admin dibuat: ') . $email);

        return self::SUCCESS;
    }
}
