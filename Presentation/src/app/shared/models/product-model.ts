export interface ProductoModel {
  id?: number;
  codigo: string;
  nombre: string;
  precio: number;
  stock: number;
  stock_minimo: number;
  activo: boolean;
  categoria_id: number;
  proveedor_id: number | null;
}