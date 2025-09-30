<?php

namespace App\Http\Controllers;

use App\Models\SessionTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ClickCtaController extends Controller
{
    public function createSession(Request $request)
    {
        $uuid = $request->uuid ?? 'N/A';
        $initialTime = Carbon::parse($request->initialTime ?? now())->setTimezone('America/Sao_Paulo');

        // LOG inicial da sessão - Formatado com emojis
        Log::info("🎬  SESSAO INICIADA", [
            '🔑 UUID' => $uuid,
            '⏰ Hora Entrada' => $initialTime->format('d/m/Y H:i:s'),
            '🌐 IP' => $request->ip(),
            '🖥️ User Agent' => substr($request->userAgent(), 0, 100),
            '📎 Referer' => $request->header('referer') ?? 'N/A',
            '🔄 Status' => 'Sessão ativa - Aguardando ações',
            '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
        ]);

        try {
            $session = SessionTracker::create([
                'uuid' => $uuid,
                'initialTime' => $initialTime,
                'clicou' => false,
            ]);

            // Log::info("✅  SESSAO REGISTRADA NO BD", [
            //     '🔑 UUID' => $session->uuid,
            //     '🆔 Session ID' => $session->id,
            //     '⏰ Initial Time' => $session->initialTime->format('d/m/Y H:i:s'),
            //     '📊 Status' => 'Registrada com sucesso'
            // ]);

            return response()->json(['message' => 'Sessão criada com sucesso!', 'data' => $session], 201);

        } catch (\Exception $e) {
            Log::error("❌  ERRO AO CRIAR SESSAO", [
                '🔑 UUID' => $uuid,
                '💥 Erro' => $e->getMessage(),
                '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
            ]);
            return response()->json(['message' => 'Erro ao criar sessão.', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|string',
            'data' => 'required|date',
            'info' => 'nullable|string',
        ]);

        $session = SessionTracker::where('uuid', $validated['uuid'])->first();
        if (!$session) {
            Log::warning("⚠️  TENTATIVA DE CLIQUE - SESSAO NAO ENCONTRADA", [
                '🔑 UUID' => $validated['uuid'],
                '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
            ]);
            return response()->json(['message' => 'Sessão não encontrada.'], 404);
        }

        $infoClique = json_decode($validated['info'], true) ?? [];

        // Mapeamento correto dos campos baseado na estrutura real do JSON
        Log::info("🎯  CLIQUE DETECTADO", [
            '🔑 UUID' => $session->uuid,
            '🆔 Session ID' => $session->id,
            '⏰ Hora Clique' => Carbon::parse($validated['data'])->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s'),
            '📞 Tipo de Ação' => $infoClique['type'] ?? 'N/A',
            '📍 Seção' => $infoClique['section'] ?? 'N/A',
            '🌐 Origem' => $infoClique['referrer'] === 'direct' ? '📱 Acesso Direto' : ('🔗 ' . ($infoClique['referrer'] ?? 'N/A')),
            '🗣️ Idioma' => $infoClique['language'] ?? 'N/A',
            '🕐 Fuso Horário' => $infoClique['timezone'] ?? 'N/A',
            '📋 User Agent' => substr($infoClique['userAgent'] ?? 'N/A', 0, 80),
            '⏱️ Timestamp Clique' => $infoClique['datetime'] ?? 'N/A'
        ]);

        $session->clicou = true;
        $session->info = $infoClique;
        $session->save();

        // Determinar emoji baseado no tipo de ação
        $tipoAcao = $infoClique['type'] ?? 'desconhecido';
        $emojiTipo = match($tipoAcao) {
            'whatsapp' => '💚',
            'phone' => '📞',
            'email' => '📧',
            'form' => '📝',
            'link' => '🔗',
            default => '🎯'
        };

        // Log::info("✅  CLIQUE REGISTRADO COM SUCESSO", [
        //     '🔑 UUID' => $session->uuid,
        //     '🆔 Session ID' => $session->id,
        //     '🎉 Status' => 'CONVERSÃO REALIZADA',
        //     '📊 Tipo' => $emojiTipo . ' ' . strtoupper($tipoAcao),
        //     '📍 Local' => $infoClique['section'] ?? 'N/A',
        //     '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
        // ]);

        return response()->json(['message' => 'Clique registrado com sucesso!', 'data' => $session], 200);
    }

    public function storeTimer(Request $request)
    {
        $data = json_decode($request->getContent(), true) ?? $request->all();
        $uuid = $data['uuid'] ?? 'N/A';

        $session = SessionTracker::where('uuid', $uuid)->first();
        if (!$session) {
            Log::warning("⚠️  ENCERRAMENTO - SESSAO NAO ENCONTRADA", [
                '🔑 UUID' => $uuid,
                '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
            ]);
            return response()->json(['message' => 'Sessão não encontrada.'], 200);
        }

        $lastTime = Carbon::parse($data['lastTime'])->setTimezone('America/Sao_Paulo');
        $session->lastTime = $lastTime;
        $session->time = $data['time'] ?? '0s';
        $session->save();

        // Recuperar informações do clique para o log final
        $infoClique = $session->info ?? [];
        $tipoAcao = $infoClique['type'] ?? 'desconhecido';
        
        $emojiTipo = match($tipoAcao) {
            'whatsapp' => '💚 WhatsApp',
            'phone' => '📞 Telefone',
            'email' => '📧 Email',
            'form' => '📝 Formulário',
            'link' => '🔗 Link',
            default => '🎯 Desconhecido'
        };

        // LOG final detalhado
        Log::info("🔚  SESSAO FINALIZADA", [
            '🔑 UUID' => $session->uuid,
            '🆔 Session ID' => $session->id,
            '🎯 Conversão' => $session->clicou ? '✅ SIM' : '❌ NÃO',
            '📊 Tipo de Ação' => $session->clicou ? $emojiTipo : 'Nenhuma',
            '📍 Seção' => $session->clicou ? ($infoClique['section'] ?? 'N/A') : 'N/A',
            '⏰ Entrada' => $session->initialTime->format('d/m/Y H:i:s'),
            '⏰ Saída' => $lastTime->format('d/m/Y H:i:s'),
            '⏱️ Duração' => $session->time,
            '📈 Resumo' => [
                'Tempo na Página' => $session->time,
                'Ação Realizada' => $session->clicou ? $tipoAcao : 'Nenhuma',
                'Status Final' => $session->clicou ? 'Sucesso - Conversão' : 'Sem Conversão',
                'Seção do Clique' => $session->clicou ? ($infoClique['section'] ?? 'N/A') : 'N/A'
            ],
            '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
        ]);

        return response()->json(['message' => 'Tempo de sessão registrado com sucesso!', 'data' => $session], 200);
    }
}