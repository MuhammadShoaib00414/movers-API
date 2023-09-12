<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::create([
            'username' => 'Admin',
            'first_name' => 'Movers',
            'last_name' => 'Admin',
            'user_type' => 'admin',
            'email' => 'admin@yopmail.com',
            'password' => Hash::make('12345678')


        ]);
    }
}
