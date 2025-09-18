<?php

namespace App\Http\Controllers;

use App\Models\ClickCta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // Importa o Log

class ClickCtaController extends Controller
{
    /**
     * Salvar um clique no banco.
     */
    public function store(Request $request)
    {
        try {
            // Testa conexão com o banco
            DB::connection()->getPdo();
            Log::info('Conexão com o banco de dados estabelecida com sucesso.');
        } catch (\Exception $e) {
            Log::error('Falha ao conectar com o banco de dados.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Falha ao conectar com o banco de dados.',
                'error' => $e->getMessage(),
            ], 500);
        }

        try {
            // Validar os dados recebidos
            $validated = $request->validate([
                'data' => 'required|date', // aceita datetime
                'info' => 'nullable|string',
            ]);

            // Tenta criar o registro
            $click = ClickCta::create($validated);

            // Log de sucesso
            Log::info('Clique registrado com sucesso.', [
                'data' => $validated['data'] ?? null,
                'info' => $validated['info'] ?? null,
            ]);

            return response()->json([
                'message' => 'Clique registrado com sucesso!',
                'data' => $click
            ], 201);

        } catch (\Exception $e) {
            // Log de erro
            Log::error('Erro ao registrar clique no banco de dados.', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erro ao registrar clique.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
