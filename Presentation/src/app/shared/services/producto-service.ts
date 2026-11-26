import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ProductoModel } from '../models/product-model';

@Injectable({
  providedIn: 'root'
})
export class ProductoService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/producto`;

  // Negocio devuelve { data: [...] }, el componente solo necesita el arreglo.
  listar(): Observable<ProductoModel[]> {
    return this.http
      .get<{ data: ProductoModel[] }>(this.url)
      .pipe(map((respuesta) => respuesta.data));
  }

  crear(producto: ProductoModel): Observable<any> {
    return this.http.post(this.url, producto);
  }

  actualizar(id: number, producto: ProductoModel): Observable<any> {
    return this.http.put(`${this.url}/${id}`, producto);
  }

  eliminar(id: number): Observable<any> {
    return this.http.delete(`${this.url}/${id}`);
  }
}