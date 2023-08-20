<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brief>
 */
class BriefFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->name,
            'note' => $this->faker->realText($maxNbChars = 16),
            'abstract' => $this->faker->realText($maxNbChars = 128),
            'hands_on' => $this->faker->realText($maxNbChars = 128),
            'parent_brief_id' => null,
            'entry_user_id' => null,  // 呼び出し元のsequenceで必ず指定する
            'entry_at' => $this->faker->dateTime($timezone = 'Asia/Tokyo'),
            'is_publish' => true,
        ];
    }
}
