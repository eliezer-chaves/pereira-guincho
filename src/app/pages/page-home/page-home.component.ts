import { Component, OnDestroy, OnInit } from '@angular/core';
import { DadosEmpresariasComponent } from '../../components/dados-empresarias/dados-empresarias.component';
import { DiferenciaisComponent } from '../../components/diferenciais/diferenciais.component';
import { FloatingButtonComponent } from '../../components/floating-button/floating-button.component';
import { FooterComponent } from '../../components/footer/footer.component';
import { GaleriaComponent } from '../../components/galeria/galeria.component';
import { HeaderComponent } from '../../components/header/header.component';
import { HeroComponent } from '../../components/hero/hero.component';
import { OrcamentosComponent } from '../../components/orcamentos/orcamentos.component';
import { ReviewComponent } from '../../components/review/review.component';
import { ServicesComponent } from '../../components/services/services.component';
import { ClickCtaService } from '../../helpers/tracker.service';
import { v4 as uuidv4 } from 'uuid';

@Component({
  selector: 'app-page-home',
  imports: [
    HeaderComponent,
    HeroComponent,
    ReviewComponent,
    ServicesComponent,
    DiferenciaisComponent,
    OrcamentosComponent,
    GaleriaComponent,
    DadosEmpresariasComponent,
    FooterComponent,
    FloatingButtonComponent
  ],
  templateUrl: './page-home.component.html',
  styleUrl: './page-home.component.css'
})
export class PageHomeComponent implements OnInit, OnDestroy {

  constructor(private trackerService: ClickCtaService) { }

  uuid: string = ''
  initialTime: Date = new Date()
  lastTime: Date = new Date()

  ngOnInit(): void {
    this.initialTime = new Date();

    this.uuid = this.generateRandomId();
    localStorage.setItem('session_uuid', this.uuid);

    // 🔹 Cria o registro inicial no banco de dados
    this.trackerService.createSession(this.uuid, this.initialTime)
      .subscribe({
        next: (response) => {
          //console.log('Sessão criada com sucesso:', response);
        },
        error: (error) => {
          //console.error('Erro ao criar sessão:', error);
        }
      });

  }

  ngOnDestroy(): void {
    this.lastTime = new Date();
    const diffMs = this.lastTime.getTime() - this.initialTime.getTime();

    // Total em segundos
    let totalSeconds = Math.floor(diffMs / 1000);

    const days = Math.floor(totalSeconds / (3600 * 24));
    totalSeconds %= (3600 * 24);

    const hours = Math.floor(totalSeconds / 3600);
    totalSeconds %= 3600;

    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    // Formatar saída
    const formatted =
      (days > 0 ? days + "d " : "") +
      String(hours).padStart(2, "0") + ":" +
      String(minutes).padStart(2, "0") + ":" +
      String(seconds).padStart(2, "0");

    this.trackerService
      .registerTime(this.uuid, this.initialTime, this.lastTime, formatted)
      .subscribe({
        complete: () => {
          // 🔹 Limpa o UUID do localStorage após enviar os dados
          localStorage.removeItem('session_uuid');
        },
        error: (error) => {
          console.error('Erro ao registrar tempo:', error);
          // Limpa mesmo em caso de erro
          localStorage.removeItem('session_uuid');
        }
      });
  }

  generateRandomId(): string {
    const timestamp = new Date().toISOString().replace(/[-:.]/g, '');
    const randomString = uuidv4().replace(/-/g, '');
    return `${timestamp}-${randomString}`;
  }
}