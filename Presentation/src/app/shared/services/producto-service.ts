import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ProductoModel } from '../models/product-model';

@Injectable({
  providedIn: 'root'
})
export class ProductoService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/productos`;

  listar(): Observable<ProductoModel[]> {
    return this.http.get<ProductoModel[]>(this.url);
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