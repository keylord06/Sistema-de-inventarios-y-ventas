import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { RouterLink } from '@angular/router';
import { TokenService } from '../../shared/services/token.service';
import { ProductoService } from '../../shared/services/producto-service';
import { VentaService } from '../../shared/services/venta-service';
import { ClienteService } from '../../shared/services/cliente-service';
import { ProductoModel } from '../../shared/models/product-model';
import { VentaModel } from '../../shared/models/venta-model';
import { ClienteModel } from '../../shared/models/cliente-model';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [MatCardModule, MatIconModule, MatButtonModule, RouterLink],
  templateUrl: './home.html',
  styleUrl: './home.css'
})
export class Home implements OnInit {
  private tokenService = inject(TokenService);
  private productoService = inject(ProductoService);
  private ventaService = inject(VentaService);
  private clienteService = inject(ClienteService);

  usuario = this.tokenService.obtenerUsuario();
  productos = signal<ProductoModel[]>([]);
  ventas = signal<VentaModel[]>([]);
  clientes = signal<ClienteModel[]>([]);

  totalIngresos = computed(() =>
    this.ventas()
      .filter((v) => v.estado !== 'Anulada')
      .reduce((acc, v) => acc + Number(v.total), 0)
  );

  productosStockBajo = computed(() =>
    this.productos().filter((p) => p.stock <= p.stock_minimo).length
  );

  ngOnInit(): void {
    this.productoService.listar().subscribe({ next: (d) => this.productos.set(d) });
    this.ventaService.listar().subscribe({ next: (d) => this.ventas.set(d) });
    this.clienteService.listar().subscribe({ next: (d) => this.clientes.set(d) });
  }
}