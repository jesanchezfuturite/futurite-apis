<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use DB;

class statusCustomers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $statuses = [
            ['description' => 'UNKNOWN'],
            ['description' => 'ENABLED'],
            ['description' => 'CANCELED'],
            ['description' => 'SUSPENDED'],
            ['description' => 'CLOSED'],
            ['description' => 'PENDING'],
        ];

        DB::table('status_customers')->insert($statuses);
    }
}
