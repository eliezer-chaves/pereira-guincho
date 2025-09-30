import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ClickCtaService {

  // Para desenvolvimento local, use localhost:8000
  // Para produção, use o domínio real
  private baseUrl = 'https://www.pereiraguinchotaubate.com.br/api'; // 👈 MUDE AQUI
  private apiUrl = `${this.baseUrl}/clicks-cta`;
  private registerTimeUrl = `${this.baseUrl}/clicks-cta-timer`; // URL para registro de tempo

  constructor(private http: HttpClient) { }

  /**
   * Coleta dados completos do visitante
   */
  private collectVisitorData() {
    const screen = window.screen;
    const connection = (navigator as any).connection;
    const deviceMemory = (navigator as any).deviceMemory;
    const hardwareConcurrency = navigator.hardwareConcurrency;

    return {
      // Dados do navegador
      userAgent: navigator.userAgent,
      language: navigator.language,
      languages: navigator.languages,
      timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,

      // Dados de tela
      screen: {
        width: screen.width,
        height: screen.height,
        colorDepth: screen.colorDepth,
        pixelDepth: screen.pixelDepth,
        availWidth: screen.availWidth,
        availHeight: screen.availHeight
      },

      // Dados do viewport
      viewport: {
        width: window.innerWidth,
        height: window.innerHeight
      },

      // Dados de performance
      performance: {
        memory: (performance as any).memory ? {
          usedJSHeapSize: (performance as any).memory.usedJSHeapSize,
          totalJSHeapSize: (performance as any).memory.totalJSHeapSize,
          jsHeapSizeLimit: (performance as any).memory.jsHeapSizeLimit
        } : null,
        timing: performance.timing ? {
          navigationStart: performance.timing.navigationStart,
          loadEventEnd: performance.timing.loadEventEnd
        } : null
      },

      // Dados de conexão
      connection: connection ? {
        effectiveType: connection.effectiveType,
        downlink: connection.downlink,
        rtt: connection.rtt,
        saveData: connection.saveData
      } : null,

      // Dados de hardware
      hardware: {
        deviceMemory: deviceMemory,
        hardwareConcurrency: hardwareConcurrency,
        maxTouchPoints: navigator.maxTouchPoints
      },

      // Dados do dispositivo
      device: {
        platform: navigator.platform,
        vendor: navigator.vendor,
        cookieEnabled: navigator.cookieEnabled,
        doNotTrack: navigator.doNotTrack,
        pdfViewerEnabled: navigator.pdfViewerEnabled
      },

      // Dados da sessão
      session: {
        referrer: document.referrer || 'direct',
        url: window.location.href,
        hostname: window.location.hostname,
        pathname: window.location.pathname,
        search: window.location.search,
        hash: window.location.hash
      },

      // Dados de geolocalização (se disponível)
      geolocation: {
        // Será preenchido posteriormente se o usuário permitir
      },

      // Timestamp
      timestamp: new Date().toISOString()
    };
  }


  /**
   * Cria um registro inicial de sessão com dados completos do visitante
   */
  createSession(uuid: string, initialTime: Date) {
    const visitorData = this.collectVisitorData();

    const payload = {
      uuid: uuid,
      initialTime: initialTime,
      visitor_data: visitorData,
      ip_data: {} // Será preenchido no backend
    };

    return this.http.post(`${this.baseUrl}/create-session`, payload);
  }

  /**
  * Registra o clique no backend
  */
  registerClick(
    type: 'whatsapp' | 'call' | 'floatingWPP' | 'form' | 'email' | 'maps-review',
    section: string,
    uuid: any
  ) {
    const now = new Date();
    const visitorData = this.collectVisitorData();

    const datetime = now.getFullYear() + '-' +
      String(now.getMonth() + 1).padStart(2, '0') + '-' +
      String(now.getDate()).padStart(2, '0') + ' ' +
      String(now.getHours()).padStart(2, '0') + ':' +
      String(now.getMinutes()).padStart(2, '0') + ':' +
      String(now.getSeconds()).padStart(2, '0');

    const payload = {
      uuid: uuid,
      data: datetime,
      info: JSON.stringify({
        type: type,
        section: section,
        datetime: now.toISOString(),
        visitor_data: visitorData
      })
    };

    return this.http.post(this.apiUrl, payload);
  }

  /**
   * Atualiza o tempo da sessão usando HttpClient (para navegação interna do Angular)
   */
  registerTime(
    uuid: string,
    initialTime: Date,
    lastTime: Date,
    time: string
  ) {
    const payload = {
      uuid: uuid,
      initialTime: initialTime.toISOString(), // Use ISOString para garantir o formato correto
      lastTime: lastTime.toISOString(),
      time: time
    };

    return this.http.post(this.registerTimeUrl, payload);
  }

  /**
   * Atualiza o tempo da sessão usando sendBeacon (para fechamento de aba/janela)
   * @param uuid UUID da sessão
   * @param initialTime Horário inicial
   * @param lastTime Horário final
   * @param time Tempo formatado
   */
  registerTimeWithBeacon(
    uuid: string,
    initialTime: Date,
    lastTime: Date,
    time: string
  ): void {
    const payload = {
      uuid: uuid,
      initialTime: initialTime.toISOString(),
      lastTime: lastTime.toISOString(),
      time: time
    };

    // sendBeacon exige que os dados sejam Blob, FormData ou URLSearchParams
    const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });

    // O navegador garante que esta requisição será enviada.
    navigator.sendBeacon(this.registerTimeUrl, blob);

    // Removemos o UUID do localStorage imediatamente, já que o sendBeacon não tem callback.
    localStorage.removeItem('session_uuid');
  }
}