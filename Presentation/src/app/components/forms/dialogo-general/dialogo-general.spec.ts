import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DialogoGeneral } from './dialogo-general';

describe('DialogoGeneral', () => {
  let component: DialogoGeneral;
  let fixture: ComponentFixture<DialogoGeneral>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DialogoGeneral],
    }).compileComponents();

    fixture = TestBed.createComponent(DialogoGeneral);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
