<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\ViewConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ViewConfiguration>
 */
class ViewConfigurationFactory extends Factory
{
    protected $model = ViewConfiguration::class;

    public function definition(): array
    {
        return [
            'role_id' => Role::factory(),
            'view_name' => fake()->randomElement(['dashboard', 'members_list', 'financials']),
            'config_json' => [
                'widgets' => ['income_by_term', 'unpaid_dues'],
            ],
        ];
    }
}

