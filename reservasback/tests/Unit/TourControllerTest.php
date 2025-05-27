<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TourControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_tours()
    {
        // Crea 2 tours en BD
        Tour::factory()->create(['name' => 'Tour A']);
        Tour::factory()->create(['name' => 'Tour B']);

        // Llama al endpoint
        $response = $this->getJson('/api/tours');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['name' => 'Tour A'])
            ->assertJsonFragment(['name' => 'Tour B']);
    }

    /** @test */
    public function show_returns_specific_tour()
    {
        $tour = Tour::factory()->create([
            'name'        => 'Cusco Tour',
            'description' => 'Desc Cusco',
            'price'       => 50.0,
            'image'       => 'https://example.com/cusco.jpg',
        ]);

        $response = $this->getJson("/api/tours/{$tour->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id'          => $tour->id,
                'name'        => 'Cusco Tour',
                'description' => 'Desc Cusco',
                'price'       => 50.0,
                'image'       => 'https://example.com/cusco.jpg',
            ]);
    }

    /** @test */
    public function store_creates_a_new_tour()
    {
        $payload = [
            'name'        => 'Amazon Adventure',
            'description' => 'Jungle tour',
            'price'       => 120.50,
            'image'       => 'https://example.com/amazon.jpg',
        ];

        $response = $this->postJson('/api/tours', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Amazon Adventure']);

        $this->assertDatabaseHas('tours', [
            'name'  => 'Amazon Adventure',
            'price' => 120.50,
        ]);
    }

    /** @test */
    public function update_modifies_existing_tour()
    {
        $tour = Tour::factory()->create([
            'name'  => 'Old Name',
            'price' => 80.0,
        ]);

        $payload = [
            'name'  => 'New Name',
            'price' => 99.99,
        ];

        $response = $this->putJson("/api/tours/{$tour->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('tours', [
            'id'    => $tour->id,
            'name'  => 'New Name',
            'price' => 99.99,
        ]);
    }

    /** @test */
    public function destroy_deletes_tour_and_returns_no_content()
    {
        $tour = Tour::factory()->create();

        $response = $this->deleteJson("/api/tours/{$tour->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tours', ['id' => $tour->id]);
    }
}
