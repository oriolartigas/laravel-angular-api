// Import third-party
import { CommonModule } from '@angular/common';
import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { take } from 'rxjs';

// Import types
import { HttpResponseData, OptionalQueryParams } from '../../core/types/api.interface';
import { Role } from '../../shared/entities/role.interface';
import { User } from '../../shared/entities/user.interface';
import { ColumnType, TableColumn } from '../../shared/types/entity-table.interface';
import { PicklistConfig } from '../../shared/types/picklist-dialog.interface';

// Import services
import { RoleService } from '../../shared/services/role.service';
import { UserService } from '../../shared/services/user.service';

// Import components
import { EntityPageComponent } from '../../shared/components/entity-page/entity-page.component';
import { EntityTableComponent } from '../../shared/components/entity-table/entity-table.component';
import { AddressDialogComponent } from './address-dialog/address-dialog.component';
import { UserFormDialogComponent } from './user-dialog/user-form-dialog.component';

@Component({
  selector: 'app-user',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, EntityTableComponent],
  templateUrl: '../../shared/components/entity-page/entity-page.component.html',
  styleUrls: [
    '../../shared/components/entity-page/entity-page.component.css',
    './user.component.css',
  ],
})
export class UserComponent extends EntityPageComponent<User, UserFormDialogComponent, UserService> {
  /** The label for the create button */
  override createButtonLabel: string = 'New User';

  /** The dialog used to create new records */
  override formDialogComponent = UserFormDialogComponent;

  /** Services for CRUD operations */
  private userService: UserService = inject(UserService);
  private roleService: RoleService = inject(RoleService);

  /**
   * Get service for CRUD operations
   * @returns UserService
   */
  protected override getService(): UserService {
    return this.userService;
  }

  /**
   * Get column configuration for the table
   * @returns TableColumn[]
   */
  protected override getColumns(): TableColumn<User>[] {
    return [
      { key: 'id', label: 'ID', type: ColumnType.Text },
      { key: 'name', label: 'Name', type: ColumnType.Text },
      { key: 'email', label: 'Email', type: ColumnType.Email },
      {
        key: 'addresses_count',
        label: 'Addresses',
        type: ColumnType.Button,
        onClick: (item: User) => this.openAddressDialog(item),
      },
      {
        key: 'roles_count',
        label: 'Roles',
        type: ColumnType.Button,
        onClick: (item: User) => this.openRoleDialog(item),
      },
      { key: 'created_at', label: 'Created', type: ColumnType.Text },
    ];
  }

  /**
   * Get the data used to create a new record
   * @returns Partial<Address>
   */
  protected override getCreateDialogData(): Partial<User> {
    return {};
  }

  /**
   * Get the parameters used in the index method of the service
   * @returns OptionalQueryParams
   */
  protected override getIndexParams(): OptionalQueryParams<User> {
    return {
      with: 'addresses,roles',
      withCount: 'addresses,roles',
    };
  }

  /**
   * Default parameters used in the create method of the service.
   * @returns OptionalQueryParams
   */
  protected override getCreateParams(): OptionalQueryParams<User> {
    return {
      with: 'addresses,roles',
      withCount: 'addresses,roles',
    };
  }

  /**
   * Get payload used to update
   * @param user
   * @returns Partial<User>
   */
  protected override getUpdatePayload(user: User): Partial<User> {
    return { name: user.name, email: user.email };
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
  private openRoleDialog(user: User) {
    const picklistConfig: PicklistConfig<User, Role> = {
      assignedIdKey: 'role_ids',
      countRelation: 'roles',
      dialogEntityName: 'roles',
      mapToPicklistItem: (role: Role) => ({ id: role.id!, name: role.name }),
      getCurrentAssignedIds: (u: User) => (u.roles ? u.roles.map((r) => r.id!) : []),
    };

    this.openPicklistDialog<User, Role>(
      user,
      this.roleService,
      this.userService,
      picklistConfig,
    ).subscribe({
      next: (response: HttpResponseData<User>) => {
        /** Assign the roles and roles_count to refresh the view */

        let item = this.items.find((item: User) => item.id === user.id) as User | undefined;

        if (item) {
          item.roles_count = response.data.roles_count;
          item.roles = response.data.roles;
        }
      },
    });
  }

  /**
   * Open the dialog to add/edit address relations
   * for the selected user.
   *
   * Get all available addresses, open the dialog
   * and update the view.
   * @param user The selected user
   */
  private openAddressDialog(user: User) {
    this.dialog
      .open(AddressDialogComponent, {
        disableClose: true,
        autoFocus: false,
        height: '650px',
        width: '450px',
        data: {
          id: user.id,
          name: user.name,
        },
      })
      .afterClosed()
      .subscribe({
        next: () => {
          this.userService
            .show(user.id!, this.getIndexParams())
            .pipe(take(1))
            .subscribe({
              next: (response: HttpResponseData<User>) => {
                let item = this.items.find((item: User) => item.id === user.id) as User | undefined;

                if (item) {
                  item.addresses_count = response.data.addresses_count;
                  item.addresses = response.data.addresses;
                }
              },
            });
        },
      });
  }
}
