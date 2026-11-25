import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FrmVenta } from './frm-venta';

describe('FrmVenta', () => {
  let component: FrmVenta;
  let fixture: ComponentFixture<FrmVenta>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FrmVenta],
    }).compileComponents();

    fixture = TestBed.createComponent(FrmVenta);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
