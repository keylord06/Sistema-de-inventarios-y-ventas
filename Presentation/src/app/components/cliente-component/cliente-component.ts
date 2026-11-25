import { Component, inject, OnInit, signal } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { ClienteModel } from '../../shared/models/cliente-model';
import { ClienteService } from '../../shared/services/cliente-service';
import { FrmCliente } from '../forms/frm-cliente/frm-cliente';
import { DialogoGeneral } from '../forms/dialogo-general/dialogo-general';

@Component({
  selector: 'app-cliente-component',
  standalone: true,
  imports: [MatTableModule, MatButtonModule, MatIconModule, MatCardModule, MatDialogModule],
  templateUrl: './cliente-component.html',
  styleUrl: './cliente-component.css'
})
export class ClienteComponent implements OnInit {
  private clienteService = inject(ClienteService);
  private dialog = inject(MatDialog);

  clientes = signal<ClienteModel[]>([]);
  columnas: string[] = ['cedula', 'nombre_completo', 'telefono', 'email', 'acciones'];

  ngOnInit(): void {
    this.cargarClientes();
  }

  cargarClientes(): void {
    this.clienteService.listar().subscribe({
      next: (data) => this.clientes.set(data)
    });
  }

  abrirFormulario(cliente?: ClienteModel): void {
    const ref = this.dialog.open(FrmCliente, { width: '430px', data: cliente || null });
    ref.afterClosed().subscribe((ok) => { if (ok) this.cargarClientes(); });
  }

  eliminarCliente(cliente: ClienteModel): void {
    const ref = this.dialog.open(DialogoGeneral, {
      width: '360px',
      data: { titulo: 'Eliminar Cliente', mensaje: `¿Eliminar a "${cliente.nombre_completo}"?` }
    });
    ref.afterClosed().subscribe((ok) => {
      if (ok && cliente.id) {
        this.clienteService.eliminar(cliente.id).subscribe({ next: () => this.cargarClientes() });
      }
    });
  }
}