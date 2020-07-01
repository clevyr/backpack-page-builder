<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'name' => 'Super Admin User',
            'email' => 'super-admin@example.com',
            'password' => Hash::make('password'),
        ])->assignRole('Super Admin');
    }
}
