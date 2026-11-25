import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { TokenService } from '../../services/token.service';

export const authGuard: CanActivateFn = (route, state) => {
  const tokenService = inject(TokenService);
  const router = inject(Router);
  const token = tokenService.obtenerToken();

  if (!token) {
    router.navigate(['/login']);
    return false;
  }

  const rolesPermitidos = route.data?.['roles'] as string[] | undefined;
  if (rolesPermitidos && rolesPermitidos.length > 0) {
    const usuario = tokenService.obtenerUsuario();
    if (!usuario || !rolesPermitidos.includes(usuario.rol)) {
      router.navigate(['/error403']);
      return false;
    }
  }

  return true;
};