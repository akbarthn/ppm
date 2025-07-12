<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nama' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('123'),
                'address' => 'Jl. Contoh No. 1',
                'phone' => '081234567890',
                'image' => 'superadmin.jpg',
                'role' => 'superadmin',
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Admin Biasa',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('123'),
                'address' => 'Jl. Admin No. 2',
                'phone' => '089876543210',
                'image' => 'admin.jpg',
                'role' => 'admin',
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
