<?php

namespace Database\Factories;

use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaceFactory extends Factory
{
    protected $model = Place::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->word(),
            'excerpt'    => $this->faker->sentence(),
            'activities' => ['hiking','kayaking'],
            'stats'      => ['likes' => $this->faker->numberBetween(0,1000)],
            'image_url'  => $this->faker->imageUrl(),
            'latitude'   => $this->faker->latitude(),
            'longitude'  => $this->faker->longitude(),
            'category'   => 'Naturaleza',
        ];
    }
}
