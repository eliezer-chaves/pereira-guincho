<?php

namespace App\Http\Controllers;

use App\Models\ClickCta;
use Illuminate\Http\Request;

class ClickCtaController extends Controller
{
    /**
     * Salvar um clique no banco.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|date', // aceita datetime
            'info' => 'nullable|string',
        ]);

        $click = ClickCta::create($validated);

        return response()->json([
            'message' => 'Clique registrado com sucesso!',
            'data' => $click
        ], 201);
    }
}
