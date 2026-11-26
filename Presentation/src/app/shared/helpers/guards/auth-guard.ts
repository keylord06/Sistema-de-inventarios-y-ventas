import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { TokenService } from '../../services/token.service';
import { AuthService } from '../../services/auth.service';

export const authGuard: CanActivateFn = (route, state) => {
  const tokenService = inject(TokenService);
  const authService = inject(AuthService);
  const router = inject(Router);
  const token = tokenService.obtenerToken();

  if (!token) {
    router.navigate(['/login']);
    return false;
  }

  const rolesPermitidos = route.data?.['roles'] as string[] | undefined;
  if (rolesPermitidos && rolesPermitidos.length > 0) {
    const rol = authService.obtenerRol();
    if (!rol || !rolesPermitidos.includes(rol)) {
      router.navigate(['/error403']);
      return false;
    }
  }

  return true;
};