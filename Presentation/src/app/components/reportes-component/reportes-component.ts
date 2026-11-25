import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ProductoService } from '../../shared/services/producto-service';
import { VentaService } from '../../shared/services/venta-service';
import { CategoriaService } from '../../shared/services/categoria-service';
import { ClienteService } from '../../shared/services/cliente-service';
import { ProductoModel } from '../../shared/models/product-model';
import { VentaModel } from '../../shared/models/venta-model';
import { CategoriaModel } from '../../shared/models/categoria-model';
import { ClienteModel } from '../../shared/models/cliente-model';

@Component({
  selector: 'app-reportes-component',
  standalone: true,
  imports: [MatCardModule, MatButtonModule, MatIconModule],
  templateUrl: './reportes-component.html',
  styleUrl: './reportes-component.css'
})
export class ReportesComponent implements OnInit {
  private productoService = inject(ProductoService);
  private ventaService = inject(VentaService);
  private categoriaService = inject(CategoriaService);
  private clienteService = inject(ClienteService);

  productos = signal<ProductoModel[]>([]);
  ventas = signal<VentaModel[]>([]);
  categorias = signal<CategoriaModel[]>([]);
  clientes = signal<ClienteModel[]>([]);

  // Reporte 1: Productos con stock bajo
  reporteStockBajo = computed(() =>
    this.productos().filter((p) => p.stock <= p.stock_minimo)
  );

  // Reporte 2: Resumen de ventas por estado
  reporteVentasEstado = computed(() => {
    const completadas = this.ventas().filter((v) => v.estado !== 'Anulada');
    const anuladas = this.ventas().filter((v) => v.estado === 'Anulada');
    return {
      completadasCount: completadas.length,
      completadasTotal: completadas.reduce((a, b) => a + Number(b.total), 0),
      anuladasCount: anuladas.length,
      anuladasTotal: anuladas.reduce((a, b) => a + Number(b.total), 0)
    };
  });

  // Reporte 3: Valor del inventario por categoría
  reportePorCategoria = computed(() =>
    this.categorias().map((cat) => {
      const prods = this.productos().filter((p) => p.categoria_id === cat.id);
      const valorTotal = prods.reduce((acc, p) => acc + Number(p.precio) * p.stock, 0);
      return { categoria: cat.nombre, cantidadProductos: prods.length, valorTotal };
    })
  );

  // Reporte 4: Compras por cliente
  reportePorCliente = computed(() =>
    this.clientes().map((cli) => {
      const compras = this.ventas().filter((v) => v.cliente_id === cli.id && v.estado !== 'Anulada');
      const totalComprado = compras.reduce((acc, v) => acc + Number(v.total), 0);
      return { cliente: cli.nombre_completo, cedula: cli.cedula, cantidadVentas: compras.length, totalComprado };
    })
  );

  ngOnInit(): void {
    this.productoService.listar().subscribe({ next: (d) => this.productos.set(d) });
    this.ventaService.listar().subscribe({ next: (d) => this.ventas.set(d) });
    this.categoriaService.listar().subscribe({ next: (d) => this.categorias.set(d) });
    this.clienteService.listar().subscribe({ next: (d) => this.clientes.set(d) });
  }

  exportarCSV(nombreArchivo: string, filas: any[]): void {
    if (!filas || filas.length === 0) return;
    const cabeceras = Object.keys(filas[0]);
    const contenido = [
      cabeceras.join(','),
      ...filas.map((fila) => cabeceras.map((k) => `"${fila[k] ?? ''}"`).join(','))
    ].join('\n');

    const blob = new Blob([contenido], { type: 'text/csv;charset=utf-8;' });
    const enlace = document.createElement('a');
    enlace.href = URL.createObjectURL(blob);
    enlace.download = `${nombreArchivo}.csv`;
    enlace.click();
  }
}