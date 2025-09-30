<?php

namespace App\Http\Controllers;

use App\Models\SessionTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // Adicionar este import
use Carbon\Carbon; // Adicionar este import

class ClickCtaController extends Controller
{
    /**
     * Cria um registro inicial de sessão quando o usuário entra na página
     */
    public function createSession(Request $request)
    {
        // ... (Seu código createSession permanece o mesmo e está correto) ...
        try {
            $validated = $request->validate([
                'uuid' => 'string',
                'initialTime' => 'required|date',
            ]);

            $session = SessionTracker::create([
                'uuid' => $validated['uuid'],
                'initialTime' => Carbon::parse($validated['initialTime'])->setTimezone('America/Sao_Paulo'),
                'clicou' => false,
            ]);
            // ... (Restante da lógica) ...

            return response()->json(['message' => 'Sessão criada com sucesso!', 'data' => $session], 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar sessão inicial.', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao criar sessão inicial.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza um registro existente com informações de clique
     */
    public function store(Request $request)
    {
        // ... (Seu código store permanece o mesmo e está correto) ...
        try {
            $validated = $request->validate([
                'uuid' => 'required|string',
                'data' => 'required|date',
                'info' => 'nullable|string',
            ]);
            
            // ... (Restante da lógica) ...
            $session = SessionTracker::where('uuid', $validated['uuid'])->first();

            if (!$session) {
                return response()->json(['message' => 'Sessão não encontrada.'], 404);
            }

            $session->clicou = true;
            $session->info = json_decode($validated['info'], true);
            $session->save();
            
            return response()->json(['message' => 'Clique registrado com sucesso!', 'data' => $session], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar clique.', ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Erro ao registrar clique.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualiza o tempo de sessão quando o usuário sai da página
     * Lida tanto com requisições HttpClient quanto com sendBeacon (corpo JSON bruto)
     */
    public function storeTimer(Request $request)
    {
        // 1. Tenta extrair dados do corpo JSON bruto (usado pelo sendBeacon)
        $data = json_decode($request->getContent(), true);

        // 2. Se falhar, usa os dados normais da requisição (usado pelo HttpClient)
        if (empty($data)) {
            $data = $request->all();
        }

        // 3. Verifica a conexão com o banco de dados (Apenas para logar e evitar falha silenciosa)
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
             // Retorna 200 (OK) para não bloquear o sendBeacon no navegador
            Log::error('Falha ao conectar com o banco de dados (storeTimer).', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Falha interna, mas requisição recebida.'], 200);
        }

        try {
            // 4. Valida os dados (Usando o helper validator para validar o array $data)
            $validated = validator($data, [
                'uuid' => 'required|string',
                // O ISOString do JS pode ser complexo, 'date' é robusto, 'required' já é suficiente
                'initialTime' => 'required|string', 
                'lastTime' => 'required|string',
                'time' => 'required|string'
            ])->validate();

            // 5. Busca e atualiza a sessão
            $session = SessionTracker::where('uuid', $validated['uuid'])->first();

            if (!$session) {
                Log::warning('Sessão não encontrada no storeTimer.', ['uuid' => $validated['uuid']]);
                return response()->json(['message' => 'Sessão não encontrada.'], 200); // Retorna 200
            }

            $session->lastTime = Carbon::parse($validated['lastTime'])->setTimezone('America/Sao_Paulo');
            $session->time = $validated['time'];
            $session->save();

            Log::info('Tempo de sessão atualizado com sucesso.', ['uuid' => $validated['uuid'], 'time' => $validated['time']]);

            return response()->json(['message' => 'Tempo de sessão registrado com sucesso!', 'data' => $session], 200);

        } catch (ValidationException $e) {
            Log::warning('Falha de validação no storeTimer.', ['errors' => $e->errors(), 'data' => $data]);
            return response()->json(['message' => 'Dados inválidos.', 'errors' => $e->errors()], 200); // Retorna 200
        } 
        catch (\Exception $e) {
            Log::error('Erro desconhecido ao registrar tempo de sessão.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Erro interno ao registrar tempo de sessão.'], 200); // Retorna 200
        }
    }
}