import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { MAT_DIALOG_DATA, MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { CategoriaModel } from '../../../shared/models/categoria-model';
import { CategoriaService } from '../../../shared/services/categoria-service';

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

  ngOnInit(): void {
    if (this.data) {
      this.formCategoria.patchValue(this.data);
    }
  }

  guardar(): void {
    if (this.formCategoria.invalid) return;
    const datos = this.formCategoria.getRawValue();

    if (this.data?.id) {
      this.categoriaService.actualizar(this.data.id, datos).subscribe({
        next: () => this.dialogRef.close(true)
      });
    } else {
      this.categoriaService.crear(datos).subscribe({
        next: () => this.dialogRef.close(true)
      });
    }
  }
}