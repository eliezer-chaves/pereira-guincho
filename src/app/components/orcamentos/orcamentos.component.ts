import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { handleBudgetFormSubmit, handleCallClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { environment } from '../../../environments/environment';
import { NgForm , FormsModule} from '@angular/forms';

@Component({
  selector: 'app-orcamentos',
  imports: [FormsModule ],
  templateUrl: './orcamentos.component.html',
  styleUrl: './orcamentos.component.css'
})
export class OrcamentosComponent {
  phone: string = environment.phoneNumber
  constructor(private router: Router, private obrigadoService: ObrigadoService) { }

  onCallClick() {
    handleCallClick(this.router, this.obrigadoService);
  }

  // A função agora recebe o formulário como parâmetro
  handleForm(form: NgForm) {
    if (form.valid) {
      const formData = form.value;
      
      // Chama a função de helper com os dados do formulário e os serviços
      handleBudgetFormSubmit(formData, this.router, this.obrigadoService);
    }
  }
}