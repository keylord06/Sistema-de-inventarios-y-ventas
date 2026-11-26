import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ClienteModel } from '../models/cliente-model';

@Injectable({
  providedIn: 'root'
})
export class ClienteService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/cliente`;

  // Negocio devuelve { data: [...] }, el componente solo necesita el arreglo.
  listar(): Observable<ClienteModel[]> {
    return this.http
      .get<{ data: ClienteModel[] }>(this.url)
      .pipe(map((respuesta) => respuesta.data));
  }

  crear(cliente: ClienteModel): Observable<any> {
    return this.http.post(this.url, cliente);
  }

  actualizar(id: number, cliente: ClienteModel): Observable<any> {
    return this.http.put(`${this.url}/${id}`, cliente);
  }

  eliminar(id: number): Observable<any> {
    return this.http.delete(`${this.url}/${id}`);
  }
}