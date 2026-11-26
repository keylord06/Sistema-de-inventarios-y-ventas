import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { VentaCrearPayload, VentaModel } from '../models/venta-model';

@Injectable({
  providedIn: 'root'
})
export class VentaService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/venta`;

  // Negocio devuelve { data: [...] }, el componente solo necesita el arreglo.
  listar(): Observable<VentaModel[]> {
    return this.http
      .get<{ data: VentaModel[] }>(this.url)
      .pipe(map((respuesta) => respuesta.data));
  }

  crear(venta: VentaCrearPayload): Observable<any> {
    return this.http.post(this.url, venta);
  }

  anular(id: number): Observable<any> {
    return this.http.put(`${this.url}/${id}/anular`, {});
  }
}