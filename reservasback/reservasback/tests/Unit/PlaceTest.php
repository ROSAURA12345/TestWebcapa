<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Place;

class PlaceTest extends TestCase
{
    /** @test */
    public function it_can_create_a_place_with_valid_data()
    {
        $data = [
            'name'      => 'Lago Titicaca',
            'excerpt'   => 'Un lago navegable en altitud',
            'activities'=> ['pesca', 'paseo en bote'],
            'stats'     => ['visitas' => 10000],
            'image_url' => 'https://example.com/image.jpg',
            'latitude'  => -15.8402,
            'longitude' => -70.0219,
            'category'  => 'Naturaleza',
        ];

        $place = new Place($data);

        $this->assertEquals('Lago Titicaca', $place->name);
        $this->assertIsArray($place->activities);
        $this->assertIsArray($place->stats);
        $this->assertEquals('Naturaleza', $place->category);
    }

    /** @test */
    public function it_casts_activities_and_stats_to_array()
    {
        $place = new Place([
            'activities' => ['hiking', 'swimming'],
            'stats' => ['likes' => 200],
        ]);


        // Laravel automáticamente convierte JSON a array si está en los casts
        $this->assertIsArray($place->activities);
        $this->assertEquals(['hiking', 'swimming'], $place->activities);

        $this->assertIsArray($place->stats);
        $this->assertEquals(['likes' => 200], $place->stats);
    }
}
