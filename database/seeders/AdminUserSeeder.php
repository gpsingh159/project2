<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([

            'name' => 'Admin',

            'email' => 'admin@gmail.com',

            'password' => bcrypt('Admin@123'),

            'role' =>  1

        ]);
    }
}
