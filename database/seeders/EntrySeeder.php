<?php

namespace Database\Seeders;

use App\Models\Entry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EntrySeeder extends Seeder
{
    use WithoutModelEvents;
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Entry::factory(1000)->sequence(function ($sequence) {

            // 登録済みIDの中から親項目IDをランダムに取得する
            $index = $sequence->index;
            $parentEntryId = $index > 1 ? random_int(1, $index - 1) : null;

            return [
                'parent_entry_id' => $parentEntryId,
                'post_user_id' => 1,
            ];
        })->create();
    }
}
