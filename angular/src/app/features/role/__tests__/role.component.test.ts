import { ExposeEntityPageMethods } from '../../../../testing/mixins/expose-entity-page.mixin';
import { Role } from '@shared/entities/role.interface';
import { RoleComponent } from '../role.component';

/**
 * Type defining protected methods of the RoleComponent
 * that are exposed for testing purposes.
 */
type RoleComponentProtectedMethods = {
  /**
   * Opens the User Picklist Dialog for a given role.
   *
   * @param role - The role for whom to open the User dialog.
   * @returns Unknown, typically the dialog result or void.
   */
  openUserDialog(role: Role): unknown;
};

/**
 * This class is used for testing the RoleComponent.
 * It extends the ExposeEntityPageMethods mixin,
 * which provides methods to test the public methods of the component.
 */
export class RoleComponentTest extends ExposeEntityPageMethods<Role, typeof RoleComponent>(
  RoleComponent,
) {
  public expose_openUserDialog(role: Role): void {
    const component = this as unknown as RoleComponentProtectedMethods;
    component.openUserDialog(role);
  }
}
