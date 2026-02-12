import { UserEntityFactory } from '../../../../testing/factories/user-entity.factory';
import {
  checkColumns,
  EntityPageTestConfig,
  EntityPageTestSuite,
} from '../../../../testing/utils/entity-page.spec';
import { User } from '@shared/entities/user.interface';
import { RoleService } from '@shared/services/entities/role.service';
import { UserService } from '@shared/services/entities/user.service';
import { ColumnType, TableColumn } from '@shared/types/entity-table.interface';
import { UserComponent } from '../user.component';
import { UserComponentTest } from './user.component.test';

/**
 * Array of TableColumn<User> representing the expected columns in the table.
 */
const expectedUserColumns = [
  { key: 'name', type: ColumnType.Text },
  { key: 'email', type: ColumnType.Email },
  { key: 'addresses_count', type: ColumnType.Button },
  { key: 'roles_count', type: ColumnType.Button },
  { key: 'created_at', type: ColumnType.Text },
];

/**
 * Function to check table column definitions in the UserComponent tests.
 *
 * @param columns - Array of TableColumn<User> representing the columns of the table.
 */
const columnCheck = (columns: TableColumn<User>[]) => {
  checkColumns<User>(columns, expectedUserColumns);
};

/**
 * Configuration object for running the EntityPageTestSuite for UserComponent.
 * This includes:
 *  - Test suite name.
 *  - Component class to test.
 *  - Harness class with exposed methods for testing.
 *  - Main and secondary services (UserService and RoleService).
 *  - Factory to generate mock user data.
 *  - Expected parameters for the index call.
 *  - Function to generate payload for update operations.
 *  - Function to check table columns.
 */
const config: EntityPageTestConfig<User> = {
  describeName: 'UserComponent (basic)',
  ComponentType: UserComponent,
  HarnessType: UserComponentTest,
  EntityService: UserService,
  EntitySecondaryService: RoleService,
  Factory: new UserEntityFactory(),
  expectedIndexParams: { with: 'addresses,roles', withCount: 'addresses,roles' },
  getUpdatePayload: (item: User) => ({ name: item.name, email: item.email }),
  columnCheck: columnCheck,
};

// Execute the EntityPageTestSuite
EntityPageTestSuite<User>(config);
