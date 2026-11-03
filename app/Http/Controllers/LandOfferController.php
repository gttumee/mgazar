<?php

namespace App\Http\Controllers;

use App\Models\Land;
use Illuminate\Http\Request;

class LandOfferController extends Controller
{
    public function store(Request $request, Land $land)
    {
        abort_unless($land->status === 'active', 404);

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [
            'price.required' => 'Санал болгох үнээ оруулна уу.',
            'price.numeric' => 'Үнэ тоо байх ёстой.',
            'price.min' => 'Үнэ 0-ээс бага байж болохгүй.',
            'phone.required' => 'Холбоо барих утасны дугаараа оруулна уу.',
            'phone.max' => 'Утасны дугаар хэт урт байна.',
            'message.max' => 'Тайлбар 2000 тэмдэгтээс хэтрэхгүй байх ёстой.',
        ]);

        $land->offers()->create([
            'price' => $validated['price'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? null,
            'status' => 'new',
        ]);

        $message = 'Таны үнийн санал илгээгдлээ. Зар эзэмшигч тантай холбогдох болно.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ], 201);
        }

        return back()->with('success', $message);
    }
}
