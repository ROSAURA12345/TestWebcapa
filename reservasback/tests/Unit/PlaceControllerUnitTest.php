<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\PlaceController;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlaceControllerUnitTest extends TestCase
{
    /** @test */
    public function index_returns_all_places()
    {
        // Prepara datos reales en base de datos falsa
        Place::factory()->create(['name' => 'Lugar 1']);
        Place::factory()->create(['name' => 'Lugar 2']);

        $controller = new PlaceController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Lugar 1', $response->getContent());
        $this->assertStringContainsString('Lugar 2', $response->getContent());
    }

    /** @test */
    public function show_returns_the_requested_place()
    {
        $place = Place::factory()->create([
            'name' => 'Machu Picchu'
        ]);

        $controller = new PlaceController();
        $response = $controller->show($place->id);

        $this->assertEquals(200, $response->status());
        $this->assertStringContainsString('Machu Picchu', $response->getContent());
    }

    /** @test */
    public function store_creates_place_with_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('foto.jpg');
        $request = Request::create('/api/places', 'POST', [
            'name'      => 'Test',
            'excerpt'   => 'Un lugar de prueba',
            'activities'=> ['nadar'],
            'stats'     => ['likes' => 20],
            'latitude'  => 1.2345,
            'longitude' => 6.7890,
            'category'  => 'Naturaleza'
        ]);
        $request->files->set('image_file', $file);

        $controller = new PlaceController();
        $response = $controller->store($request);

        $this->assertEquals(201, $response->status());
        Storage::disk('public')->assertExists('places/' . $file->hashName());
    }

    /** @test */
    public function update_modifies_existing_place()
    {
        $place = Place::factory()->create([
            'name' => 'Original',
            'excerpt' => 'Antiguo texto'
        ]);

        $request = Request::create("/api/places/{$place->id}", 'PUT', [
            'name' => 'Actualizado',
            'excerpt' => 'Nuevo texto',
        ]);

        $controller = new PlaceController();
        $response = $controller->update($request, $place->id);

        $this->assertEquals(200, $response->status());
        $this->assertDatabaseHas('places', ['id' => $place->id, 'name' => 'Actualizado']);
    }

    /** @test */
    public function destroy_removes_place_and_returns_204()
    {
        $place = Place::factory()->create();

        $controller = new PlaceController();
        $response = $controller->destroy($place->id);

        $this->assertEquals(204, $response->status());
        $this->assertDatabaseMissing('places', ['id' => $place->id]);
    }
}
