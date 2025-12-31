<?php

namespace Database\Factories;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Snippet>
 */
class SnippetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $languages = ['php', 'javascript', 'python', 'java', 'typescript', 'go', 'rust'];

        return [
            'title' => fake()->sentence(4),
            'language' => fake()->randomElement($languages),
            'content' => fake()->paragraph(5),
            'folder_id' => Folder::factory(),
            'owner_type' => 'App\Models\User',
            'owner_id' => User::factory(),
            'created_by' => User::factory(),
            'ai_description' => null,
            'ai_processed_at' => null,
            'ai_processing_failed' => false,
            'user_tags' => [],
        ];
    }
}
