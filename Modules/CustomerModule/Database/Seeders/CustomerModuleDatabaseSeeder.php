<?php

namespace Modules\CustomerModule\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CustomerModuleDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            Clean365DemoCustomerSeeder::class,
            Clean365HomeFeedSeeder::class]);
    }
}
