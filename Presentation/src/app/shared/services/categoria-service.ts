import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { CategoriaModel } from '../models/categoria-model';

@Injectable({
  providedIn: 'root'
})
export class CategoriaService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/categoria`;

  // Negocio devuelve { data: [...] }, el componente solo necesita el arreglo.
  listar(): Observable<CategoriaModel[]> {
    return this.http
      .get<{ data: CategoriaModel[] }>(this.url)
      .pipe(map((respuesta) => respuesta.data));
  }

  crear(categoria: CategoriaModel): Observable<any> {
    return this.http.post(this.url, categoria);
  }

  actualizar(id: number, categoria: CategoriaModel): Observable<any> {
    return this.http.put(`${this.url}/${id}`, categoria);
  }

  eliminar(id: number): Observable<any> {
    return this.http.delete(`${this.url}/${id}`);
  }
}