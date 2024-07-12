<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Carbon\Carbon;

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
            ['description' => 'UNKNOWN','created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['description' => 'ENABLED','created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['description' => 'PAUSED','created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['description' => 'REMOVED','created_at' => Carbon::now()->format('Y-m-d H:i:s'),
    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')]
        ];

        DB::table('status_campaigns')->insert($statuses);
    }
}
