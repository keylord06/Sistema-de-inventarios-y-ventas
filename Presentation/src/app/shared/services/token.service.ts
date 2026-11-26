import { Injectable } from '@angular/core';
import { UserModel } from '../models/user-model';

@Injectable({
  providedIn: 'root'
})
export class TokenService {
  private readonly TOKEN_KEY = 'access_token';
  private readonly REFRESH_KEY = 'refresh_token';
  private readonly USER_KEY = 'user_data';

  guardarTokens(token: string, refreshToken?: string): void {
    localStorage.setItem(this.TOKEN_KEY, token);
    if (refreshToken) {
      localStorage.setItem(this.REFRESH_KEY, refreshToken);
    }
  }

  obtenerToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  obtenerRefreshToken(): string | null {
    return localStorage.getItem(this.REFRESH_KEY);
  }

  guardarUsuario(usuario: UserModel): void {
    localStorage.setItem(this.USER_KEY, JSON.stringify(usuario));
  }

  obtenerUsuario(): UserModel | null {
    const data = localStorage.getItem(this.USER_KEY);
    return data ? JSON.parse(data) : null;
  }

  limpiarTodo(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    localStorage.removeItem(this.REFRESH_KEY);
    localStorage.removeItem(this.USER_KEY);
  }

  // El token ya viene firmado por Negocio; aquí solo leemos su contenido
  // (id, username, rol_id) para no tener que pedirlo aparte al backend.
  decodificarToken(token: string): UserModel | null {
    try {
      const payload = token.split('.')[1];
      const json = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
      const datos = JSON.parse(json);
      return {
        id: Number(datos.sub),
        username: datos.username,
        rol_id: Number(datos.rol_id)
      };
    } catch {
      return null;
    }
  }

  guardarUsuarioDesdeToken(token: string): void {
    const usuario = this.decodificarToken(token);
    if (usuario) {
      this.guardarUsuario(usuario);
    }
  }
}