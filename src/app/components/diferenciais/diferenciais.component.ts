import { Component } from '@angular/core';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { Router } from '@angular/router';
import { handleCallClick } from '../../helpers/contact.utils';

@Component({
  selector: 'app-diferenciais',
  imports: [],
  templateUrl: './diferenciais.component.html',
  styleUrl: './diferenciais.component.css'
})
export class DiferenciaisComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService) { }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
  }
}