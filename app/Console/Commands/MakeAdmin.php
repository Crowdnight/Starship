<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email : Email akun yang akan dijadikan admin}';

    protected $description = 'Promosikan akun yang sudah terdaftar menjadi admin';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('Akun dengan email tersebut tidak ditemukan.');

            return self::FAILURE;
        }

        $user->role = 'admin';
        $user->is_active = true;
        $user->save();

        $this->info("{$user->email} sekarang memiliki role admin.");

        return self::SUCCESS;
    }
}
