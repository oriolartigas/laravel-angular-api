import { Component, inject } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MAT_DIALOG_DATA, MatDialogActions, MatDialogModule } from '@angular/material/dialog';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatIconModule } from '@angular/material/icon';

// Import types
import { User } from '@shared/entities/user.interface';

// Import components
import { Address } from '@shared/entities/address.interface';
import { ColumnType, TableColumn } from '@shared/types/entity-table.interface';
import { AddressComponent } from '../../address/address.component';

@Component({
  selector: 'app-address-dialog',
  standalone: true,
  imports: [
    ReactiveFormsModule,
    MatButtonModule,
    MatDialogModule,
    MatDialogActions,
    MatExpansionModule,
    MatIconModule,
    AddressComponent,
  ],
  templateUrl: './address-dialog.component.html',
  styleUrl: './address-dialog.component.css',
})
export class AddressDialogComponent {
  private data = inject<Partial<User>>(MAT_DIALOG_DATA);

  /** The user passed as data */
  public user: Partial<User>;

  /** The columns of the table */
  public columns: TableColumn<Address>[] = [
    { key: 'id', label: 'ID', type: ColumnType.Text },
    { key: 'name', label: 'Name', type: ColumnType.Text },
  ];

  constructor() {
    this.user = {
      id: this.data.id,
      name: this.data.name,
    };
  }
}
