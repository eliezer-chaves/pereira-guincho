import { Routes } from '@angular/router';
import { PageHomeComponent } from './pages/page-home/page-home.component';
import { PageObrigadoComponent } from './pages/page-obrigado/page-obrigado.component';

export const routes: Routes = [
  { path: '', component: PageHomeComponent, title: 'Auto Socorro Pereira' },
  { path: 'obrigado', component: PageObrigadoComponent, title: 'Obrigado - Auto Socorro Pereira' },
  
];
