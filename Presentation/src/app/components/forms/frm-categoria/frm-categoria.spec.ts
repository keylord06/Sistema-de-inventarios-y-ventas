import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FrmCategoria } from './frm-categoria';

describe('FrmCategoria', () => {
  let component: FrmCategoria;
  let fixture: ComponentFixture<FrmCategoria>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FrmCategoria],
    }).compileComponents();

    fixture = TestBed.createComponent(FrmCategoria);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
