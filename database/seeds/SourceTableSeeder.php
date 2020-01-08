<?php

use Illuminate\Database\Seeder;

class SourceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sources')->insert([
                [
                    'name' => "Laravel News",
                ],
                [
                    'name' => "Demiart",
                ],
            ]
        );
    }
}
