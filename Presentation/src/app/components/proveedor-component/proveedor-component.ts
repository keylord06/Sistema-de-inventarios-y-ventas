import { Component, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { ProveedorModel } from '../../shared/models/proveedor-model';
import { ProveedorService } from '../../shared/services/proveedor-service';
import { FrmProveedor } from '../forms/frm-proveedor/frm-proveedor';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';

@Component({
  selector: 'app-proveedor-component',
  standalone: true,
  imports: [MatTableModule, MatButtonModule, MatIconModule, MatCardModule, MatDialogModule],
  templateUrl: './proveedor-component.html',
  styleUrl: './proveedor-component.css'
})
export class ProveedorComponent implements OnInit {
  private proveedorService = inject(ProveedorService);
  private dialog = inject(MatDialog);

  proveedores = signal<ProveedorModel[]>([]);
  columnas: string[] = ['nombre', 'telefono', 'email', 'acciones'];

  ngOnInit(): void {
    this.cargarProveedores();
  }

  cargarProveedores(): void {
    this.proveedorService.listar().subscribe({
      next: (data) => this.proveedores.set(data)
    });
  }

  abrirFormulario(proveedor?: ProveedorModel): void {
    const ref = this.dialog.open(FrmProveedor, { width: '420px', data: proveedor || null });
    ref.afterClosed().subscribe((ok) => { if (ok) this.cargarProveedores(); });
  }

  eliminarProveedor(proveedor: ProveedorModel): void {
    const ref = this.dialog.open(DialogoGeneral, {
      width: '360px',
      data: { titulo: 'Eliminar Proveedor', mensaje: `¿Eliminar "${proveedor.nombre}"?` }
    });
    ref.afterClosed().subscribe((ok) => {
      if (ok && proveedor.id) {
        this.proveedorService.eliminar(proveedor.id).subscribe({ next: () => this.cargarProveedores() });
      }
    });
  }
}