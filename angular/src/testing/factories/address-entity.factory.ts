import { Address } from '../../app/shared/entities/address.interface';
import { EntityFactory } from './base/entity.factory'; // Import the abstract base class

/**
 * Concrete factory for creating mock Address objects.
 */
export class AddressEntityFactory extends EntityFactory<Address> {
  /**
   * Creates a single Address object with default values,
   * allowing optional properties to be overwritten.
   * @param overrides - Values to overwrite default fields.
   * @returns An Address object.
   */
  public create(overrides?: Partial<Address>): Address {
    const uniqueId = this.getNextId();
    const defaultName = `Street name ${uniqueId}`;
    const defaultStreet = `Street ${uniqueId}`;
    const defaultCity = `City ${uniqueId}`;
    const defaultPostalCode = `Postal Code ${uniqueId}`;
    const defaultCountry = `Country ${uniqueId}`;
    const defaultState = `State ${uniqueId}`;

    return {
      id: uniqueId,
      name: defaultName,
      street: defaultStreet,
      city: defaultCity,
      postal_code: defaultPostalCode,
      country: defaultCountry,
      state: defaultState,
      ...overrides,
    };
  }
}
