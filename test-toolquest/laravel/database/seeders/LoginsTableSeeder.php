<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('logins')->insert([
            [
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => 'password123',
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'moderator',
                'email' => 'moderator@example.com',
                'password' => 'password123',
                'role' => 'moderator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => 'password123',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
