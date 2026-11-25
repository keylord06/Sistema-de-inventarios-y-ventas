import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { VentaModel } from '../models/venta-model';

@Injectable({
  providedIn: 'root'
})
export class VentaService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/ventas`;

  listar(): Observable<VentaModel[]> {
    return this.http.get<VentaModel[]>(this.url);
  }

  crear(venta: VentaModel): Observable<any> {
    return this.http.post(this.url, venta);
  }

  anular(id: number): Observable<any> {
    return this.http.put(`${this.url}/${id}`, { estado: 'Anulada' });
  }
}