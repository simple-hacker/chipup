<?php

namespace Database\Seeders;

use App\Attributes\Variant;
use Illuminate\Database\Seeder;

class VariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $variants = [
            ['variant' => 'Texas Holdem'],
            ['variant' => 'Omaha Hi'],
            ['variant' => 'Omaha Hi-Lo'],
            ['variant' => 'Short Deck'],
            ['variant' => '6+'],
            ['variant' => 'Razz'],
            ['variant' => 'HORSE'],
            ['variant' => '7-Card Stud'],
            ['variant' => '2-7 Triple Draw'],
            ['variant' => '5-Card Draw'],
            ['variant' => '5-Card Omaha'],
            ['variant' => 'Badugi'],
            ['variant' => 'Stud 8'],
            ['variant' => 'Dealer\'s Choice'],
        ];

        Variant::insert($variants);
    }
}
