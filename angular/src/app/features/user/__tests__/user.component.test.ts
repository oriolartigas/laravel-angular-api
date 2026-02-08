import { ExposeEntityPageMethods } from '../../../../testing/mixins/expose-entity-page.mixin';
import { User } from '../../../shared/entities/user.interface';
import { UserComponent } from '../user.component';

/**
 * Type defining protected methods of the UserComponent
 * that are exposed for testing purposes.
 */
type UserComponentProtectedMethods = {
  /**
   * Opens the Role Picklist Dialog for a given user.
   *
   * @param user - The user for whom to open the Role dialog.
   * @returns Unknown, typically the dialog result or void.
   */
  openRoleDialog(user: User): unknown;
};

/**
 * This class is used for testing the UserComponent.
 * It extends the ExposeEntityPageMethods mixin,
 * which provides methods to test the public methods of the component.
 */
export class UserComponentTest extends ExposeEntityPageMethods<User, typeof UserComponent>(
  UserComponent,
) {
  public expose_openRoleDialog(user: User): void {
    const component = this as unknown as UserComponentProtectedMethods;
    component.openRoleDialog(user);
  }
}
