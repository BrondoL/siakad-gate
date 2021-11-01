<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username'      => "admin",
            'password'      => Hash::make("admin", ['rounds' => 12]),
            'email'         => "admin@gmail.com",
            'ip'            => "127.0.0.1",
            'role_id'       => 1,
        ]);
    }
}
