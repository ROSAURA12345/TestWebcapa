<?php

namespace Tests\Feature;

use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlaceControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_places()
    {
        Place::factory()->count(3)->create();

        $response = $this->getJson('/api/places');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_show_a_single_place()
    {
        $place = Place::factory()->create();

        $response = $this->getJson("/api/places/{$place->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $place->id,
                     'name' => $place->name,
                 ]);
    }

    /** @test */
    public function it_can_create_a_place_with_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('foto.jpg');

        $data = [
            'name' => 'Montaña Arcoíris',
            'excerpt' => 'Colorida y espectacular',
            'activities' => ['escalada', 'fotografía'],
            'stats' => ['likes' => 1200],
            'image_file' => $file,
            'latitude' => -13.8697,
            'longitude' => -71.1355,
            'category' => 'Montaña'
        ];

        $response = $this->postJson('/api/places', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Montaña Arcoíris']);

        // Verifica que la imagen fue guardada
        Storage::disk('public')->assertExists('places/' . $file->hashName());
    }

    /** @test */
    public function it_can_update_a_place()
    {
        $place = Place::factory()->create([
            'name' => 'Original',
            'excerpt' => 'Texto',
        ]);

        $data = [
            'name' => 'Actualizado',
            'excerpt' => 'Nuevo texto',
            'activities' => ['trekking'],
            'stats' => ['visitas' => 200],
            'category' => 'Naturaleza'
        ];

        $response = $this->putJson("/api/places/{$place->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Actualizado']);
    }

    /** @test */
    public function it_can_delete_a_place()
    {
        $place = Place::factory()->create();

        $response = $this->deleteJson("/api/places/{$place->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }
}
