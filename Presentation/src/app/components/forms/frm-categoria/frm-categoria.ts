import { Component, inject, OnInit, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { CategoriaModel } from '../../../shared/models/categoria-model';
import { CategoriaService } from '../../../shared/services/categoria-service';
import { mensajeDeError } from '../../../shared/helpers/error-message';

@Component({
  selector: 'app-frm-categoria',
  standalone: true,
  imports: [ReactiveFormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule],
  templateUrl: './frm-categoria.html',
  styleUrl: './frm-categoria.css'
})
export class FrmCategoria implements OnInit {
  private fb = inject(FormBuilder);
  private categoriaService = inject(CategoriaService);
  private dialogRef = inject(MatDialogRef<FrmCategoria>);
  data = inject<CategoriaModel | null>(MAT_DIALOG_DATA);

  formCategoria = this.fb.nonNullable.group({
    nombre: ['', [Validators.required]]
  });

  errorMsg = signal<string>('');

  ngOnInit(): void {
    if (this.data) {
      this.formCategoria.patchValue(this.data);
    }
  }

  guardar(): void {
    if (this.formCategoria.invalid) return;
    this.errorMsg.set('');
    const datos = this.formCategoria.getRawValue();

    const peticion = this.data?.id
      ? this.categoriaService.actualizar(this.data.id, datos)
      : this.categoriaService.crear(datos);

    peticion.subscribe({
      next: () => this.dialogRef.close(true),
      error: (err) => this.errorMsg.set(mensajeDeError(err))
    });
  }
}