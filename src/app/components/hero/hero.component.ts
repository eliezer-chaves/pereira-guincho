// src/app/components/hero/hero.component.ts
import { Component, inject, ChangeDetectionStrategy } from '@angular/core';
import { handleCallClick, handleWhatsAppClick } from '../../helpers/contact.utils';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { ClickCtaService } from '../../helpers/tracker.service';

@Component({
  selector: 'app-hero',
  templateUrl: './hero.component.html',
  styleUrl: './hero.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class HeroComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService, private clickCTA: ClickCtaService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
    this.clickCTA.registerClick('whatsapp', 'hero-banner').subscribe();


  }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
    this.clickCTA.registerClick('call', 'hero-banner').subscribe();
  }

}