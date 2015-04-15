<?php

use Illuminate\Database\Seeder;

// composer require laracasts/testdummy

class SongTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('songs')->truncate();
        $names = ['Baz', 'Qoo'];
        foreach (range(1, 10) as $j) {
            $name = in_array($j, [1, 4, 7])
                ? 'Bar'
                : $names[ array_rand($names) ];
            $name .= " $j";
            DB::table('songs')->insert(['name' => $name]);
        }
    }
}
