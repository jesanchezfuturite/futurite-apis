<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use DB;

class statusCampaigns extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        /**
     * Run the database seeds.
     */

        //
        $statuses = [
            ['description' => 'UNKNOWN'],
            ['description' => 'ENABLED'],
            ['description' => 'PAUSED'],
            ['description' => 'REMOVED']
        ];

        DB::table('status_campaigns')->insert($statuses);
    }
}
