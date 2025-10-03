<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->unique()->safeEmail,
            'phone'      => $this->faker->phoneNumber,
            'designation'=> $this->faker->jobTitle,
            'salary'     => $this->faker->randomFloat(2, 20000, 100000),
            'joining_date' => $this->faker->date(),
            'status'     => 'active',
        ];
    }
}
