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
        Tour::factory()->create(['name' => 'Tour A']);
        Tour::factory()->create(['name' => 'Tour B']);

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
            'name' => 'Cusco Tour',
            'description' => 'Desc Cusco',
            'price' => 50.0,
            'image' => 'https://example.com/cusco.jpg',
        ]);

        $response = $this->getJson("/api/tours/{$tour->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $tour->id,
                     'name' => 'Cusco Tour',
                 ]);
    }

    /** @test */
    public function store_creates_a_new_tour()
    {
        $payload = [
            'name' => 'Amazon Adventure',
            'description' => 'Jungle tour',
            'price' => 120.50,
            'image' => 'https://example.com/amazon.jpg',
        ];

        $response = $this->postJson('/api/tours', $payload);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Amazon Adventure']);

        $this->assertDatabaseHas('tours', ['name' => 'Amazon Adventure']);
    }

    /** @test */
    public function update_modifies_existing_tour()
    {
        $tour = Tour::factory()->create(['name' => 'Old Name']);

        $payload = ['name' => 'New Name'];

        $response = $this->putJson("/api/tours/{$tour->id}", $payload);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'New Name']);
    }

    /** @test */
    public function destroy_deletes_tour()
    {
        $tour = Tour::factory()->create();

        $response = $this->deleteJson("/api/tours/{$tour->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tours', ['id' => $tour->id]);
    }
}
