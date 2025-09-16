import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
@Component({
  selector: 'app-loader',
  imports: [CommonModule],
  templateUrl: './loader.component.html',
  styleUrls: ['./loader.component.css']
})
export class LoaderComponent implements OnInit {
  @Input() visible = true;
  progress = 0;
  private interval: any;

  ngOnInit() {
    if (this.visible) {
      this.startProgress();
    }
  }

  startProgress() {
    this.interval = setInterval(() => {
      if (this.progress < 99) {
        this.progress += Math.floor(Math.random() * 10);
        if (this.progress > 95) {
          this.progress = 95;
        }
      }
    }, 300);
  }

  complete() {
    clearInterval(this.interval);
    this.progress = 100;
    setTimeout(() => (this.visible = false), 500); 
  }
}
