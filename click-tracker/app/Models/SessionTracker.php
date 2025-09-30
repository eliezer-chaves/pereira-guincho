<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionTracker extends Model
{
    use HasFactory;

    protected $table = 'session_trackers';

    protected $fillable = [
        'uuid',
        'initialTime',
        'lastTime',
        'time',
        'clicou',
        'info',
        'visitor_data',
        'ip_address',
        'country',
        'city',
        'device_type'
    ];

    protected $casts = [
        'initialTime' => 'datetime',
        'lastTime' => 'datetime',
        'clicou' => 'boolean',
        'info' => 'array',
        'visitor_data' => 'array'
    ];

    /**
     * Scope para sessões com clique
     */
    public function scopeWithClicks($query)
    {
        return $query->where('clicou', true);
    }

    /**
     * Scope para sessões por país
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope para sessões por dispositivo
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope para sessões recentes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('initialTime', '>=', now()->subDays($days));
    }

    /**
     * Acessor para dados do IP
     */
    public function getIpDataAttribute()
    {
        return $this->visitor_data['ip_data'] ?? null;
    }

    /**
     * Acessor para dados do navegador
     */
    public function getBrowserDataAttribute()
    {
        return $this->visitor_data['browser_data'] ?? null;
    }

    /**
     * Acessor para dados do frontend
     */
    public function getFrontendDataAttribute()
    {
        return $this->visitor_data['frontend_data'] ?? null;
    }

    /**
     * Acessor para resumo da sessão
     */
    public function getSessionSummaryAttribute()
    {
        return [
            'uuid' => $this->uuid,
            'duration' => $this->time,
            'converted' => $this->clicou,
            'country' => $this->country,
            'city' => $this->city,
            'device' => $this->device_type,
            'initial_time' => $this->initialTime?->format('d/m/Y H:i:s'),
            'last_time' => $this->lastTime?->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Verifica se é um dispositivo mobile
     */
    public function getIsMobileAttribute()
    {
        return $this->device_type === 'mobile';
    }

    /**
     * Verifica se é um dispositivo desktop
     */
    public function getIsDesktopAttribute()
    {
        return $this->device_type === 'desktop';
    }

    /**
     * Retorna o tipo de ação realizada (se houver clique)
     */
    public function getActionTypeAttribute()
    {
        if (!$this->clicou) {
            return null;
        }

        $clickData = $this->visitor_data['click_data'] ?? [];
        return $clickData['type'] ?? null;
    }

    /**
     * Retorna a seção onde ocorreu o clique (se houver clique)
     */
    public function getClickSectionAttribute()
    {
        if (!$this->clicou) {
            return null;
        }

        $clickData = $this->visitor_data['click_data'] ?? [];
        return $clickData['section'] ?? null;
    }

    /**
     * Retorna dados de tela do visitante
     */
    public function getScreenDataAttribute()
    {
        $frontendData = $this->frontend_data;
        
        if (!$frontendData) {
            return null;
        }

        return [
            'resolution' => ($frontendData['screen']['width'] ?? 'N/A') . 'x' . ($frontendData['screen']['height'] ?? 'N/A'),
            'viewport' => ($frontendData['viewport']['width'] ?? 'N/A') . 'x' . ($frontendData['viewport']['height'] ?? 'N/A'),
            'color_depth' => $frontendData['screen']['colorDepth'] ?? 'N/A',
            'pixel_depth' => $frontendData['screen']['pixelDepth'] ?? 'N/A',
        ];
    }

    /**
     * Retorna informações de conexão do visitante
     */
    public function getConnectionDataAttribute()
    {
        $frontendData = $this->frontend_data;
        
        if (!$frontendData || !isset($frontendData['connection'])) {
            return null;
        }

        return [
            'effective_type' => $frontendData['connection']['effectiveType'] ?? 'N/A',
            'downlink' => $frontendData['connection']['downlink'] ?? 'N/A',
            'rtt' => $frontendData['connection']['rtt'] ?? 'N/A',
            'save_data' => $frontendData['connection']['saveData'] ?? 'N/A',
        ];
    }

    /**
     * Retorna informações de hardware do visitante
     */
    public function getHardwareDataAttribute()
    {
        $frontendData = $this->frontend_data;
        
        if (!$frontendData) {
            return null;
        }

        return [
            'device_memory' => $frontendData['hardware']['deviceMemory'] ?? 'N/A',
            'hardware_concurrency' => $frontendData['hardware']['hardwareConcurrency'] ?? 'N/A',
            'max_touch_points' => $frontendData['hardware']['maxTouchPoints'] ?? 'N/A',
        ];
    }

    /**
     * Retorna o user agent formatado
     */
    public function getUserAgentShortAttribute()
    {
        $browserData = $this->browser_data;
        
        if (!$browserData) {
            return 'N/A';
        }

        $browser = $browserData['browser']['name'] ?? 'Unknown';
        $platform = $browserData['platform']['name'] ?? 'Unknown';
        
        return "$browser on $platform";
    }

    /**
     * Retorna a duração em segundos
     */
    public function getDurationInSecondsAttribute()
    {
        if (empty($this->time)) {
            return 0;
        }

        if (str_contains($this->time, ":")) {
            [$h, $m, $s] = array_pad(explode(":", $this->time), 3, 0);
            return ((int) $h * 3600) + ((int) $m * 60) + (int) $s;
        }

        if (str_ends_with($this->time, "s")) {
            return (int) str_replace("s", "", $this->time);
        }

        return (int) $this->time;
    }
}