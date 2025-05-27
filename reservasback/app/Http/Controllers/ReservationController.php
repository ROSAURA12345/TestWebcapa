<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Product;  // Cambié Tour a Product
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Crear una nueva reserva.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    // Mostrar todas las reservas del usuario
    public function userReservations()
        {
            $reservations = auth()->user()
                ->reservations()
                ->with('product.entrepreneur') // <- importante
                ->get();

            return response()->json($reservations);
        }



    // Mostrar todas las reservas de un emprendedor
    public function entrepreneurReservations($entrepreneurId)
    {
        $reservations = Reservation::with('product')->whereHas('product', function ($query) use ($entrepreneurId) {
            $query->where('entrepreneur_id', $entrepreneurId);
        })->get();

        return response()->json($reservations);
    }
    // Crear una nueva reserva
    public function store(Request $request)
    {
        $request->validate([
            'product_id'       => 'required|exists:products,id',
            'quantity'         => 'required|integer|min:1',
            'reservation_date' => 'required|date',
        ]);

        $product = Product::findOrFail($request->product_id);
        $totalPrice = $product->price * $request->quantity;

        $reservation = Reservation::create([
            'product_id'       => $request->product_id,
            'user_id'          => auth()->user()->id,
            'quantity'         => $request->quantity,
            'reservation_date' => $request->reservation_date,
            'total_amount'     => $totalPrice,
            'reservation_code' => uniqid('RES-', true),
        ]);
        $reservation->load('product.entrepreneur');

        return response()->json($reservation, 201);
    }

    /**
     * Obtener una reserva específica.
     */
    public function show($id)
    {
        $reservation = Reservation::with(['product.entrepreneur', 'user'])->findOrFail($id); // Cambié tour a product
        return response()->json($reservation);
    }

    /**
     * Listar todas las reservas.
     */
    public function index()
    {
        $reservations = Reservation::with(['product', 'user'])->get(); // Cambié tour a product
        return response()->json($reservations);
    }

    /**
     * Eliminar una reserva.
     */
    public function destroy($id)
{
    $reservation = Reservation::find($id);

    if (!$reservation) {
        return response()->json(['message' => 'Reserva no encontrada'], 404);
    }

    $user = auth()->user(); // <-- Agrega este log temporal
    \Log::info('Intento de eliminar reserva', [
        'auth_id' => $user?->id,
        'res_user_id' => $reservation->user_id
    ]);

    if ($reservation->user_id !== auth()->id()) {
        return response()->json(['message' => 'No autorizado'], 403);
    }

    $reservation->delete();
    return response()->json(['message' => 'Reserva eliminada correctamente']);
}

}

