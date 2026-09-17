<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // users.category_id is NOT NULL with a foreign key, so a category has
            // to exist before a user can be created at all.
            'category_id' => fn () => Category::query()->firstOrCreate(
                ['name' => 'General'],
                ['description' => 'Users not specific to any category'],
            )->id,
            // LoginRequest refuses to authenticate when this is false.
            'can_login' => true,
        ];
    }

    /**
     * A user who is not allowed to sign in.
     */
    public function cannotLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'can_login' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
