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
    /**
     * Coleta dados detalhados do IP e localização
     */
    private function getIpData($ip)
    {
        try {
            // Ignorar IPs locais
            if ($ip === '127.0.0.1' || $ip === 'localhost' || substr($ip, 0, 3) === '10.') {
                return [
                    'ip' => $ip,
                    'country' => 'Local',
                    'city' => 'Local',
                    'isp' => 'Local Network',
                    'service' => 'local'
                ];
            }

            $ipData = [];
            $ipData['ip'] = $ip;

            // Tentar obter hostname
            try {
                $hostname = gethostbyaddr($ip);
                $ipData['hostname'] = $hostname && $hostname !== $ip ? $hostname : 'N/A';
            } catch (\Exception $e) {
                $ipData['hostname'] = 'N/A';
            }

            // Tentar obter dados de geolocalização
            $geoData = $this->getGeoData($ip);
            $ipData = array_merge($ipData, $geoData);

            return $ipData;

        } catch (\Exception $e) {
            Log::warning("🌐 ERRO AO OBTER DADOS DO IP", [
                'ip' => $ip,
                'erro' => $e->getMessage()
            ]);
            return ['ip' => $ip, 'erro' => $e->getMessage()];
        }
    }

    /**
     * Obtém dados de geolocalização do IP
     */
    private function getGeoData($ip)
    {
        // Lista de serviços de geolocalização (gratuitos)
        $services = [
            "http://ip-api.com/json/{$ip}",
            "https://ipapi.co/{$ip}/json/",
        ];

        foreach ($services as $service) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 3, // 3 segundos timeout
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);

                $response = file_get_contents($service, false, $context);
                $data = json_decode($response, true);

                if ($data) {
                    // ip-api.com
                    if (isset($data['status']) && $data['status'] === 'success') {
                        return [
                            'country' => $data['country'] ?? 'N/A',
                            'countryCode' => $data['countryCode'] ?? 'N/A',
                            'region' => $data['region'] ?? 'N/A',
                            'regionName' => $data['regionName'] ?? 'N/A',
                            'city' => $data['city'] ?? 'N/A',
                            'zip' => $data['zip'] ?? 'N/A',
                            'lat' => $data['lat'] ?? 'N/A',
                            'lon' => $data['lon'] ?? 'N/A',
                            'timezone' => $data['timezone'] ?? 'N/A',
                            'isp' => $data['isp'] ?? 'N/A',
                            'org' => $data['org'] ?? 'N/A',
                            'as' => $data['as'] ?? 'N/A',
                            'service' => 'ip-api.com'
                        ];
                    }

                    // ipapi.co
                    if (isset($data['ip'])) {
                        return [
                            'country' => $data['country_name'] ?? 'N/A',
                            'countryCode' => $data['country_code'] ?? 'N/A',
                            'region' => $data['region'] ?? 'N/A',
                            'regionName' => $data['region'] ?? 'N/A',
                            'city' => $data['city'] ?? 'N/A',
                            'zip' => $data['postal'] ?? 'N/A',
                            'lat' => $data['latitude'] ?? 'N/A',
                            'lon' => $data['longitude'] ?? 'N/A',
                            'timezone' => $data['timezone'] ?? 'N/A',
                            'isp' => $data['org'] ?? 'N/A',
                            'org' => $data['org'] ?? 'N/A',
                            'service' => 'ipapi.co'
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::debug("Falha no serviço de geolocalização: {$service}", ['error' => $e->getMessage()]);
                continue;
            }
        }

        return [
            'country' => 'N/A',
            'city' => 'N/A',
            'service' => 'none',
            'error' => 'Não foi possível obter dados de geolocalização'
        ];
    }

    /**
     * Analisa dados do navegador - VERSÃO CORRIGIDA
     */
    private function parseBrowserData($userAgent)
    {
        try {
            $browser = 'Unknown';
            $platform = 'Unknown';
            $isMobile = false;

            // DETECÇÃO MELHORADA DE MOBILE
            $mobileKeywords = [
                'Mobile',
                'Android',
                'iPhone',
                'iPad',
                'iPod',
                'BlackBerry',
                'Windows Phone',
                'webOS',
                'Opera Mini',
                'IEMobile'
            ];

            // Verificar se é mobile pelo user agent
            foreach ($mobileKeywords as $keyword) {
                if (stripos($userAgent, $keyword) !== false) {
                    $isMobile = true;
                    break;
                }
            }

            // Detectar navegador
            if (strpos($userAgent, 'Chrome') !== false) {
                $browser = 'Chrome';
            } elseif (strpos($userAgent, 'Firefox') !== false) {
                $browser = 'Firefox';
            } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
                $browser = 'Safari';
            } elseif (strpos($userAgent, 'Edge') !== false) {
                $browser = 'Edge';
            } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
                $browser = 'Opera';
            }

            // Detectar plataforma - VERSÃO MELHORADA
            if (stripos($userAgent, 'Windows') !== false) {
                $platform = 'Windows';
            } elseif (stripos($userAgent, 'Mac') !== false) {
                $platform = 'Mac';
            } elseif (stripos($userAgent, 'Linux') !== false) {
                $platform = 'Linux';
            } elseif (stripos($userAgent, 'Android') !== false) {
                $platform = 'Android';
                $isMobile = true; // Forçar mobile para Android
            } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
                $platform = 'iOS';
                $isMobile = true; // Forçar mobile para iOS
            } elseif (stripos($userAgent, 'CrOS') !== false) {
                $platform = 'Chrome OS';
            }

            // DETECÇÃO ADICIONAL POR TAMANHO DE TELA (fallback)
            if (!$isMobile) {
                // Se não detectou por user agent, verificar por tamanho de tela
                // Mas isso será feito no createSession usando dados do frontend
            }

            // Detectar se é bot
            $isBot = preg_match('/bot|crawl|slurp|spider|mediapartners/i', $userAgent);

            return [
                'browser' => [
                    'name' => $browser,
                    'family' => $browser,
                    'version' => $this->extractBrowserVersion($userAgent, $browser)
                ],
                'platform' => [
                    'name' => $platform,
                    'family' => $platform,
                    'version' => $this->extractPlatformVersion($userAgent, $platform),
                    'is_mobile' => $isMobile
                ],
                'is_mobile' => $isMobile,
                'is_desktop' => !$isMobile,
                'is_bot' => $isBot,
                'user_agent' => substr($userAgent, 0, 255),
                'detection_method' => 'user_agent'
            ];
        } catch (\Exception $e) {
            return [
                'browser' => ['name' => 'Unknown', 'family' => 'Unknown'],
                'platform' => ['name' => 'Unknown', 'family' => 'Unknown', 'is_mobile' => false],
                'is_mobile' => false,
                'is_desktop' => true,
                'is_bot' => false,
                'error' => $e->getMessage(),
                'detection_method' => 'error'
            ];
        }
    }

    /**
     * Extrai versão do navegador
     */
    private function extractBrowserVersion($userAgent, $browser)
    {
        $pattern = '';
        switch ($browser) {
            case 'Chrome':
                $pattern = '/Chrome\/([0-9.]+)/';
                break;
            case 'Firefox':
                $pattern = '/Firefox\/([0-9.]+)/';
                break;
            case 'Safari':
                $pattern = '/Version\/([0-9.]+)/';
                break;
            case 'Edge':
                $pattern = '/Edge\/([0-9.]+)/';
                break;
            case 'Opera':
                $pattern = '/(Opera|OPR)\/([0-9.]+)/';
                break;
        }

        if ($pattern && preg_match($pattern, $userAgent, $matches)) {
            return $matches[1] ?? $matches[2] ?? 'Unknown';
        }

        return 'Unknown';
    }

    /**
     * Extrai versão da plataforma
     */
    private function extractPlatformVersion($userAgent, $platform)
    {
        $pattern = '';
        switch ($platform) {
            case 'Android':
                $pattern = '/Android ([0-9.]+)/';
                break;
            case 'iOS':
                $pattern = '/OS ([0-9_]+)/';
                break;
            case 'Windows':
                $pattern = '/Windows NT ([0-9.]+)/';
                break;
            case 'Mac':
                $pattern = '/Mac OS X ([0-9_]+)/';
                break;
        }

        if ($pattern && preg_match($pattern, $userAgent, $matches)) {
            return str_replace('_', '.', $matches[1]);
        }

        return 'Unknown';
    }

    public function createSession(Request $request)
    {
        try {
            $uuid = $request->uuid ?? 'N/A';
            $initialTime = Carbon::parse($request->initialTime ?? now())->setTimezone('America/Sao_Paulo');

            // Coletar dados do IP
            $ip = $request->ip();
            $ipData = $this->getIpData($ip);

            // Coletar dados do navegador
            $userAgent = $request->userAgent() ?? 'N/A';
            $browserData = $this->parseBrowserData($userAgent);

            // Dados do visitante do frontend
            $visitorData = $request->visitor_data ?? [];

            // 🔧 DETECÇÃO HÍBRIDA DE DISPOSITIVO (User Agent + Dados de Tela)
            $finalDeviceType = $this->determineDeviceType($browserData, $visitorData);

            // Combinar todos os dados
            $completeVisitorData = [
                'ip_data' => $ipData,
                'browser_data' => $browserData,
                'frontend_data' => $visitorData,
                'device_detection' => [
                    'user_agent_method' => $browserData['is_mobile'] ? 'mobile' : 'desktop',
                    'screen_method' => $this->detectByScreenSize($visitorData),
                    'final_result' => $finalDeviceType,
                    'confidence' => 'high'
                ],
                'headers' => [
                    'accept_language' => $request->header('accept-language'),
                    'accept_encoding' => $request->header('accept-encoding'),
                    'user_agent' => $userAgent,
                    'referer' => $request->header('referer'),
                    'host' => $request->header('host'),
                    'connection' => $request->header('connection'),
                    'cache_control' => $request->header('cache-control')
                ],
                'collected_at' => now()->setTimezone('America/Sao_Paulo')->toISOString()
            ];

            // LOG inicial detalhado
            Log::info("🎬  SESSAO INICIADA - DADOS COMPLETOS", [
                '🔑 UUID' => $uuid,
                '⏰ Hora Entrada' => $initialTime->format('d/m/Y H:i:s'),

                '🌐 DADOS IP' => [
                    'IP' => $ipData['ip'] ?? 'N/A',
                    'País' => $ipData['country'] ?? 'N/A',
                    'Cidade' => $ipData['city'] ?? 'N/A',
                    'ISP' => $ipData['isp'] ?? 'N/A'
                ],

                '🖥️ DADOS NAVEGADOR' => [
                    'Navegador' => $browserData['browser']['name'] ?? 'N/A',
                    'Versão' => $browserData['browser']['version'] ?? 'N/A',
                    'Plataforma' => $browserData['platform']['name'] ?? 'N/A',
                    'Dispositivo' => $finalDeviceType,
                    'User Agent Mobile' => $browserData['is_mobile'] ? 'Sim' : 'Não',
                    'Método Detecção' => $browserData['detection_method'] ?? 'N/A'
                ],

                '📱 DADOS TELA' => [
                    'Resolução' => ($visitorData['screen']['width'] ?? 'N/A') . 'x' . ($visitorData['screen']['height'] ?? 'N/A'),
                    'Viewport' => ($visitorData['viewport']['width'] ?? 'N/A') . 'x' . ($visitorData['viewport']['height'] ?? 'N/A'),
                    'Mobile por Tela' => $this->detectByScreenSize($visitorData)
                ],

                '📊 STATUS' => 'Sessão ativa - Aguardando ações',
                '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
            ]);

            $session = SessionTracker::create([
                'uuid' => $uuid,
                'initialTime' => $initialTime,
                'clicou' => false,
                'visitor_data' => $completeVisitorData,
                'ip_address' => $ipData['ip'] ?? $ip,
                'country' => $ipData['country'] ?? 'N/A',
                'city' => $ipData['city'] ?? 'N/A',
                'device_type' => $finalDeviceType // 🔧 USAR A DETECÇÃO HÍBRIDA
            ]);

            return response()->json([
                'message' => 'Sessão criada com sucesso!',
                'data' => $session,
                'collected_data' => [
                    'ip_info' => $ipData,
                    'browser_info' => $browserData,
                    'device_type' => $finalDeviceType,
                    'detection_method' => $completeVisitorData['device_detection']
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error("❌  ERRO AO CRIAR SESSAO", [
                '🔑 UUID' => $request->uuid ?? 'N/A',
                '💥 Erro' => $e->getMessage(),
                '📅 Timestamp' => now()->setTimezone('America/Sao_Paulo')->format('d/m/Y H:i:s')
            ]);
            return response()->json(['message' => 'Erro ao criar sessão.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Determina o tipo de dispositivo usando detecção híbrida
     */
    private function determineDeviceType($browserData, $visitorData)
    {
        $userAgentMobile = $browserData['is_mobile'] ?? false;
        $screenMobile = $this->detectByScreenSize($visitorData) === 'mobile';

        // Se ambos os métodos concordam
        if ($userAgentMobile && $screenMobile) {
            return 'mobile';
        }
        if (!$userAgentMobile && !$screenMobile) {
            return 'desktop';
        }

        // Em caso de conflito, priorizar User Agent + dados adicionais
        if ($userAgentMobile) {
            // User Agent diz que é mobile - verificar se há indicadores fortes
            $screenWidth = $visitorData['screen']['width'] ?? 0;
            $hasTouch = $visitorData['hardware']['maxTouchPoints'] ?? 0 > 1;

            if ($screenWidth <= 768 || $hasTouch) {
                return 'mobile';
            }
        }

        // User Agent diz desktop mas tela pequena - pode ser tablet/mobile em modo desktop
        $screenWidth = $visitorData['screen']['width'] ?? 0;
        if ($screenWidth <= 1024) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Detecta dispositivo por tamanho de tela
     */
    private function detectByScreenSize($visitorData)
    {
        $screenWidth = $visitorData['screen']['width'] ?? 0;
        $viewportWidth = $visitorData['viewport']['width'] ?? 0;

        $width = max($screenWidth, $viewportWidth);

        if ($width === 0)
            return 'unknown';

        if ($width <= 768)
            return 'mobile';
        if ($width <= 1024)
            return 'tablet';
        return 'desktop';
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
            '🌐 Origem' => $infoClique['visitor_data']['session']['referrer'] === 'direct' ? '📱 Acesso Direto' : ('🔗 ' . ($infoClique['visitor_data']['session']['referrer'] ?? 'N/A')),
            '🗣️ Idioma' => $infoClique['visitor_data']['language'] ?? 'N/A',
            '🕐 Fuso Horário' => $infoClique['visitor_data']['timezone'] ?? 'N/A',
            '📋 User Agent' => substr($infoClique['visitor_data']['userAgent'] ?? 'N/A', 0, 80),
            '⏱️ Timestamp Clique' => $infoClique['datetime'] ?? 'N/A'
        ]);

        $session->clicou = true;

        // Atualizar os dados do visitante com informações do clique
        $currentVisitorData = $session->visitor_data ?? [];
        $currentVisitorData['click_data'] = $infoClique;
        $session->visitor_data = $currentVisitorData;

        $session->save();

        // Determinar emoji baseado no tipo de ação
        $tipoAcao = $infoClique['type'] ?? 'desconhecido';
        $emojiTipo = match ($tipoAcao) {
            'whatsapp' => '💚',
            'call' => '📞',
            'floatingWPP' => '💚',
            'email' => '📧',
            'form' => '📝',
            'maps-review' => '⭐',
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
        $infoClique = $session->visitor_data['click_data'] ?? [];
        $tipoAcao = $infoClique['type'] ?? 'desconhecido';

        $emojiTipo = match ($tipoAcao) {
            'whatsapp' => '💚 WhatsApp',
            'call' => '📞 Telefone',
            'floatingWPP' => '💚 WhatsApp Flutuante',
            'email' => '📧 Email',
            'form' => '📝 Formulário',
            'maps-review' => '⭐ Avaliação Maps',
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
            '⏰ Entrada' => $horaEntrada,
            '⏰ Saída' => $horaSaida,
            '⏱️ Duração' => $session->time,
            '🌐 Localização' => $session->city . ', ' . $session->country,
            '📱 Dispositivo' => $session->device_type,
            '📈 Resumo' => [
                'Tempo na Página' => $session->time,
                'Ação Realizada' => $session->clicou ? $tipoAcao : 'Nenhuma',
                'Status Final' => $session->clicou ? 'Sucesso - Conversão' : 'Sem Conversão',
                'Seção do Clique' => $session->clicou ? ($infoClique['section'] ?? 'N/A') : 'N/A',
                'Localização' => $session->city . ', ' . $session->country,
                'Dispositivo' => $session->device_type
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
                    "📍 Seções Mais Cliques" => [],
                    "🌐 Países" => [],
                    "📱 Dispositivos" => [],
                    "🖥️ Navegadores" => [],
                    "🕐 Horários de Pico" => [],
                    "📶 Tipos de Conexão" => [],
                    "📏 Resoluções de Tela" => [],
                    "🗣️ Idiomas" => [],
                    "🔗 Fontes de Tráfego" => []
                ]
            ], 200);
        }

        $totalSessoes = $sessions->count();
        $totalCliques = $sessions->where('clicou', true)->count();
        $taxaConversao = $totalSessoes > 0 ? round(($totalCliques / $totalSessoes) * 100, 2) : 0;

        // Calcular duração média
        $duracoesSegundos = $sessions->filter(function ($s) {
            return !empty($s->time);
        })->map(function ($s) {
            return $s->duration_in_seconds;
        });

        $mediaSegundos = $duracoesSegundos->isNotEmpty() ? $duracoesSegundos->avg() : 0;
        $duracaoMedia = gmdate("H:i:s", (int) $mediaSegundos);

        // Inicializar arrays para contagem
        $acoes = [
            'whatsapp' => 0,
            'call' => 0,
            'form' => 0,
            'email' => 0,
            'maps-review' => 0,
        ];

        $secoes = [];
        $paises = [];
        $dispositivos = [];
        $navegadores = [];
        $horarios = [];
        $conexoes = [];
        $resolucoes = [];
        $idiomas = [];
        $fontes = [];
        $cidades = [];
        $plataformas = [];
        $taxasConversaoPorPais = [];
        $temposMediosPorDispositivo = [];

        foreach ($sessions as $s) {
            // Contar países
            $pais = $s->country !== 'N/A' ? $s->country : 'Desconhecido';
            if (!isset($paises[$pais])) {
                $paises[$pais] = 0;
                $taxasConversaoPorPais[$pais] = ['total' => 0, 'cliques' => 0];
            }
            $paises[$pais]++;
            $taxasConversaoPorPais[$pais]['total']++;
            if ($s->clicou) {
                $taxasConversaoPorPais[$pais]['cliques']++;
            }

            // Contar cidades
            $cidade = $s->city !== 'N/A' ? $s->city : 'Desconhecido';
            if (!isset($cidades[$cidade])) {
                $cidades[$cidade] = 0;
            }
            $cidades[$cidade]++;

            // Contar dispositivos
            $dispositivo = $s->device_type !== 'N/A' ? $s->device_type : 'Desconhecido';
            if (!isset($dispositivos[$dispositivo])) {
                $dispositivos[$dispositivo] = 0;
                $temposMediosPorDispositivo[$dispositivo] = [];
            }
            $dispositivos[$dispositivo]++;
            if ($s->duration_in_seconds > 0) {
                $temposMediosPorDispositivo[$dispositivo][] = $s->duration_in_seconds;
            }

            // Contar navegadores
            $navegador = $s->browser_data['browser']['name'] ?? 'Desconhecido';
            if (!isset($navegadores[$navegador])) {
                $navegadores[$navegador] = 0;
            }
            $navegadores[$navegador]++;

            // Contar plataformas
            $plataforma = $s->browser_data['platform']['name'] ?? 'Desconhecido';
            if (!isset($plataformas[$plataforma])) {
                $plataformas[$plataforma] = 0;
            }
            $plataformas[$plataforma]++;

            // Contar horários (por hora do dia)
            $hora = $s->initialTime->hour;
            $periodo = $this->getPeriodoDia($hora);
            if (!isset($horarios[$periodo])) {
                $horarios[$periodo] = 0;
            }
            $horarios[$periodo]++;

            // Contar tipos de conexão
            $conexao = $s->connection_data['effective_type'] ?? 'Desconhecido';
            if (!isset($conexoes[$conexao])) {
                $conexoes[$conexao] = 0;
            }
            $conexoes[$conexao]++;

            // Contar resoluções de tela
            $resolucao = $s->screen_data['resolution'] ?? 'Desconhecido';
            if (!isset($resolucoes[$resolucao])) {
                $resolucoes[$resolucao] = 0;
            }
            $resolucoes[$resolucao]++;

            // Contar idiomas
            $idioma = $s->frontend_data['language'] ?? 'Desconhecido';
            if (!isset($idiomas[$idioma])) {
                $idiomas[$idioma] = 0;
            }
            $idiomas[$idioma]++;

            // Contar fontes de tráfego
            $fonte = $s->frontend_data['session']['referrer'] ?? 'direct';
            if ($fonte === 'direct') {
                $fonteFormatada = 'Acesso Direto';
            } else {
                $fonteFormatada = parse_url($fonte, PHP_URL_HOST) ?? 'Referência Externa';
            }
            if (!isset($fontes[$fonteFormatada])) {
                $fontes[$fonteFormatada] = 0;
            }
            $fontes[$fonteFormatada]++;

            // Processar cliques
            if ($s->clicou && is_array($s->visitor_data)) {
                $clickData = $s->visitor_data['click_data'] ?? [];
                $tipo = strtolower($clickData['type'] ?? '');

                // floatingWPP conta como whatsapp
                if ($tipo === 'floatingwpp') {
                    $tipo = 'whatsapp';
                }

                if (array_key_exists($tipo, $acoes)) {
                    $acoes[$tipo]++;
                }

                $secao = $clickData['section'] ?? 'N/A';
                if (!isset($secoes[$secao])) {
                    $secoes[$secao] = 0;
                }
                $secoes[$secao]++;
            }
        }

        // Calcular taxas de conversão por país
        $taxasConversaoFormatadas = [];
        foreach ($taxasConversaoPorPais as $pais => $dados) {
            $taxa = $dados['total'] > 0 ? round(($dados['cliques'] / $dados['total']) * 100, 2) : 0;
            $taxasConversaoFormatadas[$pais] = $taxa . '%';
        }

        // Calcular tempos médios por dispositivo
        $temposMediosFormatados = [];
        foreach ($temposMediosPorDispositivo as $dispositivo => $tempos) {
            if (!empty($tempos)) {
                $media = array_sum($tempos) / count($tempos);
                $temposMediosFormatados[$dispositivo] = gmdate("H:i:s", (int) $media);
            } else {
                $temposMediosFormatados[$dispositivo] = "00:00:00";
            }
        }

        // Ordenar arrays por quantidade (maior para menor)
        arsort($paises);
        arsort($dispositivos);
        arsort($navegadores);
        arsort($horarios);
        arsort($conexoes);
        arsort($resolucoes);
        arsort($idiomas);
        arsort($fontes);
        arsort($cidades);
        arsort($plataformas);
        arsort($secoes);

        // Estatísticas adicionais
        $sessoesMobile = $sessions->where('device_type', 'mobile')->count();
        $sessoesDesktop = $sessions->where('device_type', 'desktop')->count();
        $taxaConversaoMobile = $sessoesMobile > 0 ?
            round(($sessions->where('device_type', 'mobile')->where('clicou', true)->count() / $sessoesMobile) * 100, 2) : 0;
        $taxaConversaoDesktop = $sessoesDesktop > 0 ?
            round(($sessions->where('device_type', 'desktop')->where('clicou', true)->count() / $sessoesDesktop) * 100, 2) : 0;

        // Top performers
        $paisMaiorConversao = '';
        $maiorTaxaConversao = 0;
        foreach ($taxasConversaoFormatadas as $pais => $taxa) {
            $taxaNum = (float) str_replace('%', '', $taxa);
            if ($taxaNum > $maiorTaxaConversao) {
                $maiorTaxaConversao = $taxaNum;
                $paisMaiorConversao = $pais;
            }
        }

        return response()->json([
            'resumo_sessao' => [
                // Dados básicos
                "📊 Total de Sessões" => $totalSessoes,
                "🎯 Total de Cliques" => $totalCliques,
                "💚 Taxa de Conversão Geral" => $taxaConversao . "%",
                "⏱️ Duração Média" => $duracaoMedia,

                // Dispositivos
                "📱 Dispositivos" => $dispositivos,
                "📱 Sessões Mobile" => $sessoesMobile,
                "🖥️ Sessões Desktop" => $sessoesDesktop,
                "📊 Taxa Conversão Mobile" => $taxaConversaoMobile . "%",
                "📊 Taxa Conversão Desktop" => $taxaConversaoDesktop . "%",
                "⏱️ Tempo Médio por Dispositivo" => $temposMediosFormatados,

                // Localização
                "🌐 Países" => $paises,
                "🏙️ Cidades" => array_slice($cidades, 0, 10), // Top 10 cidades
                "📈 Taxa de Conversão por País" => $taxasConversaoFormatadas,
                "🏆 País com Maior Conversão" => $paisMaiorConversao . " (" . $maiorTaxaConversao . "%)",

                // Navegadores e Plataformas
                "🖥️ Navegadores" => $navegadores,
                "💻 Plataformas" => $plataformas,

                // Ações e Seções
                "📞 Ações Realizadas" => $acoes,
                "📍 Seções Mais Cliques" => array_slice($secoes, 0, 10), // Top 10 seções

                // Comportamento do Usuário
                "🕐 Horários de Pico" => $horarios,
                "📶 Tipos de Conexão" => $conexoes,
                "📏 Resoluções de Tela" => array_slice($resolucoes, 0, 10), // Top 10 resoluções
                "🗣️ Idiomas" => array_slice($idiomas, 0, 10), // Top 10 idiomas
                "🔗 Fontes de Tráfego" => array_slice($fontes, 0, 10), // Top 10 fontes

                // Estatísticas de Performance
                "📈 Sessões por Dispositivo" => [
                    'mobile' => $sessoesMobile,
                    'desktop' => $sessoesDesktop,
                    'ratio_mobile_desktop' => $sessoesDesktop > 0 ? round($sessoesMobile / $sessoesDesktop, 2) : 0
                ],
                "🎯 Eficiência por Plataforma" => [
                    'plataforma_mais_popular' => array_key_first($plataformas) ?? 'N/A',
                    'navegador_mais_popular' => array_key_first($navegadores) ?? 'N/A',
                    'resolucao_mais_comum' => array_key_first($resolucoes) ?? 'N/A'
                ],

                // Métricas de Engajamento
                "📊 Métricas de Engajamento" => [
                    'sessoes_longas' => $sessions->where('duration_in_seconds', '>', 300)->count(), // +5min
                    'sessoes_medias' => $sessions->whereBetween('duration_in_seconds', [60, 300])->count(), // 1-5min
                    'sessoes_curtas' => $sessions->where('duration_in_seconds', '<', 60)->count(), // -1min
                    'taxa_retencao' => round(($sessions->where('duration_in_seconds', '>', 30)->count() / $totalSessoes) * 100, 2) . '%'
                ]
            ]
        ], 200);
    }

    /**
     * Retorna o período do dia baseado na hora
     */
    private function getPeriodoDia($hora)
    {
        if ($hora >= 5 && $hora < 12) {
            return 'Manhã (05:00-11:59)';
        } elseif ($hora >= 12 && $hora < 18) {
            return 'Tarde (12:00-17:59)';
        } elseif ($hora >= 18 && $hora < 23) {
            return 'Noite (18:00-22:59)';
        } else {
            return 'Madrugada (23:00-04:59)';
        }
    }

    /**
     * Novo método para relatório detalhado por período
     */
    public function relatorioDetalhado(Request $request)
    {
        $dias = $request->get('dias', 30);

        $sessoes = SessionTracker::where('initialTime', '>=', now()->subDays($dias))->get();

        if ($sessoes->isEmpty()) {
            return response()->json([
                'periodo' => $dias . ' dias',
                'mensagem' => 'Nenhuma sessão encontrada para o período selecionado.'
            ], 200);
        }

        // Agrupar por dia para gráfico temporal
        $sessoesPorDia = [];
        $cliquesPorDia = [];

        for ($i = $dias - 1; $i >= 0; $i--) {
            $data = now()->subDays($i)->format('Y-m-d');
            $sessoesDia = $sessoes->filter(function ($s) use ($data) {
                return $s->initialTime->format('Y-m-d') === $data;
            });

            $sessoesPorDia[$data] = $sessoesDia->count();
            $cliquesPorDia[$data] = $sessoesDia->where('clicou', true)->count();
        }

        return response()->json([
            'relatorio_periodo' => [
                'periodo_analise' => $dias . ' dias',
                'data_inicio' => now()->subDays($dias)->format('d/m/Y'),
                'data_fim' => now()->format('d/m/Y'),
                'evolucao_diaria' => [
                    'sessoes' => $sessoesPorDia,
                    'cliques' => $cliquesPorDia
                ],
                'resumo_periodo' => $this->totalResumo()->getData()->resumo_sessao
            ]
        ], 200);
    }
}