import { ExposeEntityPageMethods } from '../../../../testing/mixins/expose-entity-page.mixin';
import { Address } from '../../../shared/entities/address.interface';
import { AddressComponent } from '../address.component';

/**
 * This class is used for testing the AddressComponent.
 * It extends the ExposeEntityPageMethods mixin,
 * which provides methods to test the public methods of the component.
 */
export class AddressComponentTest extends ExposeEntityPageMethods<Address, typeof AddressComponent>(
  AddressComponent,
) {}
