import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DadosEmpresariasComponent } from './dados-empresarias.component';

describe('DadosEmpresariasComponent', () => {
  let component: DadosEmpresariasComponent;
  let fixture: ComponentFixture<DadosEmpresariasComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DadosEmpresariasComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DadosEmpresariasComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
