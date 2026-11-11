<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Set ADMIN_EMAIL to a valid email address before seeding the admin account.');
        }

        if (! is_string($password) || strlen($password) < 8) {
            throw new RuntimeException('Set ADMIN_PASSWORD to a password with at least 8 characters before seeding the admin account.');
        }

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->name = 'Admin';
            $user->password = $password;
        }

        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
    }
}
