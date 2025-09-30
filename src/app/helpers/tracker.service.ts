import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ClickCtaService {

  // Para desenvolvimento local, use localhost:8000
  // Para produção, use o domínio real
  private baseUrl = 'http://localhost:8000/api'; // 👈 MUDE AQUI
  private apiUrl = `${this.baseUrl}/clicks-cta`;
  private registerTimeUrl = `${this.baseUrl}/clicks-cta-timer`; // URL para registro de tempo

  constructor(private http: HttpClient) { }

  /**
    * Cria um registro inicial de sessão quando o usuário entra na página
    */
  createSession(uuid: string, initialTime: Date) {
    const payload = {
      uuid: uuid,
      initialTime: initialTime
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
    // ... sua lógica de formatação e payload aqui ...

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
        userAgent: navigator.userAgent,
        language: navigator.language,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        referrer: document.referrer || 'direct'
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