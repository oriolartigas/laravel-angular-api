import { RoleEntityFactory } from '../../../../testing/factories/role-entity.factory';
import {
  checkColumns,
  EntityPageTestConfig,
  EntityPageTestSuite,
} from '../../../../testing/utils/entity-page.spec';
import { Role } from '@shared/entities/role.interface';
import { RoleService } from '@shared/services/entities/role.service';
import { UserService } from '@shared/services/entities/user.service';
import { ColumnType, TableColumn } from '@shared/types/entity-table.interface';
import { RoleComponent } from '../role.component';
import { RoleComponentTest } from './role.component.test';

/**
 * Array of TableColumn<User> representing the expected columns in the table.
 */
const expectedRoleColumns = [
  { key: 'name', type: ColumnType.Text },
  { key: 'description', type: ColumnType.Text },
  { key: 'users_count', type: ColumnType.Button },
  { key: 'created_at', type: ColumnType.Text },
];

/**
 * Function to check table column definitions in the UserComponent tests.
 *
 * @param columns - Array of TableColumn<User> representing the columns of the table.
 */
const columnCheck = (columns: TableColumn<Role>[]) => {
  checkColumns<Role>(columns, expectedRoleColumns);
};
/**
 * Configuration object for running the EntityPageTestSuite for RoleComponent.
 * This includes:
 *  - Test suite name.
 *  - Component class to test.
 *  - Harness class with exposed methods for testing.
 *  - Main and secondary services (RoleService and UserService).
 *  - Factory to generate mock role data.
 *  - Expected parameters for the index call.
 *  - Function to generate payload for update operations.
 *  - Function to check table columns.
 */
const config: EntityPageTestConfig<Role> = {
  describeName: 'RoleComponent (basic)',
  ComponentType: RoleComponent,
  HarnessType: RoleComponentTest,
  EntityService: RoleService,
  EntitySecondaryService: UserService,
  Factory: new RoleEntityFactory(),
  expectedIndexParams: { with: 'users', withCount: 'users' },
  getUpdatePayload: (item: Role) => ({
    user_ids: item.user_ids,
    name: item.name,
    description: item.description,
  }),
  columnCheck: columnCheck,
};

// Execute the EntityPageTestSuite
EntityPageTestSuite<Role>(config);
