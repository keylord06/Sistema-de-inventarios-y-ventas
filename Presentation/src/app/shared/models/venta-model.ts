export interface DetalleVentaModel {
  id?: number;
  venta_id?: number;
  producto_id: number;
  producto_nombre?: string;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
}

export interface VentaModel {
  id?: number;
  cliente_id: number | null;
  usuario_id: number;
  fecha?: string;
  total: number;
  estado?: 'Completada' | 'Anulada';
  detalles?: DetalleVentaModel[];
}