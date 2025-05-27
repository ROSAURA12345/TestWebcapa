<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\PlaceController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PlaceControllerUnitTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function index_returns_all_places()
    {
        // Alias mock para interceptar llamadas estáticas
        $placeMock = Mockery::mock('alias:App\\Models\\Place');

        // Colección de prueba
        $fakePlaces = collect([(object)['id' => 1, 'name' => 'A']]);

        $placeMock
            ->shouldReceive('orderBy')
            ->once()
            ->with('created_at', 'desc')
            ->andReturnSelf();
        $placeMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($fakePlaces);

        $controller = new PlaceController();
        $response = $controller->index();

        $this->assertEquals(200, $response->status());
        $this->assertEquals($fakePlaces->toJson(), $response->getContent());
    }

    /** @test */
    public function show_returns_the_requested_place()
    {
        $placeMock = Mockery::mock('alias:App\\Models\\Place');
        $fakePlace = new \App\Models\Place(['id' => 42, 'name' => 'Mi Lugar']);

        $placeMock
            ->shouldReceive('findOrFail')
            ->once()
            ->with(42)
            ->andReturn($fakePlace);

        $controller = new PlaceController();
        $response = $controller->show(42);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(json_encode($fakePlace), $response->getContent());
    }

    /** @test */
    public function store_validates_and_creates_place_with_image()
    {
        // Mock Storage para store de archivo
        Storage::shouldReceive('disk')->with('public')->andReturnSelf();
        Storage::shouldReceive('putFileAs')->andReturn('places/fake.jpg');

        $file = UploadedFile::fake()->image('foto.jpg');
        $request = Request::create('/api/places', 'POST', [
            'name'      => 'Test',
            'excerpt'   => 'Excerpt',
            'activities'=> ['a','b'],
            'stats'     => ['x'=>1],
            'latitude'  => 0,
            'longitude' => 0,
            'category'  => 'Cat',
        ]);
        $request->files->set('image_file', $file);

        $placeMock = Mockery::mock('alias:App\\Models\\Place');
        $fakePlace = new \App\Models\Place(array_merge($request->all(), [
            'id'        => 99,
            'image_url' => asset('storage/places/fake.jpg'),
        ]));

        $placeMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($fakePlace);

        $controller = new PlaceController();
        $response = $controller->store($request);

        $this->assertEquals(201, $response->status());
        $this->assertEquals(json_encode($fakePlace), $response->getContent());
    }

    /** @test */
    public function update_validates_and_updates_place()
    {
        $placeMock = Mockery::mock('alias:App\\Models\\Place');
        $existing = Mockery::mock(App\Models\Place::class)->makePartial();

        $placeMock
            ->shouldReceive('findOrFail')
            ->once()
            ->with(5)
            ->andReturn($existing);

        $request = Request::create('/api/places/5', 'PUT', [
            'name'    => 'New',
            'excerpt' => 'New Excerpt',
        ]);

        $existing
            ->shouldReceive('update')
            ->once()
            ->with($request->all());

        $controller = new PlaceController();
        $response = $controller->update($request, 5);

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function destroy_deletes_place_and_returns_204()
    {
        $placeMock = Mockery::mock('alias:App\\Models\\Place');
        $existing = Mockery::mock(App\Models\Place::class)->makePartial();
        $existing->image_url = '/storage/test.jpg';

        $placeMock
            ->shouldReceive('findOrFail')
            ->once()
            ->with(7)
            ->andReturn($existing);

        // Mock Storage para borrar
        Storage::shouldReceive('disk')->with('public')->andReturnSelf();
        Storage::shouldReceive('delete')->once();

        $existing
            ->shouldReceive('delete')
            ->once();

        $controller = new PlaceController();
        $response = $controller->destroy(7);

        $this->assertEquals(204, $response->status());
        $this->assertSame('{}', $response->getContent());

    }
}
