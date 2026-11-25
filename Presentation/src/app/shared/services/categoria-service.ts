import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import { CategoriaModel } from '../models/categoria-model';

@Injectable({
  providedIn: 'root'
})
export class CategoriaService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/categorias`;

  listar(): Observable<CategoriaModel[]> {
    return this.http.get<CategoriaModel[]>(this.url);
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