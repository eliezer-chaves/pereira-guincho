import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class ClickCtaService {

  private apiUrl = 'https://www.pereiraguinchotaubate.com.br/api/clicks-cta';

  constructor(private http: HttpClient) {}

  /**
   * Registra o clique no backend
   * @param type Tipo do clique (whatsapp, call, floatingWPP, form)
   * @param section Seção da página onde o clique ocorreu
   */
  registerClick(
    type: 'whatsapp' | 'call' | 'floatingWPP' | 'form',
    section: string
  ) {
    const now = new Date();

    // Converte para "YYYY-MM-DD HH:MM:SS"
    const datetime = now.getFullYear() + '-' +
                     String(now.getMonth() + 1).padStart(2, '0') + '-' +
                     String(now.getDate()).padStart(2, '0') + ' ' +
                     String(now.getHours()).padStart(2, '0') + ':' +
                     String(now.getMinutes()).padStart(2, '0') + ':' +
                     String(now.getSeconds()).padStart(2, '0');

    const payload = {
      data: datetime, // envia no formato datetime
      info: JSON.stringify({
        type: type,
        section: section,          // adiciona a seção clicada
        datetime: now.toISOString(),
        userAgent: navigator.userAgent,
        language: navigator.language,
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        referrer: document.referrer || 'direct'
      })
    };

    return this.http.post(this.apiUrl, payload);
  }
}
