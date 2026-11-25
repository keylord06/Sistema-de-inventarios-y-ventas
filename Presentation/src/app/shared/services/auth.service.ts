import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { TokenService } from './token.service';
import { LoginResponse } from '../models/interfaces';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private tokenService = inject(TokenService);
  private router = inject(Router);
  private apiUrl = environment.apiUrl;

  estaAutenticado = signal<boolean>(!!this.tokenService.obtenerToken());

  login(credenciales: { username: string; password: string }): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/auth/login`, credenciales).pipe(
      tap((resp) => {
        if (resp.token) {
          this.tokenService.guardarTokens(resp.token, resp.refreshToken);
          if (resp.usuario) {
            this.tokenService.guardarUsuario(resp.usuario);
          }
          this.estaAutenticado.set(true);
        }
      })
    );
  }

  refrescarToken(): Observable<LoginResponse> {
    const refreshToken = this.tokenService.obtenerRefreshToken();
    return this.http.post<LoginResponse>(`${this.apiUrl}/auth/refresh`, { refreshToken }).pipe(
      tap((resp) => {
        if (resp.token) {
          this.tokenService.guardarTokens(resp.token, resp.refreshToken);
        }
      })
    );
  }

  logout(): void {
    this.tokenService.limpiarTodo();
    this.estaAutenticado.set(false);
    this.router.navigate(['/login']);
  }

  obtenerRol(): string {
    const user = this.tokenService.obtenerUsuario();
    if (!user) return '';
    if (user.rol) return user.rol;
    if (user.rol_id === 1) return 'Admin';
    if (user.rol_id === 2) return 'Vendedor';
    if (user.rol_id === 3) return 'Bodeguero';
    return '';
  }
}