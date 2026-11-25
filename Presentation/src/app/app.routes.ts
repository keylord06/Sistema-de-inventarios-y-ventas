import { Routes } from '@angular/router';
import { Login } from './components/login/login';
import { Home } from './components/home/home';
import { ProductoComponent } from './components/producto-component/producto-component';
import { Error403Component } from './components/error403-component/error403-component';
import { Error404Component } from './components/error404-component/error404-component';
import { authGuard } from './shared/helpers/guards/auth-guard';

export const routes: Routes = [
  { path: '', redirectTo: 'login', pathMatch: 'full' },
  { path: 'login', component: Login },
  { path: 'home', component: Home, canActivate: [authGuard] },
  { path: 'productos', component: ProductoComponent, canActivate: [authGuard] },
  { path: 'error403', component: Error403Component },
  { path: '**', component: Error404Component }
];