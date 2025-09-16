import { Component, ViewChild } from '@angular/core';
import { LoaderComponent } from './shared/loader/loader.component';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  imports: [LoaderComponent, RouterOutlet]
})
export class AppComponent {
  loading = true;

  @ViewChild(LoaderComponent) loader!: LoaderComponent;

  ngOnInit() {
    // Quando a aplicação terminar de iniciar, finaliza loader
    setTimeout(() => {
      this.loader.complete();
      this.loading = false;
    }, 500); // pode ser 2s ou até terminar alguma inicialização real
  }
}
