import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { ProductoModel } from '../../../shared/models/product-model';
import { ProductoService } from '../../../shared/services/producto-service';
import { mensajeDeError } from '../../../shared/helpers/error-message';

@Component({
  selector: 'app-frm-producto',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatCheckboxModule
  ],
  templateUrl: './frm-producto.html',
  styleUrl: './frm-producto.css'
})
export class FrmProducto implements OnInit {
  private fb = inject(FormBuilder);
  private productoService = inject(ProductoService);
  private dialogRef = inject(MatDialogRef<FrmProducto>);
  data = inject<ProductoModel | null>(MAT_DIALOG_DATA);

  formProducto = this.fb.nonNullable.group({
    codigo: ['', [Validators.required]],
    nombre: ['', [Validators.required]],
    categoria_id: [1, [Validators.required, Validators.min(1)]],
    proveedor_id: this.fb.control<number | null>(null),
    precio: [0, [Validators.required, Validators.min(0.01)]],
    stock: [0, [Validators.required, Validators.min(0)]],
    stock_minimo: [5, [Validators.required, Validators.min(0)]],
    activo: [true]
  });

  errorMsg = signal<string>('');

  ngOnInit(): void {
    if (this.data) {
      this.formProducto.patchValue({
        ...this.data,
        activo: Boolean(this.data.activo ?? true)
      });
    }
  }

  guardar(): void {
    if (this.formProducto.invalid) return;
    this.errorMsg.set('');
    const datos = this.formProducto.getRawValue();

    const peticion = this.data?.id
      ? this.productoService.actualizar(this.data.id, datos)
      : this.productoService.crear(datos);

    peticion.subscribe({
      next: () => this.dialogRef.close(true),
      error: (err) => this.errorMsg.set(mensajeDeError(err))
    });
  }
}