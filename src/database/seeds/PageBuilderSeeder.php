<?php

use Illuminate\Database\Seeder;

/**
 * Class PageBuilderSeeder
 */
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
         ]);
    }
}
