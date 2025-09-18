import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { handleWhatsAppClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { ClickCtaService } from '../../helpers/tracker.service';

@Component({
  selector: 'app-floating-button',
  imports: [],
  templateUrl: './floating-button.component.html',
  styleUrl: './floating-button.component.css'
})
export class FloatingButtonComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService, private clickCta: ClickCtaService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
    this.clickCta.registerClick('floatingWPP', 'floating')
  }
}
