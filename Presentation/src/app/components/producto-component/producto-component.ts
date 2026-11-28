import { Component, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { ProductoModel } from '../../shared/models/product-model';
import { ProductoService } from '../../shared/services/producto-service';
import { FrmProducto } from '../forms/frm-producto/frm-producto';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';
import { mensajeDeError } from '../../shared/helpers/error-message';

@Component({
  selector: 'app-producto-component',
  standalone: true,
  imports: [
    MatTableModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatDialogModule
  ],
  templateUrl: './producto-component.html',
  styleUrl: './producto-component.css'
})
export class ProductoComponent implements OnInit {
  private productoService = inject(ProductoService);
  private dialog = inject(MatDialog);

  productos = signal<ProductoModel[]>([]);
  columnas: string[] = ['codigo', 'nombre', 'precio', 'stock', 'stock_minimo', 'activo', 'acciones'];

  ngOnInit(): void {
    this.cargarProductos();
  }

  cargarProductos(): void {
    this.productoService.listar().subscribe({
      next: (data) => this.productos.set(data)
    });
  }

  abrirFormulario(producto?: ProductoModel): void {
    const ref = this.dialog.open(FrmProducto, {
      width: '450px',
      data: producto || null
    });

    ref.afterClosed().subscribe((guardado) => {
      if (guardado) this.cargarProductos();
    });
  }

  eliminarProducto(producto: ProductoModel): void {
    const ref = this.dialog.open(DialogoGeneral, {
      width: '360px',
      data: {
        titulo: 'Eliminar Producto',
        mensaje: `¿Seguro que deseas eliminar "${producto.nombre}"?`
      }
    });

    ref.afterClosed().subscribe((confirmado) => {
      if (confirmado && producto.id) {
        this.productoService.eliminar(producto.id).subscribe({
          next: () => this.cargarProductos(),
          error: (err) => alert(mensajeDeError(err))
        });
      }
    });
  }
}