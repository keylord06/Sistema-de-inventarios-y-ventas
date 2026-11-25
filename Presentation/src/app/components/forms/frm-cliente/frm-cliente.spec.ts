import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FrmCliente } from './frm-cliente';

describe('FrmCliente', () => {
  let component: FrmCliente;
  let fixture: ComponentFixture<FrmCliente>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FrmCliente],
    }).compileComponents();

    fixture = TestBed.createComponent(FrmCliente);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
