import { Component } from '@angular/core';
import { FrmLogin } from '../forms/frm-login/frm-login';

@Component({
  selector: 'app-login',
  standalone: true,
  imports: [FrmLogin],
  templateUrl: './login.html',
  styleUrl: './login.css'
})
export class Login {}