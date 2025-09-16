import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { environment } from '../../../environments/environment';
import { handleWhatsAppClick, handleCallClick, handleEmailClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';

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

  constructor(private router: Router, private obrigadoService: ObrigadoService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
  }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
  }

  onEmailClick(){
    handleEmailClick(this.router, this.obrigadoService);
  }
}
