import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PageObrigadoComponent } from './page-obrigado.component';

describe('PageObrigadoComponent', () => {
  let component: PageObrigadoComponent;
  let fixture: ComponentFixture<PageObrigadoComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PageObrigadoComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PageObrigadoComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
