import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FrmProveedor } from './frm-proveedor';

describe('FrmProveedor', () => {
  let component: FrmProveedor;
  let fixture: ComponentFixture<FrmProveedor>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FrmProveedor],
    }).compileComponents();

    fixture = TestBed.createComponent(FrmProveedor);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
