import { Component } from '@angular/core';
import { environment } from '../../../environments/environment';
import { Router } from '@angular/router';
import { handleWhatsAppClick, handleCallClick, handleEmailClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';

@Component({
  selector: 'app-dados-empresarias',
  imports: [],
  templateUrl: './dados-empresarias.component.html',
  styleUrl: './dados-empresarias.component.css'
})
export class DadosEmpresariasComponent {
  company: string = environment.companyName
  cnpj: string = environment.cnpj
  phone: string = environment.phoneNumber
  email: string = environment.email
  emailUrl: string = environment.emailUrl

  primaryStreet: string = environment.addressPrimary.street
  primaryNeighborhood: string = environment.addressPrimary.neighborhood
  primaryCity: string = environment.addressPrimary.city
  primaryZipCode: string = environment.addressPrimary.zipCode

  secondaryStreet: string = environment.addressSecondary.street
  secondaryNeighborhood: string = environment.addressSecondary.neighborhood
  secondaryCity: string = environment.addressSecondary.city
  secondaryZipCode: string = environment.addressSecondary.zipCode


  constructor(private router: Router, private obrigadoService: ObrigadoService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
  }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
  }
  onEmailClick() {
    handleEmailClick(this.router, this.obrigadoService);
  }
}
