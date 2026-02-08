import { FormArray, FormControl, FormGroup } from '@angular/forms';
import { AddressFormControls } from '../../../shared/forms/interfaces/address-form-interface';

/**
 * Controls of the address form
 */
export interface UserFormControls {
  id?: FormControl<number | null>;
  role_ids?: FormControl<number[] | null>;
  name: FormControl<string | number | null>;
  email: FormControl<string | null>;
  password?: FormControl<string | null>;
  password_confirmation?: FormControl<string | null>;
  addresses: FormArray<FormGroup<AddressFormControls>>;
}
