import { Role } from '@shared/entities/role.interface';
import { EntityFactory } from './base/entity.factory'; // Import the abstract base class

/**
 * Concrete factory for creating mock Role objects.
 */
export class RoleEntityFactory extends EntityFactory<Role> {
  /**
   * Creates a single Role object with default values,
   * allowing optional properties to be overwritten.
   * @param overrides - Values to overwrite default fields.
   * @returns A Role object.
   */
  public create(overrides?: Partial<Role>): Role {
    const uniqueId = this.getNextId();
    const defaultName = `Role name ${uniqueId}`;
    const defaultDescription = `Role description ${uniqueId}`;

    return {
      id: uniqueId,
      name: defaultName,
      description: defaultDescription,
      ...overrides,
    };
  }
}
