import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { handleWhatsAppClick, handleCallClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { ClickCtaService } from '../../helpers/tracker.service';

@Component({
  selector: 'app-services',
  imports: [],
  templateUrl: './services.component.html',
  styleUrl: './services.component.css'
})
export class ServicesComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService, private clickCTA: ClickCtaService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
    this.clickCTA.registerClick('whatsapp', 'services-section').subscribe()
  }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService)
    this.clickCTA.registerClick('call', 'services-section').subscribe();
  }
}
