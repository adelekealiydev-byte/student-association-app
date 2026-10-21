<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IdCardController extends Controller
{
    public function show(Request $request)
    {
        $card = $request->user()->idCards()->latest()->firstOrFail();

        if (view()->exists('cards.show')) {
            return view('cards.show', ['card' => $card]); // render qr_code_data as a QR image
        }

        return response()->json(['card' => $card]);
    }
}

