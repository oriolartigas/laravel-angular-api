// Import third-party
import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

// Import types
import { HttpResponseData, OptionalQueryParams } from '@core/types/api.interface';
import { Role } from '@shared/entities/role.interface';
import { User } from '@shared/entities/user.interface';
import { ColumnType, TableColumn } from '@shared/types/entity-table.interface';
import { PicklistConfig } from '@shared/types/picklist-dialog.interface';

// Import services
import { RoleService } from '@shared/services/entities/role.service';
import { UserService } from '@shared/services/entities/user.service';

// Import components
import { EntityPageComponent } from '@shared/components/entity-page/entity-page.component';
import { EntityTableComponent } from '@shared/components/entity-table/entity-table.component';
import { RoleFormDialogComponent } from './role-dialog/role-form-dialog.component';

@Component({
  selector: 'app-role',
  imports: [EntityTableComponent, CommonModule, MatButtonModule, MatIconModule],
  templateUrl: '../../shared/components/entity-page/entity-page.component.html',
  styleUrls: [
    '../../shared/components/entity-page/entity-page.component.css',
    './role.component.css',
  ],
})
export class RoleComponent extends EntityPageComponent<Role, RoleFormDialogComponent, RoleService> {
  /** The label for the create button */
  override createButtonLabel: string = 'New Role';

  /** The dialog used to create new records */
  override formDialogComponent = RoleFormDialogComponent;

  /** Services for CRUD operations */
  private roleService: RoleService = inject(RoleService);
  private userService: UserService = inject(UserService);

  /**
   * Get service for CRUD operations
   * @returns RoleService
   */
  protected override getService(): RoleService {
    return this.roleService;
  }

  /**
   * Get column configuration for the table
   * @returns TableColumn[]
   */
  protected override getColumns(): TableColumn<Role>[] {
    return [
      { key: 'id', label: 'ID', type: ColumnType.Text },
      { key: 'name', label: 'Name', type: ColumnType.Text },
      { key: 'description', label: 'Description', type: ColumnType.Text },
      {
        key: 'users_count',
        label: 'Users',
        type: ColumnType.Button,
        onClick: (item: Role) => this.openUserDialog(item),
      },
      { key: 'created_at', label: 'Created', type: ColumnType.Text },
    ];
  }

  /**
   * Get the data used to create a new record
   * @returns Partial<Address>
   */
  protected override getCreateDialogData(): Partial<Role> {
    return {};
  }

  /**
   * Get the parameters used in the index method of the service
   * @returns OptionalQueryParams
   */
  protected override getIndexParams(): OptionalQueryParams<Role> {
    return {
      with: 'users',
      withCount: 'users',
    };
  }

  /**
   * Get the parameters used in the create method of the service
   * @returns OptionalQueryParams
   */
  protected override getCreateParams(): OptionalQueryParams<Role> {
    return {
      with: 'users',
      withCount: 'users',
    };
  }

  /**
   * Get payload used to update
   * @param role
   * @returns Partial<Role>
   */
  protected override getUpdatePayload(role: Role): Partial<Role> {
    return {
      user_ids: role.user_ids,
      name: role.name,
      description: role.description,
    };
  }

  /**
   * Define the default sort key
   * @returns The default sort key
   */
  protected override getDefaultSortKey(): string {
    return 'name';
  }

  /**
   * Open the dialog to add role relations
   * for the selected user.
   *
   * Get all available roles, open the dialog
   * and update the view.
   * @param user The selected user
   */
  private openUserDialog(role: Role) {
    const picklistConfig: PicklistConfig<Role, User> = {
      width: '750px',
      assignedIdKey: 'user_ids',
      countRelation: 'users',
      dialogEntityName: 'users',
      mapToPicklistItem: (user: User) => ({ id: user.id!, name: user.name }),
      getCurrentAssignedIds: (r: Role) => (r.users ? r.users.map((r) => r.id!) : []),
    };

    this.openPicklistDialog<Role, User>(
      role,
      this.userService,
      this.roleService,
      picklistConfig,
    ).subscribe({
      next: (response: HttpResponseData<Role>) => {
        /** Assign the users and users_count to refresh the view */

        let item = this.items.find((item: Role) => item.id === role.id) as Role | undefined;

        if (item) {
          item.users_count = response.data.users_count;
          item.users = response.data.users;
        }
      },
    });
  }
}
