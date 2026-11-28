import { Component, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { VentaModel } from '../../shared/models/venta-model';
import { VentaService } from '../../shared/services/venta-service';
import { FrmVenta } from '../forms/frm-venta/frm-venta';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';
import { mensajeDeError } from '../../shared/helpers/error-message';

@Component({
  selector: 'app-venta-component',
  standalone: true,
  imports: [MatTableModule, MatButtonModule, MatIconModule, MatCardModule, MatDialogModule],
  templateUrl: './venta-component.html',
  styleUrl: './venta-component.css'
})
export class VentaComponent implements OnInit {
  private ventaService = inject(VentaService);
  private dialog = inject(MatDialog);

  ventas = signal<VentaModel[]>([]);
  columnas: string[] = ['id', 'cliente_id', 'fecha', 'total', 'estado', 'acciones'];

  ngOnInit(): void {
    this.cargarVentas();
  }

  cargarVentas(): void {
    this.ventaService.listar().subscribe({
      next: (data) => this.ventas.set(data)
    });
  }

  abrirNuevaVenta(): void {
    const ref = this.dialog.open(FrmVenta, { width: '560px' });
    ref.afterClosed().subscribe((ok) => { if (ok) this.cargarVentas(); });
  }

  anularVenta(venta: VentaModel): void {
    const ref = this.dialog.open(DialogoGeneral, {
      width: '360px',
      data: { titulo: 'Anular Venta', mensaje: `¿Deseas anular la venta #${venta.id}?` }
    });
    ref.afterClosed().subscribe((ok) => {
      if (ok && venta.id) {
        this.ventaService.anular(venta.id).subscribe({
          next: () => this.cargarVentas(),
          error: (err) => alert(mensajeDeError(err))
        });
      }
    });
  }
}