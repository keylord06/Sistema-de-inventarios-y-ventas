import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { ProveedorModel } from '../../../shared/models/proveedor-model';
import { ProveedorService } from '../../../shared/services/proveedor-service';
import { mensajeDeError } from '../../../shared/helpers/error-message';

@Component({
  selector: 'app-frm-proveedor',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule],
  templateUrl: './frm-proveedor.html',
  styleUrl: './frm-proveedor.css'
})
export class FrmProveedor implements OnInit {
  private fb = inject(FormBuilder);
  private proveedorService = inject(ProveedorService);
  private dialogRef = inject(MatDialogRef<FrmProveedor>);
  data = inject<ProveedorModel | null>(MAT_DIALOG_DATA);

  formProveedor = this.fb.nonNullable.group({
    nombre: ['', [Validators.required]],
    telefono: [''],
    email: ['', [Validators.email]]
  });

  errorMsg = signal<string>('');

  ngOnInit(): void {
    if (this.data) {
      this.formProveedor.patchValue(this.data);
    }
  }

  guardar(): void {
    if (this.formProveedor.invalid) return;
    this.errorMsg.set('');
    const datos = this.formProveedor.getRawValue();

    const peticion = this.data?.id
      ? this.proveedorService.actualizar(this.data.id, datos)
      : this.proveedorService.crear(datos);

    peticion.subscribe({
      next: () => this.dialogRef.close(true),
      error: (err) => this.errorMsg.set(mensajeDeError(err))
    });
  }
}