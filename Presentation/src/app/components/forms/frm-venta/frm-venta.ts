import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ClienteModel } from '../../../shared/models/cliente-model';
import { ProductoModel } from '../../../shared/models/product-model';
import { DetalleVentaModel } from '../../../shared/models/venta-model';
import { ClienteService } from '../../../shared/services/cliente-service';
import { ProductoService } from '../../../shared/services/producto-service';
import { VentaService } from '../../../shared/services/venta-service';
import { TokenService } from '../../../shared/services/token.service';

@Component({
  selector: 'app-frm-venta',
  standalone: true,
  imports: [
    FormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatSelectModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule
  ],
  templateUrl: './frm-venta.html',
  styleUrl: './frm-venta.css'
})
export class FrmVenta implements OnInit {
  private clienteService = inject(ClienteService);
  private productoService = inject(ProductoService);
  private ventaService = inject(VentaService);
  private tokenService = inject(TokenService);
  private dialogRef = inject(MatDialogRef<FrmVenta>);

  clientes = signal<ClienteModel[]>([]);
  productos = signal<ProductoModel[]>([]);
  detalles = signal<DetalleVentaModel[]>([]);

  clienteSeleccionado: number | null = null;
  productoSeleccionadoId: number | null = null;
  cantidad = 1;

  totalVenta = computed(() =>
    this.detalles().reduce((acc, item) => acc + item.subtotal, 0)
  );

  ngOnInit(): void {
    this.clienteService.listar().subscribe({ next: (c) => this.clientes.set(c) });
    this.productoService.listar().subscribe({ next: (p) => this.productos.set(p) });
  }

  agregarLinea(): void {
    if (!this.productoSeleccionadoId || this.cantidad <= 0) return;
    const prod = this.productos().find((p) => p.id === this.productoSeleccionadoId);
    if (!prod || !prod.id) return;

    const nuevaLinea: DetalleVentaModel = {
      producto_id: prod.id,
      producto_nombre: prod.nombre,
      cantidad: this.cantidad,
      precio_unitario: Number(prod.precio),
      subtotal: Number(prod.precio) * this.cantidad
    };

    this.detalles.update((lista) => [...lista, nuevaLinea]);
    this.productoSeleccionadoId = null;
    this.cantidad = 1;
  }

  quitarLinea(index: number): void {
    this.detalles.update((lista) => lista.filter((_, i) => i !== index));
  }

  guardarVenta(): void {
    if (this.detalles().length === 0) return;
    const usuario = this.tokenService.obtenerUsuario();

    const payload = {
      cliente_id: this.clienteSeleccionado,
      usuario_id: usuario?.id || 1,
      total: this.totalVenta(),
      estado: 'Completada' as const,
      detalles: this.detalles()
    };

    this.ventaService.crear(payload).subscribe({
      next: () => this.dialogRef.close(true)
    });
  }
}