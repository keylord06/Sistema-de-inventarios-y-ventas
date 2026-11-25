import { ComponentFixture, TestBed } from '@angular/core/testing';
import { VentaComponent } from './venta-component';

describe('VentaComponent', () => {
  let component: VentaComponent;
  let fixture: ComponentFixture<VentaComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VentaComponent],
    }).compileComponents();

    fixture = TestBed.createComponent(VentaComponent);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
