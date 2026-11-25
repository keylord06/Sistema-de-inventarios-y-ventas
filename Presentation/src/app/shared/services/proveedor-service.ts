import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ProveedorModel } from '../models/proveedor-model';

@Injectable({
  providedIn: 'root'
})
export class ProveedorService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/proveedores`;

  listar(): Observable<ProveedorModel[]> {
    return this.http.get<ProveedorModel[]>(this.url);
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