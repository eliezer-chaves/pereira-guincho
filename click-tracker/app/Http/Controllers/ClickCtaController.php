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

    // USAR initialTime DO BANCO (já salvo corretamente)
    $horaEntrada = $session->initialTime->format('d/m/Y H:i:s');
    $horaSaida = $lastTime->format('d/m/Y H:i:s');

    // LOG final detalhado
    Log::info("🔚  SESSAO FINALIZADA", [
        '🔑 UUID' => $session->uuid,
        '🆔 Session ID' => $session->id,
        '🎯 Conversão' => $session->clicou ? '✅ SIM' : '❌ NÃO',
        '📊 Tipo de Ação' => $session->clicou ? $emojiTipo : 'Nenhuma',
        '📍 Seção' => $session->clicou ? ($infoClique['section'] ?? 'N/A') : 'N/A',
        '⏰ Entrada' => $horaEntrada, // ← Agora do banco
        '⏰ Saída' => $horaSaida,
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

public function totalResumo()
{
    $sessions = SessionTracker::all();

    if ($sessions->isEmpty()) {
        return response()->json([
            'resumo_sessao' => [
                "📊 Total de Sessões" => 0,
                "🎯 Total de Cliques" => 0,
                "💚 Taxa de Conversão" => "0%",
                "⏱️ Duração Média" => "00:00:00",
                "📞 Ações Realizadas" => [
                    'whatsapp' => 0,
                    'call' => 0,
                    'form' => 0,
                    'email' => 0,
                    'maps-review' => 0,
                ],
                "📍 Seções Mais Cliques" => []
            ]
        ], 200);
    }

    $totalSessoes = $sessions->count();
    $totalCliques = $sessions->where('clicou', true)->count();
    $taxaConversao = $totalSessoes > 0 ? round(($totalCliques / $totalSessoes) * 100, 2) : 0;

    // Calcular duração média (somente sessões com time válido)
    $duracoesSegundos = $sessions->filter(function ($s) {
        return !empty($s->time);
    })->map(function ($s) {
        if (str_contains($s->time, ":")) {
            [$h, $m, $s_] = array_pad(explode(":", $s->time), 3, 0);
            return ((int) $h * 3600) + ((int) $m * 60) + (int) $s_;
        }
        if (str_ends_with($s->time, "s")) {
            return (int) str_replace("s", "", $s->time);
        }
        return (int) $s->time;
    });

    $mediaSegundos = $duracoesSegundos->isNotEmpty() ? $duracoesSegundos->avg() : 0;
    $duracaoMedia = gmdate("H:i:s", (int) $mediaSegundos);

    // Inicializar todos os tipos possíveis (sem floatingWPP)
    $acoes = [
        'whatsapp' => 0,
        'call' => 0,
        'form' => 0,
        'email' => 0,
        'maps-review' => 0,
    ];

    // Contar seções
    $secoes = [];

    foreach ($sessions as $s) {
        if ($s->clicou && is_array($s->info)) {
            $tipo = strtolower($s->info['type'] ?? '');

            // floatingWPP conta como whatsapp
            if ($tipo === 'floatingwpp') {
                $tipo = 'whatsapp';
            }

            if (array_key_exists($tipo, $acoes)) {
                $acoes[$tipo]++;
            }

            $secao = $s->info['section'] ?? 'N/A';
            if (!isset($secoes[$secao])) {
                $secoes[$secao] = 0;
            }
            $secoes[$secao]++;
        }
    }

    return response()->json([
        'resumo_sessao' => [
            "📊 Total de Sessões" => $totalSessoes,
            "🎯 Total de Cliques" => $totalCliques,
            "💚 Taxa de Conversão" => $taxaConversao . "%",
            "⏱️ Duração Média" => $duracaoMedia,
            "📞 Ações Realizadas" => $acoes,
            "📍 Seções Mais Cliques" => $secoes
        ]
    ], 200);
}




}