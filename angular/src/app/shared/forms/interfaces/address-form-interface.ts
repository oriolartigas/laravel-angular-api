import { FormControl } from '@angular/forms';

/**
 * Controls of the address form
 */
export interface AddressFormControls {
  id?: FormControl<number | null>;
  user_id?: FormControl<string | number | null>;
  name: FormControl<string | null>;
  street: FormControl<string | null>;
  city: FormControl<string | null>;
  postal_code: FormControl<string | null>;
  country: FormControl<string | null>;
  state: FormControl<string | null>;
}
