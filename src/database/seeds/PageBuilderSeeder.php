<?php

use Illuminate\Database\Seeder;

class PageBuilderSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call([
             RolePermissionsSeeder::class,
             SuperAdminSeeder::class,
         ]);
    }
}
