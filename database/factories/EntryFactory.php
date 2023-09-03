<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entry>
 */
class EntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'           => $this->faker->unique()->name,
            'subtitle'        => $this->faker->realText($maxNbChars = 16),
            'body'            => $this->faker->realText($maxNbChars = 128),
            'parent_entry_id' => null,
            'post_user_id'    => null,  // 呼び出し元で必ず指定する
            'is_publish'      => true,
        ];
    }
}
