<?php

namespace Database\Factories;

use App\Models\Permit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permit>
 */
class PermitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate' => 'AA123BB',
            'holder' => 'Hotel Test',
            'type' => 'NCC',
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay(),
            'status' => 'active',
            'qr_token' => Str::uuid(),
        ];
    }
}
