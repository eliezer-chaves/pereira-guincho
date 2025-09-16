import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { handleWhatsAppClick, handleCallClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';

@Component({
  selector: 'app-services',
  imports: [],
  templateUrl: './services.component.html',
  styleUrl: './services.component.css'
})
export class ServicesComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService) { }

  onWhatsappClick() {
    handleWhatsAppClick(this.router, this.obrigadoService);
  }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
  }
}
