<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tour;
use App\Http\Controllers\TourController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TourControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_a_tour_directly()
    {
        $request = Request::create('/api/tours', 'POST', [
            'name' => 'Unit Test Tour',
            'description' => 'Test description',
            'price' => 100.0,
            'image' => 'https://example.com/img.jpg',
        ]);

        $controller = new TourController();
        $response = $controller->store($request);

        $this->assertEquals(201, $response->status());
        $this->assertDatabaseHas('tours', ['name' => 'Unit Test Tour']);
    }

    /** @test */
    public function it_can_show_a_tour_directly()
    {
        $tour = Tour::factory()->create(['name' => 'Unit Show']);

        $controller = new TourController();
        $response = $controller->show($tour->id);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Unit Show', $response->getContent());
    }
}
