import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { handleWhatsAppClick, handleCallClick, handleMapsClick } from '../../helpers/contact.utils';
import { ObrigadoService } from '../../helpers/obrigado.service';
import { ClickCtaService } from '../../helpers/tracker.service';

@Component({
  selector: 'app-review',
  imports: [],
  templateUrl: './review.component.html',
  styleUrl: './review.component.css'
})
export class ReviewComponent {
  constructor(private router: Router, private obrigadoService: ObrigadoService, private clickCTA: ClickCtaService) { }

  onMapsClick() {
    const uuid = localStorage.getItem('session_uuid')

    handleMapsClick(this.router, this.obrigadoService)
    this.clickCTA.registerClick('maps-review', 'review-section', uuid).subscribe();
  }
}
