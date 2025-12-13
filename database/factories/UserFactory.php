<?php

namespace Database\Factories;

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
        $first = fake()->firstName();
        $last = fake()->lastName();

        return [
            'name' => "{$first} {$last}",
            'first_name' => $first,
            'middle_name' => fake()->optional()->lastName(),
            'last_name' => $last,
            'extension_name' => null,
            'lrn' => fake()->unique()->numerify(str_repeat('#', 12)),
            'grade' => 'Grade ' . fake()->numberBetween(7, 12),
            'section' => fake()->randomElement(['Rizal', 'Bonifacio', 'Mabini']),
            'adviser' => 'Mr./Ms. ' . fake()->lastName(),
            'zipcode' => fake()->postcode(),
            'house_no' => (string) fake()->numberBetween(1, 999),
            'street_name' => fake()->streetName(),
            'barangay' => 'Brgy. ' . fake()->word(),
            'municipality' => fake()->city(),
            'province' => fake()->state(),
            'country' => 'Philippines',
            'email' => fake()->unique()->safeEmail(),
            'mobile' => '09' . fake()->numerify('#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
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
