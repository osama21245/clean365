<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            \Modules\CategoryManagement\Database\Seeders\Clean365CatalogSeeder::class,
            \Modules\CustomerModule\Database\Seeders\Clean365DemoCustomerSeeder::class,
            \Modules\CustomerModule\Database\Seeders\Clean365HomeFeedSeeder::class]);
    }
}
