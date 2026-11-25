import { Component, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { CategoriaModel } from '../../shared/models/categoria-model';
import { CategoriaService } from '../../shared/services/categoria-service';
import { FrmCategoria } from '../forms/frm-categoria/frm-categoria';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';

@Component({
  selector: 'app-categoria-component',
  standalone: true,
  imports: [MatTableModule, MatButtonModule, MatIconModule, MatCardModule, MatDialogModule],
  templateUrl: './categoria-component.html',
  styleUrl: './categoria-component.css'
})
export class CategoriaComponent implements OnInit {
  private categoriaService = inject(CategoriaService);
  private dialog = inject(MatDialog);

  categorias = signal<CategoriaModel[]>([]);
  columnas: string[] = ['id', 'nombre', 'acciones'];

  ngOnInit(): void {
    this.cargarCategorias();
  }

  cargarCategorias(): void {
    this.categoriaService.listar().subscribe({
      next: (data) => this.categorias.set(data)
    });
  }

  abrirFormulario(categoria?: CategoriaModel): void {
    const ref = this.dialog.open(FrmCategoria, { width: '400px', data: categoria || null });
    ref.afterClosed().subscribe((ok) => { if (ok) this.cargarCategorias(); });
  }

  eliminarCategoria(categoria: CategoriaModel): void {
    const ref = this.dialog.open(DialogoGeneral, {
      width: '360px',
      data: { titulo: 'Eliminar Categoría', mensaje: `¿Eliminar "${categoria.nombre}"?` }
    });
    ref.afterClosed().subscribe((ok) => {
      if (ok && categoria.id) {
        this.categoriaService.eliminar(categoria.id).subscribe({ next: () => this.cargarCategorias() });
      }
    });
  }
}