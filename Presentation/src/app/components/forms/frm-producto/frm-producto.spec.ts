import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FrmProducto } from './frm-producto';

describe('FrmProducto', () => {
  let component: FrmProducto;
  let fixture: ComponentFixture<FrmProducto>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FrmProducto],
    }).compileComponents();

    fixture = TestBed.createComponent(FrmProducto);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
