import { AddressEntityFactory } from '../../../../testing/factories/address-entity.factory';
import {
  checkColumns,
  EntityPageTestConfig,
  EntityPageTestSuite,
} from '../../../../testing/utils/entity-page.spec';
import { Address } from '../../../shared/entities/address.interface';
import { AddressService } from '../../../shared/services/address.service';
import { ColumnType, TableColumn } from '../../../shared/types/entity-table.interface';
import { AddressComponent } from '../address.component';
import { AddressComponentTest } from './address.component.test';

/**
 * Array of TableColumn<Address> representing the expected columns in the table.
 */
const expectedAddressColumns = [
  { key: 'user.name', type: ColumnType.Text },
  { key: 'name', type: ColumnType.Text },
  { key: 'street', type: ColumnType.Text },
  { key: 'city', type: ColumnType.Text },
  { key: 'postal_code', type: ColumnType.Text },
  { key: 'country', type: ColumnType.Text },
];

/**
 * Function to check table column definitions in the AddressComponent tests.
 *
 * @param columns - Array of TableColumn<Address> representing the columns of the table.
 */
const columnCheck = (columns: TableColumn<Address>[]) => {
  checkColumns<Address>(columns, expectedAddressColumns);
};

/**
 * Configuration object for running the EntityPageTestSuite for AddressComponent.
 * This includes:
 *  - Test suite name.
 *  - Component class to test.
 *  - Harness class with exposed methods for testing.
 *  - Main service (AddressService).
 *  - Optional secondary service (none in this case).
 *  - Factory to generate mock address data.
 *  - Expected parameters for the index call.
 *  - Function to generate payload for update operations.
 *  - Function to check table columns.
 */
const config: EntityPageTestConfig<Address> = {
  describeName: 'AddressComponent (basic)',
  ComponentType: AddressComponent,
  HarnessType: AddressComponentTest,
  EntityService: AddressService,
  EntitySecondaryService: undefined,
  Factory: new AddressEntityFactory(),
  expectedIndexParams: { with: 'user' },
  getUpdatePayload: (item: Address) => ({
    user_id: item.user_id,
    name: item.name,
    street: item.street,
    city: item.city,
    postal_code: item.postal_code,
    state: item.state,
    country: item.country,
  }),
  columnCheck: columnCheck,
};

// Execute the EntityPageTestSuite
EntityPageTestSuite<Address>(config);
