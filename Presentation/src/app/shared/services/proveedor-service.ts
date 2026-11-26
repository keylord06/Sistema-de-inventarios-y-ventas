import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ProveedorModel } from '../models/proveedor-model';

@Injectable({
  providedIn: 'root'
})
export class ProveedorService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/proveedor`;

  // Negocio devuelve { data: [...] }, el componente solo necesita el arreglo.
  listar(): Observable<ProveedorModel[]> {
    return this.http
      .get<{ data: ProveedorModel[] }>(this.url)
      .pipe(map((respuesta) => respuesta.data));
  }

  crear(proveedor: ProveedorModel): Observable<any> {
    return this.http.post(this.url, proveedor);
  }

  actualizar(id: number, proveedor: ProveedorModel): Observable<any> {
    return this.http.put(`${this.url}/${id}`, proveedor);
  }

  eliminar(id: number): Observable<any> {
    return this.http.delete(`${this.url}/${id}`);
  }
}