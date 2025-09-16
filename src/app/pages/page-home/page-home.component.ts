import { Component } from '@angular/core';
import { DadosEmpresariasComponent } from '../../components/dados-empresarias/dados-empresarias.component';
import { DiferenciaisComponent } from '../../components/diferenciais/diferenciais.component';
import { FloatingButtonComponent } from '../../components/floating-button/floating-button.component';
import { FooterComponent } from '../../components/footer/footer.component';
import { GaleriaComponent } from '../../components/galeria/galeria.component';
import { HeaderComponent } from '../../components/header/header.component';
import { HeroComponent } from '../../components/hero/hero.component';
import { OrcamentosComponent } from '../../components/orcamentos/orcamentos.component';
import { ReviewComponent } from '../../components/review/review.component';
import { ServicesComponent } from '../../components/services/services.component';

@Component({
  selector: 'app-page-home',
  imports: [ HeaderComponent, HeroComponent, ReviewComponent, ServicesComponent, DiferenciaisComponent, OrcamentosComponent, GaleriaComponent, DadosEmpresariasComponent, FooterComponent, FloatingButtonComponent],
  templateUrl: './page-home.component.html',
  styleUrl: './page-home.component.css'
})
export class PageHomeComponent {

}
