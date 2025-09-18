import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { handleWhatsAppClick, handleCallClick, handleEmailClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { ClickCtaService } from '../../helpers/tracker.service';

@Component({
  selector: 'app-footer',
  imports: [],
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.css'
})
export class FooterComponent {
 company: string = environment.companyName
  cnpj: string = environment.cnpj
  phone: string = environment.phoneNumber
  email: string = environment.email

  constructor(private router: Router, private obrigadoService: ObrigadoService, private clickCTA: ClickCtaService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
    this.clickCTA.registerClick('whatsapp', 'dados-empresarias').subscribe()

  }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
    this.clickCTA.registerClick('call', 'dados-empresarias').subscribe()

  }
  onEmailClick() {
    handleEmailClick(this.router, this.obrigadoService);
        this.clickCTA.registerClick('email', 'dados-empresarias').subscribe()

  }
}
