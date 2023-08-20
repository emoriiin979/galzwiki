<?php

namespace Database\Seeders;

use App\Models\Brief;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BriefSeeder extends Seeder
{
    use WithoutModelEvents;
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Brief::factory(1000)->sequence(function ($sequence) {

            $index = $sequence->index;
            $parent_brief_id = $index > 1 ? random_int(1, $index - 1) : null;

            return [
                'parent_brief_id' => $parent_brief_id,
                'entry_user_id' => 1,
            ];
        })->create();
    }
}
