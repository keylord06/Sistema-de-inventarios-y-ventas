import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ClienteModel } from '../models/cliente-model';

@Injectable({
  providedIn: 'root'
})
export class ClienteService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/clientes`;

  listar(): Observable<ClienteModel[]> {
    return this.http.get<ClienteModel[]>(this.url);
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