<?php

use App\Attributes\TableSize;
use Illuminate\Database\Seeder;

class TableSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $table_sizes = [
            ['table_size' => 'Full Ring'],
            ['table_size' => '6 Max'],
            ['table_size' => '8 Max'],
            ['table_size' => 'Heads Up'],
        ];

        TableSize::insert($table_sizes);
    }
}
