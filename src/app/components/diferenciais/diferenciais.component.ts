import { Component } from '@angular/core';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { Router } from '@angular/router';
import { handleCallClick } from '../../helpers/contact.utils';
import { ClickCtaService } from '../../helpers/tracker.service';

@Component({
  selector: 'app-diferenciais',
  imports: [],
  templateUrl: './diferenciais.component.html',
  styleUrl: './diferenciais.component.css'
})
export class DiferenciaisComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService, private clickCTA: ClickCtaService) { }


  onCallClick() {
    const uuid = localStorage.getItem('session_uuid')

    handleCallClick(this.router, this.obrigadoService)
    this.clickCTA.registerClick('call', 'services-section', uuid).subscribe()
      ;
  }
}