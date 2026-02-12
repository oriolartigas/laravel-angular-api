// Import third-party
import { CommonModule } from '@angular/common';
import { Component, inject, Input } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

// Import types
import { OptionalQueryParams } from '@core/types/api.interface';
import { Address } from '@shared/entities/address.interface';
import { User } from '@shared/entities/user.interface';
import { ColumnType, TableColumn } from '@shared/types/entity-table.interface';

// Import services
import { AddressService } from '@shared/services/entities/address.service';

// Import components
import { EntityPageComponent } from '@shared/components/entity-page/entity-page.component';
import { EntityTableComponent } from '@shared/components/entity-table/entity-table.component';
import { AddressFormDialogComponent } from './address-dialog/address-form-dialog.component';

@Component({
  selector: 'app-address',
  imports: [EntityTableComponent, CommonModule, MatButtonModule, MatIconModule],
  templateUrl: '../../shared/components/entity-page/entity-page.component.html',
  styleUrls: [
    '../../shared/components/entity-page/entity-page.component.css',
    './address.component.css',
  ],
})
export class AddressComponent extends EntityPageComponent<
  Address,
  AddressFormDialogComponent,
  AddressService
> {
  /** ID of the user to filter by */
  @Input() user_id?: User['id'];

  /** Custom columns for the table */
  @Input() customColumns: TableColumn<Address>[] = [];

  /** The label for the create button */
  override createButtonLabel: string = 'New Address';

  /** The dialog used to create new records */
  override formDialogComponent = AddressFormDialogComponent;

  /** Service for CRUD operations */
  private addressService: AddressService = inject(AddressService);

  /**
   * Get service for CRUD operations
   * @returns AddressService
   */
  protected override getService(): AddressService {
    return this.addressService;
  }

  /**
   * Get column configuration for the table
   * @returns TableColumn[]
   */
  protected override getColumns(): TableColumn<Address>[] {
    if (this.customColumns.length > 0) {
      return this.customColumns;
    } else {
      return [
        { key: 'id', label: 'ID', type: ColumnType.Text },
        { key: 'user.name', label: 'User', type: ColumnType.Text },
        { key: 'name', label: 'Name', type: ColumnType.Text },
        { key: 'street', label: 'Street', type: ColumnType.Text },
        { key: 'city', label: 'City', type: ColumnType.Text },
        { key: 'postal_code', label: 'Postal Code', type: ColumnType.Text },
        { key: 'country', label: 'Country', type: ColumnType.Text },
      ];
    }
  }

  /**
   * Get the data used to create a new record
   * @returns Partial<Address>
   */
  protected override getCreateDialogData(): Partial<Address> {
    return {
      user_id: this.user_id,
    };
  }

  /**
   * Get the parameters used in the index method of the service
   * @returns OptionalQueryParams
   */
  protected override getIndexParams(): OptionalQueryParams<Address> {
    let params: OptionalQueryParams<Address> = {
      with: 'user',
    };

    if (this.user_id) {
      params['where'] = { user_id: this.user_id };
    }

    return params;
  }

  /**
   * Default parameters used in the create method of the service.
   * @returns OptionalQueryParams
   */
  protected override getCreateParams(): OptionalQueryParams<Address> {
    return {
      with: 'user',
    };
  }

  /**
   * Get payload used to update
   * @param address
   * @returns Partial<Address>
   */
  protected override getUpdatePayload(address: Address): Partial<Address> {
    return {
      user_id: address.user_id,
      name: address.name,
      street: address.street,
      city: address.city,
      postal_code: address.postal_code,
      state: address.state,
      country: address.country,
    };
  }

  /**
   * Define the default sort key
   * @returns The default sort key
   */
  protected override getDefaultSortKey(): string {
    return 'user.name';
  }
}
