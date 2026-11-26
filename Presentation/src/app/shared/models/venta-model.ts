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

// Lo que realmente espera Negocio al crear una venta: el usuario_id lo toma
// del JWT y el precio/total los calcula el servidor, nunca el navegador.
export interface VentaCrearPayload {
  cliente_id: number | null;
  items: { producto_id: number; cantidad: number }[];
}