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
                'Username' => 'admin',
                'Password' => 'secret123',
                'Email'    => 'admin@example.local',
                'Role'     => 'admin',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Username' => 'testuser',
                'Password' => 'wachtwoord',
                'Email'    => 'user@example.local',
                'Role'     => 'user',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Username' => 'Bloem',
                'Password' => 'Password123',
                'Email'    => 'Bloem@example.local',
                'Role'     => 'user',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'Username' => 'Roos',
                'Password' => 'Lente12345',
                'Email'    => 'Roos@example.local',
                'Role'     => 'user',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
