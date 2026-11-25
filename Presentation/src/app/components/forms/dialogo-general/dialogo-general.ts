import { Component, inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { DialogData } from '../../../shared/models/interfaces';

@Component({
  selector: 'app-dialogo-general',
  standalone: true,
  imports: [MatDialogModule, MatButtonModule],
  templateUrl: './dialogo-general.html',
  styleUrl: './dialogo-general.css'
})
export class DialogoGeneral {
  data = inject<DialogData>(MAT_DIALOG_DATA);
}