<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('reservation.product.entrepreneur', 'reservation.user')->get();

        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'image_file'     => 'nullable|image|max:2048',
            'note'           => 'nullable|string',
        ]);

        $imageUrl = null;

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('payments', 'public');
            $imageUrl = asset("storage/$path");
        }

        $payment = Payment::create([
            'reservation_id' => $request->reservation_id,
            'image_url'      => $imageUrl,
            'note'           => $request->note,
            'status'         => 'enviado',
        ]);

        return response()->json($payment->load('reservation.product'), 201);
    }

    public function show($id)
    {
        $payment = Payment::with('reservation.product')->findOrFail($id);
        return response()->json($payment);
    }

    public function confirm($id)
    {
        $payment = Payment::with('reservation.product')->findOrFail($id);

        $payment->update([
            'status' => 'confirmado',
            'confirmed_at' => Carbon::now(),
        ]);

        $reservation = $payment->reservation;
        $reservation->update(['status' => 'confirmada']);

        if ($reservation->product) {
            $reservation->product->decrement('stock', $reservation->quantity);
        }

        return response()->json(['message' => 'Pago confirmado. Reserva actualizada.']);
    }

    public function reject($id)
    {
        $payment = Payment::with('reservation')->findOrFail($id);

        $payment->update([
            'status' => 'rechazado',
            'rejected_at' => Carbon::now(),
        ]);

        $payment->reservation->update(['status' => 'cancelada']);

        return response()->json(['message' => 'Pago rechazado. Reserva cancelada.']);
    }
    public function indexForEntrepreneur()
{
    $user = auth()->user();
    if (!$user || !$user->entrepreneur) {
        return response()->json([], 403);
    }

    $entrepreneurId = $user->entrepreneur->id;

    $payments = Payment::whereHas('reservation.product', function ($q) use ($entrepreneurId) {
        $q->where('entrepreneur_id', $entrepreneurId);
    })->with(['reservation.product', 'reservation.user'])->get();

    return response()->json($payments);
}


}
