import { ComponentFixture, TestBed } from '@angular/core/testing';
import { FrmLogin } from './frm-login';

describe('FrmLogin', () => {
  let component: FrmLogin;
  let fixture: ComponentFixture<FrmLogin>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [FrmLogin],
    }).compileComponents();

    fixture = TestBed.createComponent(FrmLogin);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
