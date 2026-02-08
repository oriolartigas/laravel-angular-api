import { User } from '../../app/shared/entities/user.interface';
import { EntityFactory } from './base/entity.factory';

/**
 * Concrete factory for creating mock User objects.
 */
export class UserEntityFactory extends EntityFactory<User> {
  /**
   * Creates a single User object with default values,
   * allowing optional properties to be overwritten.
   * @param overrides - Values to overwrite default fields.
   * @returns A User object.
   */
  public create(overrides?: Partial<User>): User {
    const uniqueId = this.getNextId();
    const defaultName = `User name ${uniqueId}`;
    const defaultEmail = `user${uniqueId}@test.com`;

    return {
      id: uniqueId,
      name: defaultName,
      email: defaultEmail,
      ...overrides,
    };
  }
}
