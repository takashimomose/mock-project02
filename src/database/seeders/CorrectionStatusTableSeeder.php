<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CorrectionStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $correctionStatus = [
            [
                'id' => 1,
                'status_name' => '承認待ち',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'status_name' => '承認済み',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('correction_status')->insert($correctionStatus);
    }
}
