import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-page-obrigado',
  imports: [CommonModule],
  templateUrl: './page-obrigado.component.html',
  styleUrls: ['./page-obrigado.component.css']
})
export class PageObrigadoComponent implements OnInit {
  type: string | null = null;

  constructor(private router: Router, private obrigadoService: ObrigadoService) { }

  ngOnInit() {
    // Primeiro tenta pegar do estado da navegação
    const nav = this.router.getCurrentNavigation();
    this.type = nav?.extras.state?.['type'] || this.obrigadoService.getType();

    // Limpa o serviço para a próxima navegação
    this.obrigadoService.clear();
  }

  goToHome() {
    this.router.navigate(['/'])
    this.obrigadoService.clear();

  }
}
