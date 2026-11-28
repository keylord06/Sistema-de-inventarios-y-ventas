import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { ClienteModel } from '../../../shared/models/cliente-model';
import { ClienteService } from '../../../shared/services/cliente-service';
import { mensajeDeError } from '../../../shared/helpers/error-message';

@Component({
  selector: 'app-frm-cliente',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule],
  templateUrl: './frm-cliente.html',
  styleUrl: './frm-cliente.css'
})
export class FrmCliente implements OnInit {
  private fb = inject(FormBuilder);
  private clienteService = inject(ClienteService);
  private dialogRef = inject(MatDialogRef<FrmCliente>);
  data = inject<ClienteModel | null>(MAT_DIALOG_DATA);

  formCliente = this.fb.nonNullable.group({
    cedula: ['', [Validators.required]],
    nombre_completo: ['', [Validators.required]],
    telefono: [''],
    email: ['', [Validators.email]]
  });

  errorMsg = signal<string>('');

  ngOnInit(): void {
    if (this.data) {
      this.formCliente.patchValue(this.data);
    }
  }

  guardar(): void {
    if (this.formCliente.invalid) return;
    this.errorMsg.set('');
    const datos = this.formCliente.getRawValue();

    const peticion = this.data?.id
      ? this.clienteService.actualizar(this.data.id, datos)
      : this.clienteService.crear(datos);

    peticion.subscribe({
      next: () => this.dialogRef.close(true),
      error: (err) => this.errorMsg.set(mensajeDeError(err))
    });
  }
}