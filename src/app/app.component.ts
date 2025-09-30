import { Component, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { LoaderComponent } from './shared/loader/loader.component';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css'],
  imports: [LoaderComponent, RouterOutlet]
})
export class AppComponent implements OnInit {
  loading = true;

  @ViewChild(LoaderComponent) loader!: LoaderComponent;

  ngOnInit() {
    
    
    setTimeout(() => {
      this.loader.complete();
      this.loading = false;
    }, 500); 
  }

  
}
