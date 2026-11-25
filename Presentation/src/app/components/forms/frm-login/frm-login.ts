import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { AuthService } from '../../../shared/services/auth.service';

@Component({
  selector: 'app-frm-login',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule
  ],
  templateUrl: './frm-login.html',
  styleUrl: './frm-login.css'
})
export class FrmLogin {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);

  errorMsg = signal<string>('');
  cargando = signal<boolean>(false);

  formLogin = this.fb.nonNullable.group({
    username: ['', [Validators.required]],
    password: ['', [Validators.required]]
  });

  iniciarSesion(): void {
    if (this.formLogin.invalid) return;

    this.cargando.set(true);
    this.errorMsg.set('');

    this.authService.login(this.formLogin.getRawValue()).subscribe({
      next: () => {
        this.cargando.set(false);
        this.router.navigate(['/home']);
      },
      error: () => {
        this.cargando.set(false);
        this.errorMsg.set('Usuario o contraseña incorrectos.');
      }
    });
  }
}